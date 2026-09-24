<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$search = $_GET['search'] ?? '';

$query = "SELECT v.*, p.name as product_name
          FROM product_variants v
          JOIN products p ON v.product_id = p.id
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (v.sku LIKE ? OR p.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.name ASC, v.variant_name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$inventory = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Inventory Overview</h1>
            <div>
                <a href="adjustments.php" class="bg-blue-600 text-white px-4 py-2 rounded">Adjust Stock</a>
                <a href="history.php" class="bg-gray-600 text-white px-4 py-2 rounded">View History</a>
            </div>
        </div>

        <form method="get" class="mb-6 flex gap-4">
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search product or variant SKU..." class="border p-2 rounded">
            <button type="submit" class="bg-gray-200 px-4 py-2 rounded">Filter</button>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Product</th>
                        <th class="py-3 px-4 text-left">Variant</th>
                        <th class="py-3 px-4 text-left">SKU</th>
                        <th class="py-3 px-4 text-left">Stock</th>
                        <th class="py-3 px-4 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $i): ?>
                    <tr class="border-t">
                        <td class="py-3 px-4 font-medium"><?php echo e($i['product_name']); ?></td>
                        <td class="py-3 px-4"><?php echo e($i['variant_name']); ?></td>
                        <td class="py-3 px-4"><?php echo e($i['sku']); ?></td>
                        <td class="py-3 px-4 font-bold text-lg <?php echo $i['stock_quantity'] <= $i['low_stock_threshold'] ? 'text-red-600' : 'text-green-600'; ?>">
                            <?php echo e($i['stock_quantity']); ?>
                        </td>
                        <td class="py-3 px-4">
                            <?php
                                if($i['stock_quantity'] <= 0) echo '<span class="text-red-500 font-bold">Out of Stock</span>';
                                else if($i['stock_quantity'] <= $i['low_stock_threshold']) echo '<span class="text-orange-500 font-bold">Low Stock</span>';
                                else echo '<span class="text-green-500 font-bold">In Stock</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><a href="../index.php" class="text-gray-500">&larr; Back to Dashboard</a></div>
    </div>
</body>
</html>