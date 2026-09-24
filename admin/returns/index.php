<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$query = "SELECT r.*, o.order_number, u.name as customer_name
          FROM returns r
          JOIN orders o ON r.order_id = o.id
          JOIN users u ON r.user_id = u.id
          ORDER BY r.created_at DESC";
$returns = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Returns - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-8 max-w-6xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Returns & Refunds</h1>
            <a href="../index.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded">Back to Dashboard</a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Return ID</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Order #</th>
                        <th class="py-3 px-4 text-left">Customer</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returns as $r): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="py-3 px-4 font-bold">#RET-<?php echo e($r['id']); ?></td>
                        <td class="py-3 px-4 text-xs text-gray-500"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                        <td class="py-3 px-4 text-blue-600"><a href="../orders/view.php?id=<?php echo $r['order_id']; ?>"><?php echo e($r['order_number']); ?></a></td>
                        <td class="py-3 px-4"><?php echo e($r['customer_name']); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-gray-200 text-gray-700"><?php echo e($r['status']); ?></span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="view.php?id=<?php echo $r['id']; ?>" class="text-blue-600 font-bold hover:underline">Manage</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>