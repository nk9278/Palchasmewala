<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_admin($pdo);

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT id, name, email, phone, status, role, created_at FROM users WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) die("Customer not found.");

$orders_stmt = $pdo->prepare("SELECT id, order_number, grand_total, order_status, payment_status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->execute([$id]);
$orders = $orders_stmt->fetchAll();

$addr_stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
$addr_stmt->execute([$id]);
$addresses = $addr_stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="w-full max-w-6xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Customer Profile</h1>
        <a href="index.php" class="bg-gray-300 px-4 py-2 rounded text-sm font-bold">Back to Customers</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Customer Details -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Profile</h2>
                <p class="mb-2 text-lg font-bold text-gray-800"><?php echo e($customer['name']); ?></p>
                <p class="mb-2 text-sm text-gray-600"><i class="fa-solid fa-phone w-5"></i> <?php echo e($customer['phone']); ?></p>
                <p class="mb-4 text-sm text-gray-600"><i class="fa-solid fa-envelope w-5"></i> <?php echo e($customer['email'] ?? 'N/A'); ?></p>
                <div class="text-sm border-t pt-4">
                    <p class="mb-1"><strong>Status:</strong> <span class="uppercase text-xs font-bold px-2 py-0.5 rounded <?php echo $customer['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"><?php echo e($customer['status']); ?></span></p>
                    <p class="mb-1"><strong>Role:</strong> <?php echo e(ucfirst($customer['role'])); ?></p>
                    <p class="text-gray-500"><strong>Joined:</strong> <?php echo date('d M Y', strtotime($customer['created_at'])); ?></p>
                </div>
            </div>

            <!-- Addresses -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Saved Addresses</h2>
                <?php if(empty($addresses)): ?>
                    <p class="text-sm text-gray-500">No addresses saved.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($addresses as $a): ?>
                        <div class="p-3 border rounded text-sm relative <?php echo $a['is_default'] ? 'border-pcwRed bg-red-50/20' : ''; ?>">
                            <?php if($a['is_default']): ?><span class="absolute top-2 right-2 text-xs font-bold text-pcwRed bg-white px-1">Default</span><?php endif; ?>
                            <p class="font-bold text-gray-800"><?php echo e($a['full_name']); ?></p>
                            <p class="text-gray-600 mt-1">
                                <?php echo e($a['address_line_1']); ?><br>
                                <?php if($a['address_line_2']) echo e($a['address_line_2']) . '<br>'; ?>
                                <?php echo e($a['city']); ?>, <?php echo e($a['state']); ?> - <?php echo e($a['pincode']); ?>

                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Orders -->
        <div class="md:col-span-2">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 border-b pb-2">Order History</h2>
                <?php if(empty($orders)): ?>
                    <p class="text-sm text-gray-500">This customer hasn't placed any orders yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="py-2 px-3 text-left">Order #</th>
                                    <th class="py-2 px-3 text-left">Date</th>
                                    <th class="py-2 px-3 text-left">Total</th>
                                    <th class="py-2 px-3 text-left">Order Status</th>
                                    <th class="py-2 px-3 text-left">Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $o): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="py-3 px-3"><a href="../orders/view.php?id=<?php echo $o['id']; ?>" class="text-blue-600 font-bold hover:underline"><?php echo e($o['order_number']); ?></a></td>
                                    <td class="py-3 px-3 text-gray-500"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                                    <td class="py-3 px-3 font-bold"><?php echo format_price($o['grand_total']); ?></td>
                                    <td class="py-3 px-3"><span class="uppercase text-xs font-bold"><?php echo e($o['order_status']); ?></span></td>
                                    <td class="py-3 px-3"><span class="uppercase text-[10px] font-bold px-2 py-1 rounded <?php echo $o['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'; ?>"><?php echo e($o['payment_status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>