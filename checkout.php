<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$user_id = current_user_id();

// Fetch User Addresses
$addr_stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$addr_stmt->execute([$user_id]);
$addresses = $addr_stmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    $address_id = (int)($_POST['address_id'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? '';
    $customer_notes = trim($_POST['customer_notes'] ?? '');

    if (!$address_id || !in_array($payment_method, ['COD', 'ONLINE'])) {
        $error = "Please select a valid address and payment method.";
    } else {
        // Fetch selected address to snapshot
        $sel_addr = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $sel_addr->execute([$address_id, $user_id]);
        $address = $sel_addr->fetch();

        if (!$address) {
            $error = "Invalid address selected.";
        } else {
            // Begin Transaction
            try {
                $pdo->beginTransaction();

                $subtotal = 0;
                $tax_amount = 0;
                $order_items_data = [];

                // Process Cart Items
                foreach ($_SESSION['cart'] as $key => $item) {
                    $p_id = $item['product_id'];
                    $v_id = $item['variant_id'];
                    $qty = $item['quantity'];

                    // Lock product and variant for update to prevent race condition
                    $st = $pdo->prepare("SELECT p.name, p.status, p.sku as p_sku, p.base_price, p.gst_percent,
                                         v.variant_name, v.price as v_price, v.sku as v_sku, v.stock_quantity, v.status as v_status
                                         FROM products p
                                         LEFT JOIN product_variants v ON v.id = ?
                                         WHERE p.id = ? FOR UPDATE");
                    $st->execute([$v_id, $p_id]);
                    $row = $st->fetch();

                    if (!$row) {
                        throw new Exception("Product missing from database.");
                    }

                    if ($row['status'] !== 'active' || ($v_id && $row['v_status'] !== 'active')) {
                        throw new Exception("Product {$row['name']} is no longer active.");
                    }

                    if ($v_id && $qty > $row['stock_quantity']) {
                        throw new Exception("Insufficient stock for {$row['name']} - {$row['variant_name']}.");
                    }

                    $unit_price = $v_id ? $row['v_price'] : $row['base_price'];
                    $line_total = $unit_price * $qty;
                    $subtotal += $line_total;

                    // Tax calculation (simplified inclusive or exclusive logic. Assuming price is base, and gst is % added for this demo)
                    $gst_perc = $row['gst_percent'];
                    $tax = ($line_total * $gst_perc) / 100;
                    $tax_amount += $tax;

                    $order_items_data[] = [
                        'product_id' => $p_id,
                        'variant_id' => $v_id,
                        'product_name' => $row['name'],
                        'sku' => $v_id ? $row['v_sku'] : $row['p_sku'],
                        'variant_name' => $row['variant_name'],
                        'quantity' => $qty,
                        'unit_price' => $unit_price,
                        'tax' => $tax,
                        'line_total' => $line_total,
                        'prev_stock' => $row['stock_quantity']
                    ];
                }

                $shipping_charge = 0; // Configurable later
                $discount = 0; // Configurable later
                $grand_total = $subtotal + $tax_amount + $shipping_charge - $discount;

                $order_number = 'PCW-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                $payment_status = $payment_method === 'COD' ? 'pending' : 'pending'; // For online, stays pending until gateway confirms

                // 1. Create Order
                $ins_order = $pdo->prepare("INSERT INTO orders (order_number, user_id, subtotal, discount, shipping_charge, tax_amount, grand_total, payment_method, payment_status, customer_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins_order->execute([$order_number, $user_id, $subtotal, $discount, $shipping_charge, $tax_amount, $grand_total, $payment_method, $payment_status, $customer_notes]);
                $order_id = $pdo->lastInsertId();

                // 2. Create Order Address Snapshot
                $ins_addr = $pdo->prepare("INSERT INTO order_addresses (order_id, full_name, phone, address_line_1, address_line_2, landmark, city, state, pincode, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins_addr->execute([$order_id, $address['full_name'], $address['phone'], $address['address_line_1'], $address['address_line_2'], $address['landmark'], $address['city'], $address['state'], $address['pincode'], $address['address_type']]);

                // 3. Create Order Items & Deduct Stock
                $ins_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, variant_id, product_name, sku, variant_name, quantity, unit_price, tax, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $upd_stock = $pdo->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $ins_inv_trans = $pdo->prepare("INSERT INTO inventory_transactions (product_id, variant_id, transaction_type, quantity, previous_quantity, new_quantity, reference_type, reference_id, created_by) VALUES (?, ?, 'sale', ?, ?, ?, 'order', ?, ?)");

                foreach ($order_items_data as $item) {
                    $ins_item->execute([$order_id, $item['product_id'], $item['variant_id'], $item['product_name'], $item['sku'], $item['variant_name'], $item['quantity'], $item['unit_price'], $item['tax'], $item['line_total']]);

                    if ($item['variant_id']) {
                        $upd_stock->execute([$item['quantity'], $item['variant_id']]);
                        $new_stock = $item['prev_stock'] - $item['quantity'];
                        $inv_qty = -$item['quantity']; // negative for sale
                        $ins_inv_trans->execute([$item['product_id'], $item['variant_id'], $inv_qty, $item['prev_stock'], $new_stock, $order_id, $user_id]);
                    }
                }

                // 4. Create History
                $pdo->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note) VALUES (?, NULL, 'pending', ?, 'Order Placed')")->execute([$order_id, $user_id]);

                // Commit
                $pdo->commit();

                // Clear Cart
                $_SESSION['cart'] = [];
                redirect("order-success.php?order=" . urlencode($order_number));

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Order failed: " . $e->getMessage();
            }
        }
    }
}

// Calculate totals for display
$display_subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    // Quick fetch for display. Since cart should be valid before checkout, we just sum up.
    $st = $pdo->prepare("SELECT price FROM product_variants WHERE id = ?");
    $st->execute([$item['variant_id']]);
    $p = $st->fetchColumn();
    $display_subtotal += $p * $item['quantity'];
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 max-w-6xl">
        <h1 class="text-3xl font-bold text-pcwBlack mb-8">Checkout</h1>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="checkout.php" class="flex flex-col lg:flex-row gap-8">
            <?php echo csrf_field(); ?>
            <div class="w-full lg:w-2/3 space-y-6">
                <!-- Address Section -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-bold text-gray-800">1. Shipping Address</h2>
                        <a href="account/addresses.php" class="text-blue-600 hover:underline text-sm font-bold">Add New Address</a>
                    </div>
                    <?php if (empty($addresses)): ?>
                        <p class="text-red-500 mb-4">You have no saved addresses. Please add an address before proceeding.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach($addresses as $idx => $addr): ?>
                                <label class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-pcwRed transition-colors relative block <?php echo $addr['is_default'] ? 'border-pcwRed bg-red-50/20' : ''; ?>">
                                    <input type="radio" name="address_id" value="<?php echo $addr['id']; ?>" class="absolute top-4 right-4" required <?php echo $addr['is_default'] ? 'checked' : ''; ?>>
                                    <h3 class="font-bold text-gray-800 mb-1"><?php echo e($addr['full_name']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-2"><?php echo e($addr['phone']); ?></p>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        <?php echo e($addr['address_line_1']); ?><br>
                                        <?php echo e($addr['city']); ?>, <?php echo e($addr['state']); ?> - <?php echo e($addr['pincode']); ?>

                                    </p>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Section -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">2. Payment Method</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="COD" required checked>
                            <span class="font-bold text-gray-800">Cash on Delivery (COD)</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="ONLINE" required>
                            <span class="font-bold text-gray-800">Online Payment (Cards, UPI, Netbanking)</span>
                            <span class="text-xs text-pcwRed ml-auto font-bold">Coming Soon</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Order Notes (Optional)</h2>
                    <textarea name="customer_notes" rows="3" class="w-full border border-gray-300 rounded p-3 outline-none focus:border-pcwRed" placeholder="Any special instructions..."></textarea>
                </div>
            </div>

            <!-- Summary -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Order Summary</h3>

                    <div class="space-y-3 mb-6 max-h-60 overflow-y-auto pr-2">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="flex justify-between items-start text-sm border-b border-gray-50 pb-2">
                            <div class="text-gray-600 w-3/4">
                                <?php echo $item['quantity']; ?>x <span class="font-bold"><?php echo e((function() use ($pdo, $item) { $s = $pdo->prepare("SELECT name FROM products WHERE id=?"); $s->execute([$item['product_id']]); return $s->fetchColumn(); })()); ?></span>
                                <div class="text-xs text-gray-400 truncate"><?php echo e((function() use ($pdo, $item) { $s = $pdo->prepare("SELECT variant_name FROM product_variants WHERE id=?"); $s->execute([$item['variant_id']]); return $s->fetchColumn(); })()); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="space-y-4 mb-6 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-bold"><?php echo format_price($display_subtotal); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span class="text-green-600 font-bold">Free</span>
                        </div>
                    </div>

                    <div class="border-t pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-lg text-gray-800">Total</span>
                            <span class="font-bold text-xl text-pcwRed"><?php echo format_price($display_subtotal); ?></span>
                        </div>
                    </div>

                    <button type="submit" class="block w-full bg-pcwRed hover:bg-red-800 text-white text-center font-bold py-3 rounded-lg transition-colors shadow-md text-sm sm:text-base">
                        Place Order
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>