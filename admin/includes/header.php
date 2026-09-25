<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pal Chasme Wale - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pcwRed: '#C00000',
                        pcwGold: '#D4AF37',
                        pcwBlack: '#111111',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-pcwBlack text-white flex flex-col h-full shadow-xl z-20 flex-shrink-0">
        <div class="p-6 border-b border-gray-800 text-center">
            <h2 class="text-2xl font-bold text-pcwGold tracking-wider">Admin Panel</h2>
            <p class="text-xs text-gray-400 mt-1 uppercase">Pal Chasme Wale</p>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            <a href="/admin/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-gauge w-6"></i> Dashboard
            </a>
            <a href="/admin/orders/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-box w-6"></i> Orders
            </a>
            <a href="/admin/returns/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-rotate-left w-6"></i> Returns
            </a>
            <a href="/admin/products/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-glasses w-6"></i> Products
            </a>
            <a href="/admin/categories/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-list w-6"></i> Categories
            </a>
            <a href="/admin/brands/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-tags w-6"></i> Brands
            </a>
            <a href="/admin/inventory/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-warehouse w-6"></i> Inventory
            </a>
            <a href="/admin/customers/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-users w-6"></i> Customers
            </a>
            <a href="/admin/reviews/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-star w-6"></i> Reviews
            </a>
            <a href="/admin/reports/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <i class="fa-solid fa-chart-line w-6"></i> Reports
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <a href="/index.php" class="block w-full text-center bg-gray-800 hover:bg-gray-700 text-sm py-2 rounded transition-colors">
                View Store
            </a>
            <a href="/logout.php" class="block w-full text-center text-red-400 hover:text-red-300 text-sm py-2 mt-2 transition-colors">
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Header -->
        <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0 z-10">
            <h1 class="text-xl font-bold text-gray-800">Admin Operations</h1>
            <div class="text-sm font-semibold text-gray-500">
                Logged in as: <span class="text-pcwBlack"><?php echo e($_SESSION['user_name'] ?? 'Admin'); ?></span>
            </div>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-8">
            <!-- Content goes here -->
