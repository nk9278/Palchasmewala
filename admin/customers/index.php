<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_admin($pdo);

$search = $_GET['search'] ?? '';
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$per_page = 20;

$query = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at, u.role,
                 (SELECT COUNT(id) FROM orders WHERE user_id = u.id) as order_count
          FROM users u
          WHERE 1=1";
$count_query = "SELECT COUNT(*) FROM users u WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $count_query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY u.created_at DESC LIMIT $per_page OFFSET " . (($page - 1) * $per_page);

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$c_stmt = $pdo->prepare($count_query);
$c_stmt->execute($params);
$total_customers = $c_stmt->fetchColumn();
$total_pages = ceil($total_customers / $per_page);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="w-full max-w-7xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Customers</h1>
    </div>

    <form method="get" class="mb-6 flex gap-4">
        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search name, phone, email..." class="border p-2 rounded w-64">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
    </form>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left">Customer</th>
                    <th class="py-3 px-4 text-left">Contact</th>
                    <th class="py-3 px-4 text-left">Role</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-left">Orders</th>
                    <th class="py-3 px-4 text-left">Joined</th>
                    <th class="py-3 px-4 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                <tr class="border-t hover:bg-gray-50">
                    <td class="py-3 px-4 font-bold text-gray-800"><?php echo e($c['name']); ?></td>
                    <td class="py-3 px-4 text-gray-600"><?php echo e($c['phone']); ?><br><span class="text-xs text-gray-400"><?php echo e($c['email'] ?? 'No email'); ?></span></td>
                    <td class="py-3 px-4"><span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs uppercase font-bold"><?php echo e($c['role']); ?></span></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $c['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo e($c['status']); ?>

                        </span>
                    </td>
                    <td class="py-3 px-4 font-bold"><?php echo $c['order_count']; ?></td>
                    <td class="py-3 px-4 text-gray-500 text-xs"><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                    <td class="py-3 px-4">
                        <a href="view.php?id=<?php echo $c['id']; ?>" class="text-blue-600 font-bold hover:underline">View Profile</a>
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