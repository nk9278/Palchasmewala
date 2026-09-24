<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$order_num = $_GET['order'] ?? '';
if (!$order_num) {
    redirect('account/orders.php');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_num, current_user_id()]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or unauthorized access.");
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="bg-gray-50 py-16 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-gray-100 max-w-lg w-full mx-4 text-center">
        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 class="text-3xl font-bold text-pcwBlack mb-2">Order Confirmed!</h1>
        <p class="text-gray-500 mb-6">Thank you for your purchase.</p>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8 text-left text-sm">
            <p class="flex justify-between mb-2"><span class="text-gray-500">Order Number:</span> <span class="font-bold"><?php echo e($order['order_number']); ?></span></p>
            <p class="flex justify-between mb-2"><span class="text-gray-500">Date:</span> <span class="font-bold"><?php echo e(date('d M Y', strtotime($order['created_at']))); ?></span></p>
            <p class="flex justify-between mb-2"><span class="text-gray-500">Total:</span> <span class="font-bold text-pcwRed"><?php echo format_price($order['grand_total']); ?></span></p>
            <p class="flex justify-between mb-2"><span class="text-gray-500">Payment:</span> <span class="font-bold"><?php echo e($order['payment_method']); ?></span></p>
        </div>

        <div class="flex gap-4 justify-center">
            <a href="account/order.php?id=<?php echo $order['id']; ?>" class="bg-gray-800 text-white font-bold py-2.5 px-6 rounded transition-colors hover:bg-black">View Order</a>
            <a href="shop.php" class="bg-white border border-gray-300 text-gray-700 font-bold py-2.5 px-6 rounded transition-colors hover:bg-gray-50">Continue Shopping</a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>