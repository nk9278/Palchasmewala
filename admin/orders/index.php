<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$payment = $_GET['payment'] ?? '';

$query = "SELECT o.*, u.name as customer_name, u.phone as customer_phone
          FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $query .= " AND o.order_status = ?";
    $params[] = $status;
}

if ($payment) {
    $query .= " AND o.payment_status = ?";
    $params[] = $payment;
}

$query .= " ORDER BY o.created_at DESC LIMIT 100";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-8 max-w-7xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Orders</h1>
            <a href="../index.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded">Back to Dashboard</a>
        </div>

        <form method="get" class="mb-6 flex gap-4">
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Order #, Customer, Phone..." class="border p-2 rounded w-64">
            <select name="status" class="border p-2 rounded">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status=='pending'?'selected':''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status=='confirmed'?'selected':''; ?>>Confirmed</option>
                <option value="processing" <?php echo $status=='processing'?'selected':''; ?>>Processing</option>
                <option value="shipped" <?php echo $status=='shipped'?'selected':''; ?>>Shipped</option>
                <option value="delivered" <?php echo $status=='delivered'?'selected':''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $status=='cancelled'?'selected':''; ?>>Cancelled</option>
            </select>
            <select name="payment" class="border p-2 rounded">
                <option value="">All Payments</option>
                <option value="pending" <?php echo $payment=='pending'?'selected':''; ?>>Pending</option>
                <option value="paid" <?php echo $payment=='paid'?'selected':''; ?>>Paid</option>
                <option value="refund_pending" <?php echo $payment=='refund_pending'?'selected':''; ?>>Refund Pending</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
        </form>

        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Order #</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Customer</th>
                        <th class="py-3 px-4 text-left">Total</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Payment</th>
                        <th class="py-3 px-4 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="py-3 px-4 font-bold"><?php echo e($o['order_number']); ?></td>
                        <td class="py-3 px-4 text-xs text-gray-500"><?php echo date('d M Y H:i', strtotime($o['created_at'])); ?></td>
                        <td class="py-3 px-4">
                            <?php echo e($o['customer_name']); ?><br>
                            <span class="text-xs text-gray-500"><?php echo e($o['customer_phone']); ?></span>
                        </td>
                        <td class="py-3 px-4 font-bold text-pcwRed"><?php echo format_price($o['grand_total']); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-gray-200 text-gray-700"><?php echo e($o['order_status']); ?></span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase <?php echo $o['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo e($o['payment_status']); ?> (<?php echo e($o['payment_method']); ?>)
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="view.php?id=<?php echo $o['id']; ?>" class="text-blue-600 font-bold hover:underline">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>