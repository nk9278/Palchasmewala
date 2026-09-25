<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

require_admin($pdo);


$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die("Order not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF validation failed.");

$new_status = $_POST['order_status'] ?? '';
    if ($new_status && $new_status !== $order['order_status']) {
        if ($new_status === 'cancelled') {
            // Use unified cancellation logic to ensure inventory restores
            $cancel_result = cancel_order($pdo, $id, 'Admin updated status to cancelled', null);
            if ($cancel_result['success']) {
                redirect("admin/orders/view.php?id=$id");
            } else {
                $error = $cancel_result['error'];
            }
        } else {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$new_status, $id]);
                $pdo->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$id, $order['order_status'], $new_status, null, 'Status updated by Admin']);
                $pdo->commit();
                redirect("admin/orders/view.php?id=$id");
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to update status.";
            }
        }
    }
}

$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();

$addr = $pdo->prepare("SELECT * FROM order_addresses WHERE order_id = ?");
$addr->execute([$id]);
$addr = $addr->fetch();
$inv_stmt = $pdo->prepare("SELECT id FROM invoices WHERE order_id = ?");
$inv_stmt->execute([$id]);
$invoice_id = $inv_stmt->fetchColumn();

// Lazy Generation Fallback
if (!$invoice_id && !in_array($order['order_status'], ['cancelled'])) {
    $gen = generate_invoice($pdo, $id);
    if ($gen['success']) {
        $inv_stmt->execute([$id]);
        $invoice_id = $inv_stmt->fetchColumn();
    }
}

$history = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC");
$history->execute([$id]);
$history = $history->fetchAll();
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full max-w-6xl">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-bold">Order: <?php echo e($order['order_number']); ?></h1>
                <?php if($invoice_id): ?>
                    <a href="../../account/invoice.php?id=<?php echo $invoice_id; ?>" target="_blank" class="bg-blue-100 text-blue-800 border border-blue-200 font-bold py-1.5 px-4 rounded text-sm hover:bg-blue-200 transition-colors">View Invoice</a>
                <?php endif; ?>
            </div>
            <a href="index.php" class="bg-gray-300 px-4 py-2 rounded font-bold text-sm">Back</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="md:col-span-2 space-y-6">
                <!-- Items -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Items</h2>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="pb-2">Product</th>
                                <th class="pb-2">SKU</th>
                                <th class="pb-2">Price</th>
                                <th class="pb-2">Qty</th>
                                <th class="pb-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $i): ?>
                            <tr class="border-b">
                                <td class="py-2">
                                    <span class="font-bold"><?php echo e($i['product_name']); ?></span><br>
                                    <span class="text-xs text-gray-500"><?php echo e($i['variant_name']); ?></span>
                                </td>
                                <td class="py-2"><?php echo e($i['sku']); ?></td>
                                <td class="py-2"><?php echo format_price($i['unit_price']); ?></td>
                                <td class="py-2"><?php echo $i['quantity']; ?></td>
                                <td class="py-2 text-right font-bold"><?php echo format_price($i['line_total']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="w-1/2 ml-auto mt-6 text-sm">
                        <div class="flex justify-between mb-1"><span>Subtotal:</span> <span><?php echo format_price($order['subtotal']); ?></span></div>
                        <div class="flex justify-between mb-1"><span>Tax:</span> <span><?php echo format_price($order['tax_amount']); ?></span></div>
                        <div class="flex justify-between mb-1"><span>Shipping:</span> <span><?php echo format_price($order['shipping_charge']); ?></span></div>
                        <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2"><span>Grand Total:</span> <span class="text-pcwRed"><?php echo format_price($order['grand_total']); ?></span></div>
                    </div>
                </div>

                <!-- History -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Status History</h2>
                    <div class="space-y-3">
                        <?php foreach($history as $h): ?>
                        <div class="text-sm">
                            <span class="text-gray-500"><?php echo e($h['created_at']); ?></span> -
                            Status changed to <span class="font-bold uppercase"><?php echo e($h['new_status']); ?></span>
                            <?php if($h['note']) echo "<br><span class='text-xs text-gray-500'>Note: ".e($h['note'])."</span>"; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Manage Status -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Manage Status</h2>
                    <form method="post" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <label class="block text-sm font-bold">Update Order Status</label>
                        <select name="order_status" class="w-full border p-2 rounded">
                            <?php
                            $statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
                            foreach($statuses as $s) {
                                $sel = $order['order_status'] === $s ? 'selected' : '';
                                echo "<option value='$s' $sel>".ucfirst($s)."</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded mt-2 hover:bg-blue-700">Update</button>
                    </form>

                    <div class="mt-6 border-t pt-4 text-sm">
                        <p><strong>Payment Method:</strong> <?php echo e($order['payment_method']); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo e(ucfirst($order['payment_status'])); ?></p>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Customer & Address</h2>
                    <p class="font-bold"><?php echo e($order['customer_name']); ?></p>
                    <p class="text-sm text-gray-600 mb-4"><?php echo e($order['customer_phone']); ?><br><?php echo e($order['customer_email']); ?></p>

                    <?php if($addr): ?>
                    <h3 class="font-bold text-sm text-gray-800">Shipping To:</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        <?php echo e($addr['full_name']); ?><br>
                        <?php echo e($addr['address_line_1']); ?><br>
                        <?php if($addr['address_line_2']) echo e($addr['address_line_2']) . '<br>'; ?>
                        <?php echo e($addr['city']); ?>, <?php echo e($addr['state']); ?> - <?php echo e($addr['pincode']); ?>

                    </p>
                    <?php endif; ?>
                </div>

                <!-- Notes -->
                <?php if($order['customer_notes']): ?>
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-2 border-b pb-2">Customer Notes</h2>
                    <p class="text-sm text-gray-600 italic"><?php echo e($order['customer_notes']); ?></p>
                </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
