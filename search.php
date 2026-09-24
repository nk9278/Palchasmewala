<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
// We'll just map /search.php to /shop.php search logic
$q = $_GET['q'] ?? '';
if ($q) {
    redirect("shop.php?search=" . urlencode($q));
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="bg-gray-50 py-16 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-gray-100 max-w-lg w-full mx-4 text-center">
        <h1 class="text-3xl font-bold text-pcwBlack mb-6">Search</h1>
        <form method="get" action="shop.php" class="flex gap-2">
            <input type="text" name="search" placeholder="Search products, brands, categories..." class="w-full border-2 border-gray-200 rounded-lg p-3 outline-none focus:border-pcwRed" autofocus>
            <button type="submit" class="bg-pcwRed text-white px-6 py-3 rounded-lg font-bold hover:bg-red-800 transition-colors">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
