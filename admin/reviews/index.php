<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin($pdo);


$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$rating = $_GET['rating'] ?? '';
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$per_page = 20;

$query = "SELECT r.*, p.name as product_name, u.name as customer_name
          FROM product_reviews r
          JOIN products p ON r.product_id = p.id
          JOIN users u ON r.user_id = u.id
          WHERE 1=1";
$count_query = "SELECT COUNT(*) FROM product_reviews r JOIN products p ON r.product_id = p.id JOIN users u ON r.user_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR u.name LIKE ?)";
    $count_query .= " AND (p.name LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $query .= " AND r.status = ?";
    $count_query .= " AND r.status = ?";
    $params[] = $status;
}

if ($rating) {
    $query .= " AND r.rating = ?";
    $count_query .= " AND r.rating = ?";
    $params[] = $rating;
}

$query .= " ORDER BY r.created_at DESC LIMIT $per_page OFFSET " . (($page - 1) * $per_page);
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$c_stmt = $pdo->prepare($count_query);
$c_stmt->execute($params);
$total_reviews = $c_stmt->fetchColumn();
$total_pages = ceil($total_reviews / $per_page);
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full max-w-7xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Product Reviews</h1>
            <a href="../index.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded">Back to Dashboard</a>
        </div>

        <form method="get" class="mb-6 flex gap-4">
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Product or Customer..." class="border p-2 rounded w-64">
            <select name="status" class="border p-2 rounded">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status=='pending'?'selected':''; ?>>Pending</option>
                <option value="approved" <?php echo $status=='approved'?'selected':''; ?>>Approved</option>
                <option value="rejected" <?php echo $status=='rejected'?'selected':''; ?>>Rejected</option>
            </select>
            <select name="rating" class="border p-2 rounded">
                <option value="">All Ratings</option>
                <?php for($i=5; $i>=1; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $rating==$i?'selected':''; ?>><?php echo $i; ?> Star(s)</option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
        </form>

        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Customer</th>
                        <th class="py-3 px-4 text-left">Product</th>
                        <th class="py-3 px-4 text-left">Rating</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="py-3 px-4 text-xs text-gray-500"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                        <td class="py-3 px-4 font-bold"><?php echo e($r['customer_name']); ?></td>
                        <td class="py-3 px-4 text-blue-600">
                            <a href="../../product.php?slug=<?php echo urlencode($pdo->query("SELECT slug FROM products WHERE id = {$r['product_id']}")->fetchColumn()); ?>" target="_blank" class="hover:underline">
                                <?php echo e($r['product_name']); ?>
                            </a>
                        </td>
                        <td class="py-3 px-4 text-yellow-500 font-bold"><?php echo str_repeat('★', $r['rating']); ?></td>
                        <td class="py-3 px-4">
                            <?php
                                $color = 'bg-gray-200 text-gray-700';
                                if($r['status'] == 'approved') $color = 'bg-green-100 text-green-800';
                                if($r['status'] == 'rejected') $color = 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $color; ?>"><?php echo e($r['status']); ?></span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="view.php?id=<?php echo $r['id']; ?>" class="text-blue-600 font-bold hover:underline">Review</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
            <div class="mt-6 flex justify-center gap-2">
                <?php
                $qs = $_GET;
                unset($qs['page']);
                $qstr = http_build_query($qs);
                $qstr = $qstr ? '&' . $qstr : '';
                for($i=1; $i<=$total_pages; $i++):
                    $active = $i == $page ? 'bg-blue-600 text-white' : 'bg-white border text-gray-700 hover:bg-gray-50';
                ?>
                    <a href="index.php?page=<?php echo $i; ?><?php echo $qstr; ?>" class="px-4 py-2 rounded font-semibold text-sm transition-colors <?php echo $active; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
