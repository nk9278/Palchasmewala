<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin($pdo);


$search = $_GET['search'] ?? '';
$cat_id = $_GET['category'] ?? '';

$query = "SELECT p.*, c.name as cat_name, b.name as brand_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN brands b ON p.brand_id = b.id
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cat_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $cat_id;
}

$query .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Products</h1>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded">Add Product</a>
        </div>

        <form method="get" class="mb-6 flex gap-4">
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search name or SKU..." class="border p-2 rounded">
            <select name="category" class="border p-2 rounded">
                <option value="">All Categories</option>
                <?php foreach($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $cat_id == $c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-200 px-4 py-2 rounded">Filter</button>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">SKU</th>
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Category</th>
                        <th class="py-3 px-4 text-left">Brand</th>
                        <th class="py-3 px-4 text-left">Base Price</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr class="border-t">
                        <td class="py-3 px-4"><?php echo e($p['sku']); ?></td>
                        <td class="py-3 px-4 font-medium"><?php echo e($p['name']); ?></td>
                        <td class="py-3 px-4"><?php echo e($p['cat_name']); ?></td>
                        <td class="py-3 px-4"><?php echo e($p['brand_name'] ?? '-'); ?></td>
                        <td class="py-3 px-4"><?php echo format_price($p['base_price']); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs text-white <?php echo $p['status'] == 'active' ? 'bg-green-500' : 'bg-red-500'; ?>">
                                <?php echo e(ucfirst($p['status'])); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 flex gap-2">
                            <a href="view.php?id=<?php echo $p['id']; ?>" class="text-blue-500 hover:underline">View</a>
                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="text-green-500 hover:underline">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><a href="../index.php" class="text-gray-500">&larr; Back to Dashboard</a></div>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
