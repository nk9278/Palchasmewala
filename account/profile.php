<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name) {
        $upd = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $upd->execute([$name, $email ?: null, $user_id]);
        $_SESSION['user_name'] = $name;
        $user['name'] = $name;
        $user['email'] = $email;
        $success = "Profile updated successfully.";
    }
}
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
                    <a href="profile.php" class="px-6 py-3 font-semibold text-pcwRed bg-gray-50 border-l-4 border-pcwRed transition-colors">Profile Information</a>
                    <a href="addresses.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">Manage Addresses</a>
                    <a href="orders.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">My Orders</a>
                    <a href="reviews.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">My Reviews</a>
                    <a href="../logout.php" class="px-6 py-3 text-red-500 hover:bg-red-50 transition-colors border-t mt-2">Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="w-full md:w-3/4">
            <h1 class="text-3xl font-bold text-pcwBlack mb-6">Profile Information</h1>

            <?php if($success): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-6 text-sm border border-green-200"><?php echo e($success); ?></div>
            <?php endif; ?>

            <form method="post" action="profile.php" class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100 max-w-2xl space-y-5">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                    <input type="text" value="<?php echo e($user['phone']); ?>" disabled class="w-full border border-gray-200 rounded-lg p-2.5 bg-gray-50 text-gray-500 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Phone number cannot be changed directly.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="<?php echo e($user['name']); ?>" required class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?php echo e($user['email']); ?>" class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
                </div>

                <button type="submit" class="bg-pcwRed hover:bg-red-800 text-white font-bold py-2.5 px-8 rounded-lg transition-colors shadow-md mt-2">
                    Save Changes
                </button>
            </form>
        </main>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>