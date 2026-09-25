<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();

$stmt = $pdo->prepare("SELECT r.*, p.name as product_name, p.slug FROM product_reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$reviews = $stmt->fetchAll();
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
                    <a href="orders.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">My Orders</a>
                    <a href="reviews.php" class="px-6 py-3 font-semibold text-pcwRed bg-gray-50 border-l-4 border-pcwRed transition-colors">My Reviews</a>
                    <a href="../logout.php" class="px-6 py-3 text-red-500 hover:bg-red-50 transition-colors border-t mt-2">Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="w-full md:w-3/4">
            <h1 class="text-3xl font-bold text-pcwBlack mb-6">My Reviews</h1>

            <?php if(empty($reviews)): ?>
                <div class="bg-white p-12 text-center rounded-xl shadow-sm border border-gray-100">
                    <i class="fa-regular fa-star text-4xl text-gray-300 mb-4 block"></i>
                    <p class="text-gray-500">You haven't reviewed any products yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($reviews as $r): ?>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col gap-3">
                            <div class="flex justify-between items-start border-b border-gray-50 pb-3">
                                <div>
                                    <h3 class="font-bold text-gray-800 hover:text-pcwRed">
                                        <a href="../product.php?slug=<?php echo urlencode($r['slug']); ?>"><?php echo e($r['product_name']); ?></a>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('d M Y', strtotime($r['created_at'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <?php
                                        $color = 'bg-gray-200 text-gray-700';
                                        if($r['status'] == 'approved') $color = 'bg-green-100 text-green-800';
                                        if($r['status'] == 'rejected') $color = 'bg-red-100 text-red-800';
                                    ?>
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?php echo $color; ?>"><?php echo e($r['status']); ?></span>
                                    <div class="text-pcwGold text-xs mt-2">
                                        <?php echo str_repeat('<i class="fa-solid fa-star"></i>', $r['rating']) . str_repeat('<i class="fa-regular fa-star text-gray-300"></i>', 5 - $r['rating']); ?>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed"><?php echo nl2br(e($r['review'])); ?></p>
                            <?php if($r['image_path']): ?>
                                <img src="<?php echo e($r['image_path']); ?>" alt="Review" class="w-20 h-20 object-cover rounded border border-gray-200 mt-2">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>