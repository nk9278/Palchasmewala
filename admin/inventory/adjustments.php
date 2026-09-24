<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

$variants = $pdo->query("SELECT v.id, v.sku, v.variant_name, p.name as product_name
                         FROM product_variants v
                         JOIN products p ON v.product_id = p.id
                         ORDER BY p.name ASC, v.variant_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF Token verification failed.");
    }

    $variant_id = $_POST['variant_id'] ?? 0;
    $adjustment = (int)($_POST['adjustment'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    if ($variant_id && $adjustment != 0) {
        $pdo->beginTransaction();

        try {
            // Lock variant row for update
            $stmt = $pdo->prepare("SELECT product_id, stock_quantity FROM product_variants WHERE id = ? FOR UPDATE");
            $stmt->execute([$variant_id]);
            $variant = $stmt->fetch();

            if ($variant) {
                $prev_qty = $variant['stock_quantity'];
                $new_qty = $prev_qty + $adjustment;

                // Update stock
                $upd = $pdo->prepare("UPDATE product_variants SET stock_quantity = ? WHERE id = ?");
                $upd->execute([$new_qty, $variant_id]);

                // Record transaction
                $trans = $pdo->prepare("INSERT INTO inventory_transactions (product_id, variant_id, transaction_type, quantity, previous_quantity, new_quantity, note) VALUES (?, ?, 'manual', ?, ?, ?, ?)");
                $trans->execute([$variant['product_id'], $variant_id, $adjustment, $prev_qty, $new_qty, $note]);

                $pdo->commit();
                redirect('admin/inventory/index.php');
            } else {
                $pdo->rollBack();
                $error = "Variant not found.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update inventory.";
        }
    } else {
        $error = "Invalid adjustment amount or variant.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Adjust Stock - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-8 max-w-lg">
        <h1 class="text-3xl font-bold mb-6">Manual Stock Adjustment</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-200 text-red-800 p-3 mb-4 rounded"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" class="bg-white p-6 rounded shadow space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700">Variant</label>
                <select name="variant_id" required class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="">Select Variant...</option>
                    <?php foreach($variants as $v): ?>
                        <option value="<?php echo $v['id']; ?>"><?php echo e($v['product_name'] . ' - ' . $v['variant_name'] . ' (' . $v['sku'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Adjustment Quantity (e.g. +5 or -2)</label>
                <input type="number" name="adjustment" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Note / Reason</label>
                <textarea name="note" class="mt-1 block w-full border border-gray-300 rounded p-2" required></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Adjust Stock</button>
        </form>
    </div>
</body>
</html>