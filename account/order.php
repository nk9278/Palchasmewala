<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();
$order_id = $_GET['id'] ?? 0;

// IDOR Protection: Ensure order belongs to user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or unauthorized access.");
}

$success = '';
$error = '';

// Handle Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }

    $reason = trim($_POST['reason'] ?? 'User requested cancellation');

// Check if order is eligible visually (actual check happens in function)
    if (in_array($order['order_status'], ['pending', 'confirmed'])) {
        $cancel_result = cancel_order($pdo, $order_id, $reason, $user_id);
        if ($cancel_result['success']) {
            $success = "Order cancelled successfully.";
            $order['order_status'] = 'cancelled';
        } else {
            $error = $cancel_result['error'];
        }
    } else {
        $error = "Order cannot be cancelled at this stage.";
    }
}


// Fetch related data
$items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

$addr_stmt = $pdo->prepare("SELECT * FROM order_addresses WHERE order_id = ?");
$addr_stmt->execute([$order_id]);
$address = $addr_stmt->fetch();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 max-w-5xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-pcwBlack">Order #<?php echo e($order['order_number']); ?></h1>
            <a href="orders.php" class="text-gray-500 hover:text-pcwBlack text-sm font-bold">&larr; Back to Orders</a>
        </div>

        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6 text-sm border border-green-200"><?php echo e($success); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-6 text-sm border border-red-200"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2 space-y-6">

                <!-- Items -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b bg-gray-50">
                        <h2 class="font-bold text-gray-800">Items Ordered</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach($items as $i): ?>
                            <div class="p-4 flex justify-between items-center text-sm">
                                <div>
                                    <p class="font-bold text-gray-900"><?php echo e($i['product_name']); ?></p>
                                    <p class="text-xs text-gray-500">Option: <?php echo e($i['variant_name']); ?> | SKU: <?php echo e($i['sku']); ?></p>
                                    <p class="text-gray-600 mt-1">Qty: <?php echo $i['quantity']; ?> x <?php echo format_price($i['unit_price']); ?></p>
                                </div>
                                <div class="font-bold text-gray-800">
                                    <?php echo format_price($i['line_total']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Totals -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-6 text-sm">
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-600"><span>Subtotal:</span> <span><?php echo format_price($order['subtotal']); ?></span></div>
                        <div class="flex justify-between text-gray-600"><span>Tax (GST):</span> <span><?php echo format_price($order['tax_amount']); ?></span></div>
                        <div class="flex justify-between text-gray-600"><span>Shipping:</span> <span><?php echo format_price($order['shipping_charge']); ?></span></div>
                        <div class="flex justify-between text-gray-800 font-bold text-base pt-3 border-t"><span>Grand Total:</span> <span class="text-pcwRed"><?php echo format_price($order['grand_total']); ?></span></div>
                    </div>
                </div>

            </div>

            <div class="space-y-6">
                <!-- Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-4 border-b pb-2">Order Information</h2>
                    <p class="text-sm text-gray-600 mb-2"><strong>Date:</strong> <?php echo date('d M Y h:i A', strtotime($order['created_at'])); ?></p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Order Status:</strong> <span class="capitalize font-bold text-gray-900"><?php echo e($order['order_status']); ?></span></p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Payment Method:</strong> <?php echo e($order['payment_method']); ?></p>
                    <p class="text-sm text-gray-600 mb-4"><strong>Payment Status:</strong> <span class="capitalize font-bold text-gray-900"><?php echo e($order['payment_status']); ?></span></p>

                    <?php if(in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                        <form method="post" onsubmit="return confirm('Are you sure you want to cancel this order? This cannot be undone.');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="cancel">
                            <select name="reason" class="w-full border border-gray-300 rounded p-2 text-sm mb-2" required>
                                <option value="">Select cancellation reason...</option>
                                <option value="Changed my mind">Changed my mind</option>
                                <option value="Ordered by mistake">Ordered by mistake</option>
                                <option value="Found another product">Found another product</option>
                                <option value="Delivery time issue">Delivery time issue</option>
                                <option value="Other">Other</option>
                            </select>
                            <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 font-bold py-2 rounded text-sm hover:bg-red-100 transition-colors">Cancel Order</button>
                        </form>
                    <?php elseif($order['order_status'] === 'delivered'): ?>
                        <a href="return-request.php?id=<?php echo $order_id; ?>" class="block text-center w-full bg-blue-50 text-blue-600 border border-blue-200 font-bold py-2 rounded text-sm hover:bg-blue-100 transition-colors">Request Return</a>
                    <?php endif; ?>
                </div>

                <!-- Shipping Address -->
                <?php if($address): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-800 mb-4 border-b pb-2">Shipping Address</h2>
                    <h3 class="font-bold text-gray-800 mb-1"><?php echo e($address['full_name']); ?></h3>
                    <p class="text-sm text-gray-600 mb-2"><?php echo e($address['phone']); ?></p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        <?php echo e($address['address_line_1']); ?><br>
                        <?php if($address['address_line_2']) echo e($address['address_line_2']) . '<br>'; ?>
                        <?php echo e($address['city']); ?>, <?php echo e($address['state']); ?> - <?php echo e($address['pincode']); ?>

                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>