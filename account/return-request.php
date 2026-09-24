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

// Verify ownership and eligibility (only delivered orders)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND order_status = 'delivered'");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or not eligible for return.");
}

// Fetch items
$items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }

    $item_id = (int)$_POST['item_id'] ?? 0;
    $qty = (int)$_POST['quantity'] ?? 0;
    $reason = trim($_POST['reason'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Validate Item
    $valid_item = false;
    $max_qty = 0;
    foreach ($items as $i) {
        if ($i['id'] == $item_id) {
            $valid_item = true;
            $max_qty = $i['quantity'];
            break;
        }
    }

    if (!$valid_item || $qty <= 0 || $qty > $max_qty || empty($reason)) {
        $error = "Invalid return parameters.";
    } else {
        // Check if a return already exists for this item
        $check_ret = $pdo->prepare("SELECT SUM(quantity) FROM return_items ri JOIN returns r ON ri.return_id = r.id WHERE r.order_id = ? AND ri.order_item_id = ? AND r.status NOT IN ('cancelled', 'rejected')");
        $check_ret->execute([$order_id, $item_id]);
        $existing_qty = (int)$check_ret->fetchColumn();

        if ($existing_qty + $qty > $max_qty) {
            $error = "You have already requested a return for this quantity.";
        } else {
            try {
                $pdo->beginTransaction();

                // Create Return
                $ins_ret = $pdo->prepare("INSERT INTO returns (order_id, user_id, status, reason, customer_notes) VALUES (?, ?, 'requested', ?, ?)");
                $ins_ret->execute([$order_id, $user_id, $reason, $notes]);
                $return_id = $pdo->lastInsertId();

                // Create Return Item
                $ins_item = $pdo->prepare("INSERT INTO return_items (return_id, order_item_id, quantity) VALUES (?, ?, ?)");
                $ins_item->execute([$return_id, $item_id, $qty]);

                // Status History Note
                $pdo->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note) VALUES (?, ?, ?, ?, ?)")->execute([$order_id, $order['order_status'], $order['order_status'], $user_id, "Return requested for item ID $item_id (Qty: $qty)"]);

                $pdo->commit();
                $success = "Return request submitted successfully. We will review it shortly.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to submit return request: " . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-pcwBlack">Request Return</h1>
            <a href="order.php?id=<?php echo $order_id; ?>" class="text-gray-500 hover:text-pcwBlack text-sm font-bold">&larr; Back to Order</a>
        </div>

        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-6 text-sm border border-green-200">
                <p class="font-bold mb-2"><?php echo e($success); ?></p>
                <a href="order.php?id=<?php echo $order_id; ?>" class="underline">View Order</a>
            </div>
        <?php else: ?>
            <?php if($error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-6 text-sm border border-red-200"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="post" class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100">
                <?php echo csrf_field(); ?>
                <p class="text-sm text-gray-500 mb-6">Select the item you wish to return from Order #<?php echo e($order['order_number']); ?>.</p>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Item to Return</label>
                    <div class="space-y-3">
                        <?php foreach($items as $i): ?>
                            <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="item_id" value="<?php echo $i['id']; ?>" class="mt-1" required>
                                <div>
                                    <span class="font-bold text-gray-800 block text-sm"><?php echo e($i['product_name']); ?></span>
                                    <span class="text-xs text-gray-500">Variant: <?php echo e($i['variant_name']); ?> | Purchased: <?php echo $i['quantity']; ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Quantity to Return</label>
                        <input type="number" name="quantity" min="1" value="1" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Reason for Return</label>
                        <select name="reason" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                            <option value="">Select reason...</option>
                            <option value="Damaged product">Damaged product</option>
                            <option value="Wrong product">Wrong product</option>
                            <option value="Size/fit issue">Size/fit issue</option>
                            <option value="Product not as expected">Product not as expected</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded p-3 text-sm outline-none focus:border-pcwRed" placeholder="Please provide more details..."></textarea>
                </div>

                <button type="submit" class="w-full bg-pcwRed hover:bg-red-800 text-white font-bold py-3 rounded-lg transition-colors shadow-md text-sm sm:text-base">
                    Submit Return Request
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>