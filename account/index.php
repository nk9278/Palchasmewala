<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();
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
                    <a href="index.php" class="px-6 py-3 font-semibold text-pcwRed bg-gray-50 border-l-4 border-pcwRed">Dashboard</a>
                    <a href="profile.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">Profile Information</a>
                    <a href="addresses.php" class="px-6 py-3 text-gray-600 hover:text-pcwBlack hover:bg-gray-50 transition-colors">Manage Addresses</a>
                    <a href="#" class="px-6 py-3 text-gray-400 cursor-not-allowed">My Orders (Coming Soon)</a>
                    <a href="../logout.php" class="px-6 py-3 text-red-500 hover:bg-red-50 transition-colors border-t mt-2">Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="w-full md:w-3/4">
            <h1 class="text-3xl font-bold text-pcwBlack mb-6">Dashboard</h1>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 cursor-pointer hover:shadow-md transition-shadow" onclick="window.location='profile.php'">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Profile</h3>
                        <p class="text-xs text-gray-500">Edit details</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 cursor-pointer hover:shadow-md transition-shadow" onclick="window.location='addresses.php'">
                    <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Addresses</h3>
                        <p class="text-xs text-gray-500">Manage saved</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 opacity-50 cursor-not-allowed">
                    <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Orders</h3>
                        <p class="text-xs text-gray-500">View history</p>
                    </div>
                </div>

            </div>
        </main>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>