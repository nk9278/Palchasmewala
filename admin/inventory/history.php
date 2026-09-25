<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin($pdo);


$query = "SELECT t.*, p.name as product_name, v.variant_name, v.sku
          FROM inventory_transactions t
          JOIN products p ON t.product_id = p.id
          JOIN product_variants v ON t.variant_id = v.id
          ORDER BY t.created_at DESC LIMIT 100";
$history = $pdo->query($query)->fetchAll();
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Inventory Transaction History</h1>
            <a href="index.php" class="bg-gray-600 text-white px-4 py-2 rounded">Back to Inventory</a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Product / Variant</th>
                        <th class="py-3 px-4 text-left">Type</th>
                        <th class="py-3 px-4 text-left">Change</th>
                        <th class="py-3 px-4 text-left">Balance</th>
                        <th class="py-3 px-4 text-left">Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr class="border-t">
                        <td class="py-3 px-4"><?php echo e($h['created_at']); ?></td>
                        <td class="py-3 px-4"><?php echo e($h['product_name'] . ' / ' . $h['variant_name'] . ' (' . $h['sku'] . ')'); ?></td>
                        <td class="py-3 px-4 capitalize"><?php echo e($h['transaction_type']); ?></td>
                        <td class="py-3 px-4 font-bold <?php echo $h['quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $h['quantity'] > 0 ? '+' : ''; ?><?php echo e($h['quantity']); ?>
                        </td>
                        <td class="py-3 px-4"><?php echo e($h['new_quantity']); ?></td>
                        <td class="py-3 px-4 text-gray-500 text-xs"><?php echo e($h['note']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
