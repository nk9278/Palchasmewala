<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 max-w-5xl flex flex-col md:flex-row gap-8">

        <!-- Account Sidebar -->
        <aside class="w-full md:w-1/4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                <div class="p-6 bg-gray-900 text-white">
                    <h2 class="text-xl font-bold">My Account</h2>
                    <p class="text-sm text-gray-400 mt-1">Hello, <?php echo e($_SESSION['user_name']); ?></p>
                </div>
                <div class="flex flex-col py-2">
                    <a href="index.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">Dashboard</a>
                    <a href="profile.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">Profile Information</a>
                    <a href="addresses.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">Manage Addresses</a>
                    <a href="orders.php" class="px-6 py-3 font-semibold text-pcwRed bg-gray-50 border-l-4 border-pcwRed transition-colors">My Orders</a>
                    <a href="reviews.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">My Reviews</a>
                    <a href="../logout.php" class="px-6 py-3 text-red-500 hover:bg-red-50 transition-colors border-t mt-2">Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="w-full md:w-3/4">
            <h1 class="text-3xl font-bold text-pcwBlack mb-6">My Orders</h1>

            <?php if (empty($orders)): ?>
                <div class="bg-white p-12 text-center rounded-xl shadow-sm border border-gray-100">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500 mb-4">You have not placed any orders yet.</p>
                    <a href="../shop.php" class="text-pcwRed hover:underline font-bold">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($orders as $o): ?>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between sm:items-center gap-4 hover:shadow-md transition-shadow">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg mb-1"><?php echo e($o['order_number']); ?></h3>
                                <p class="text-sm text-gray-500 mb-2">Placed on <?php echo date('d M Y', strtotime($o['created_at'])); ?></p>
                                <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 text-gray-600 capitalize"><?php echo e($o['order_status']); ?></span>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-bold text-pcwRed text-xl mb-2"><?php echo format_price($o['grand_total']); ?></p>
                                <div class="flex flex-col sm:flex-row gap-2 justify-end">
                                    <a href="order.php?id=<?php echo $o['id']; ?>" class="inline-block border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold px-4 py-1.5 rounded text-sm transition-colors text-center">View Details</a>                                    <?php
                                    $inv = $pdo->prepare("SELECT id FROM invoices WHERE order_id = ?");
                                    $inv->execute([$o['id']]);
                                    $inv_id = $inv->fetchColumn();

                                    // Lazy Generation Fallback
                                    if (!$inv_id && !in_array($o['order_status'], ['cancelled'])) {
                                        $gen = generate_invoice($pdo, $o['id']);
                                        if ($gen['success']) {
                                            $inv->execute([$o['id']]);
                                            $inv_id = $inv->fetchColumn();
                                        }
                                    }

                                    if($inv_id):
                                    ?>
                                        <a href="invoice.php?id=<?php echo $inv_id; ?>" class="inline-block border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold px-4 py-1.5 rounded text-sm transition-colors text-center">View Invoice</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>