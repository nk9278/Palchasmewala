<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT r.*, o.order_number, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                       FROM returns r JOIN orders o ON r.order_id = o.id JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$return_req = $stmt->fetch();

if (!$return_req) die("Return request not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF validation failed.");

    $new_status = $_POST['status'] ?? '';
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    if ($new_status && $new_status !== $return_req['status']) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE returns SET status = ?, admin_notes = ? WHERE id = ?")->execute([$new_status, $admin_notes, $id]);

            // If approved or received, typically you update order_status to 'returned' or 'partially_returned' and restore stock.
            // For Phase 4 foundation, we will simply log order history.
            $pdo->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note) VALUES (?, NULL, 'Return $new_status', NULL, 'Admin updated return request #$id to $new_status')")
                ->execute([$return_req['order_id']]);

            $pdo->commit();
            redirect("admin/returns/view.php?id=$id");
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update return status.";
        }
    }
}

$items = $pdo->prepare("SELECT ri.*, oi.product_name, oi.variant_name, oi.sku, oi.unit_price
                        FROM return_items ri
                        JOIN order_items oi ON ri.order_item_id = oi.id
                        WHERE ri.return_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Return - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-8">
    <div class="container mx-auto max-w-5xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Return Request #RET-<?php echo e($return_req['id']); ?></h1>
            <a href="index.php" class="bg-gray-300 px-4 py-2 rounded">Back</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Items & Info -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Return Details</h2>
                    <p class="mb-2"><strong>Order:</strong> <a href="../orders/view.php?id=<?php echo $return_req['order_id']; ?>" class="text-blue-600 underline"><?php echo e($return_req['order_number']); ?></a></p>
                    <p class="mb-2"><strong>Reason:</strong> <?php echo e($return_req['reason']); ?></p>
                    <p class="mb-4"><strong>Customer Notes:</strong> <?php echo nl2br(e($return_req['customer_notes'])); ?></p>

                    <h3 class="font-bold text-gray-800 mb-2">Items to Return:</h3>
                    <div class="space-y-3">
                        <?php foreach($items as $i): ?>
                            <div class="bg-gray-50 border p-3 rounded text-sm">
                                <span class="font-bold"><?php echo e($i['product_name']); ?></span><br>
                                <span class="text-xs text-gray-500">Option: <?php echo e($i['variant_name']); ?> | SKU: <?php echo e($i['sku']); ?></span><br>
                                <span>Qty to return: <strong><?php echo $i['quantity']; ?></strong></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Manage Actions -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Manage Status</h2>
                    <form method="post" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-sm font-bold mb-1">Status</label>
                            <select name="status" class="w-full border p-2 rounded">
                                <?php
                                $statuses = ['requested', 'approved', 'rejected', 'received', 'refunded', 'cancelled'];
                                foreach($statuses as $s) {
                                    $sel = $return_req['status'] === $s ? 'selected' : '';
                                    echo "<option value='$s' $sel>".ucfirst($s)."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Admin Notes (Internal)</label>
                            <textarea name="admin_notes" rows="3" class="w-full border p-2 rounded"><?php echo e($return_req['admin_notes']); ?></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded mt-2 hover:bg-blue-700">Update Return</button>
                    </form>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Customer Info</h2>
                    <p class="font-bold"><?php echo e($return_req['customer_name']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo e($return_req['customer_phone']); ?><br><?php echo e($return_req['customer_email']); ?></p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>