<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $address1 = trim($_POST['address_line_1']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $pincode = trim($_POST['pincode']);

        if ($name && $phone && $address1 && $city && $state && $pincode) {
            // Unset default if new is set
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            if ($is_default) {
                $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
            }

            $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, full_name, phone, address_line_1, city, state, pincode, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $phone, $address1, $city, $state, $pincode, $is_default]);
            $success = "Address added successfully.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['address_id'];
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $success = "Address deleted.";
    } elseif ($action === 'set_default') {
        $id = (int)$_POST['address_id'];
        $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);
        $success = "Default address updated.";
    }
}

$addresses = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$addresses->execute([$user_id]);
$addresses = $addresses->fetchAll();
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
                    <a href="addresses.php" class="px-6 py-3 font-semibold text-pcwRed bg-gray-50 border-l-4 border-pcwRed transition-colors">Manage Addresses</a>
                    <a href="orders.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">My Orders</a>
                    <a href="../logout.php" class="px-6 py-3 text-red-500 hover:bg-red-50 transition-colors border-t mt-2">Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="w-full md:w-3/4">
            <h1 class="text-3xl font-bold text-pcwBlack mb-6">Manage Addresses</h1>

            <?php if($success): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-6 text-sm border border-green-200"><?php echo e($success); ?></div>
            <?php endif; ?>

            <!-- Add New Address Form -->
            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100 mb-8">
                <h2 class="text-lg font-bold mb-4 border-b pb-2">Add New Address</h2>
                <form method="post" action="addresses.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add">

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Address Line 1</label>
                        <input type="text" name="address_line_1" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">City</label>
                        <input type="text" name="city" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">State</label>
                        <input type="text" name="state" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Pincode</label>
                        <input type="text" name="pincode" required class="w-full border border-gray-300 rounded p-2 text-sm outline-none focus:border-pcwRed">
                    </div>
                    <div class="md:col-span-2 flex items-center gap-2 mt-2">
                        <input type="checkbox" name="is_default" id="is_default">
                        <label for="is_default" class="text-sm text-gray-600 font-bold">Set as Default Address</label>
                    </div>
                    <div class="md:col-span-2 mt-2">
                        <button type="submit" class="bg-gray-800 hover:bg-black text-white font-bold py-2 px-6 rounded transition-colors text-sm">Save Address</button>
                    </div>
                </form>
            </div>

            <!-- Saved Addresses -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach($addresses as $addr): ?>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 relative">
                    <?php if($addr['is_default']): ?>
                        <span class="absolute top-4 right-4 bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Default</span>
                    <?php endif; ?>
                    <h3 class="font-bold text-gray-800 mb-1"><?php echo e($addr['full_name']); ?></h3>
                    <p class="text-sm text-gray-600 mb-3"><?php echo e($addr['phone']); ?></p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        <?php echo e($addr['address_line_1']); ?><br>
                        <?php echo e($addr['city']); ?>, <?php echo e($addr['state']); ?> - <?php echo e($addr['pincode']); ?>

                    </p>

                    <div class="mt-4 flex gap-3 pt-3 border-t border-gray-100">
                        <?php if(!$addr['is_default']): ?>
                        <form method="post" class="inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="set_default">
                            <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                            <button type="submit" class="text-xs text-blue-600 hover:underline font-bold">Set Default</button>
                        </form>
                        <?php endif; ?>

                        <form method="post" class="inline ml-auto" onsubmit="return confirm('Are you sure you want to delete this address?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="address_id" value="<?php echo $addr['id']; ?>">
                            <button type="submit" class="text-xs text-red-500 hover:underline font-bold">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </main>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>