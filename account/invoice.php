<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

// Auth checks. Admin can view any invoice, customers can view their own.
if (!is_logged_in()) {
    redirect('login.php');
}

$invoice_id = $_GET['id'] ?? 0;
$user_id = current_user_id();
$is_admin = is_admin($pdo);

if ($is_admin) {
    $stmt = $pdo->prepare("SELECT i.*, o.order_number, o.created_at as order_date, o.payment_method, o.payment_status, o.user_id
                           FROM invoices i JOIN orders o ON i.order_id = o.id
                           WHERE i.id = ?");
    $stmt->execute([$invoice_id]);
} else {
    $stmt = $pdo->prepare("SELECT i.*, o.order_number, o.created_at as order_date, o.payment_method, o.payment_status
                           FROM invoices i JOIN orders o ON i.order_id = o.id
                           WHERE i.id = ? AND o.user_id = ?");
    $stmt->execute([$invoice_id, $user_id]);
}

$invoice = $stmt->fetch();
if (!$invoice) die("Invoice not found or access denied.");

// Fetch Address
$addr = $pdo->prepare("SELECT * FROM order_addresses WHERE order_id = ?");
$addr->execute([$invoice['order_id']]);
$address = $addr->fetch();

// Fetch Items
$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$invoice['order_id']]);
$items = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo e($invoice['invoice_number']); ?> - Pal Chasme Wale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-border { border: 1px solid #e5e7eb !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans p-4 sm:p-8">

    <div class="max-w-4xl mx-auto">
        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-6 no-print">
            <a href="javascript:history.back()" class="text-blue-600 hover:underline font-bold">&larr; Back</a>
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700 transition-colors shadow-sm">
                Print Invoice
            </button>
        </div>

        <!-- Invoice Document -->
        <div class="bg-white p-8 sm:p-12 shadow-sm rounded-lg print-border">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start border-b pb-8 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-1 tracking-tight">INVOICE</h1>
                    <p class="text-gray-500 text-sm">Invoice #<?php echo e($invoice['invoice_number']); ?></p>
                    <p class="text-gray-500 text-sm">Date: <?php echo date('d M Y', strtotime($invoice['invoice_date'])); ?></p>
                </div>
                <div class="mt-6 md:mt-0 text-left md:text-right">
                    <h2 class="text-xl font-bold text-gray-800 tracking-tighter">Pal Chasme Wale</h2>
                    <p class="text-gray-600 text-sm mt-1">G.N. Road, Peepal Wali Gali,<br>Sultanpur, Uttar Pradesh</p>
                    <p class="text-gray-600 text-sm mt-1">GSTIN: 09FIDPP0678D1Z3</p>
                    <p class="text-gray-600 text-sm mt-1">Phone: 9005632555</p>
                </div>
            </div>

            <!-- Meta & Address -->
            <div class="flex flex-col sm:flex-row justify-between gap-8 mb-8">
                <div>
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Billed To</h3>
                    <?php if($address): ?>
                        <p class="font-bold text-gray-800"><?php echo e($address['full_name']); ?></p>
                        <p class="text-sm text-gray-600 mt-1"><?php echo e($address['address_line_1']); ?><br>
                        <?php if($address['address_line_2']) echo e($address['address_line_2']) . '<br>'; ?>
                        <?php echo e($address['city']); ?>, <?php echo e($address['state']); ?> - <?php echo e($address['pincode']); ?></p>
                        <p class="text-sm text-gray-600 mt-1">Phone: <?php echo e($address['phone']); ?></p>
                    <?php else: ?>
                        <p class="text-sm text-gray-600">Address missing.</p>
                    <?php endif; ?>
                </div>

                <div class="sm:text-right">
                    <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Order Details</h3>
                    <p class="text-sm text-gray-800 mb-1"><span class="font-bold">Order #:</span> <?php echo e($invoice['order_number']); ?></p>
                    <p class="text-sm text-gray-800 mb-1"><span class="font-bold">Order Date:</span> <?php echo date('d M Y', strtotime($invoice['order_date'])); ?></p>
                    <p class="text-sm text-gray-800 mb-1"><span class="font-bold">Payment Method:</span> <?php echo e($invoice['payment_method']); ?></p>
                    <p class="text-sm text-gray-800"><span class="font-bold">Payment Status:</span> <span class="uppercase"><?php echo e($invoice['payment_status']); ?></span></p>
                </div>
            </div>

            <!-- Items -->
            <table class="w-full text-left mb-8 text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="py-3 font-bold text-gray-800">Item</th>
                        <th class="py-3 font-bold text-gray-800 text-center">Qty</th>
                        <th class="py-3 font-bold text-gray-800 text-right">Unit Price</th>
                        <th class="py-3 font-bold text-gray-800 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($items as $i): ?>
                    <tr>
                        <td class="py-4">
                            <p class="font-bold text-gray-800"><?php echo e($i['product_name']); ?></p>
                            <p class="text-xs text-gray-500 mt-1">SKU: <?php echo e($i['sku']); ?><?php echo $i['variant_name'] ? " | Option: " . e($i['variant_name']) : ""; ?></p>
                        </td>
                        <td class="py-4 text-center"><?php echo $i['quantity']; ?></td>
                        <td class="py-4 text-right text-gray-600"><?php echo format_price($i['unit_price']); ?></td>
                        <td class="py-4 text-right font-bold text-gray-800"><?php echo format_price($i['line_total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="flex justify-end">
                <div class="w-full sm:w-1/2 lg:w-1/3 space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span><?php echo format_price($invoice['subtotal']); ?></span>
                    </div>
                    <?php if($invoice['discount'] > 0): ?>
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span>-<?php echo format_price($invoice['discount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span><?php echo format_price($invoice['shipping_amount']); ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600 border-b pb-3">
                        <span>Tax Amount</span>
                        <span><?php echo format_price($invoice['tax_amount']); ?></span>
                    </div>
                    <div class="flex justify-between font-bold text-lg text-gray-900 pt-1">
                        <span>Grand Total</span>
                        <span><?php echo format_price($invoice['grand_total']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Footer Notes -->
            <div class="border-t mt-12 pt-6 text-center text-xs text-gray-500">
                <p>This is a computer generated invoice and does not require a signature.</p>
                <p class="mt-1">Thank you for shopping with Pal Chasme Wale!</p>
            </div>

        </div>
    </div>

</body>
</html>