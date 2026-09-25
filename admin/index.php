<?php
// admin/index.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin($pdo);

// 1. KPI Queries
$kpi_orders = $pdo->query("SELECT COUNT(id) FROM orders WHERE order_status NOT IN ('cancelled', 'refunded', 'returned')")->fetchColumn();
$kpi_revenue = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE order_status NOT IN ('cancelled', 'refunded', 'returned')")->fetchColumn();
$kpi_customers = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'customer'")->fetchColumn();
$kpi_products = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn();
$kpi_pending_orders = $pdo->query("SELECT COUNT(id) FROM orders WHERE order_status = 'pending'")->fetchColumn();
$kpi_pending_reviews = $pdo->query("SELECT COUNT(id) FROM product_reviews WHERE status = 'pending'")->fetchColumn();
$kpi_pending_returns = $pdo->query("SELECT COUNT(id) FROM returns WHERE status = 'requested'")->fetchColumn();
$kpi_low_stock = $pdo->query("SELECT COUNT(id) FROM product_variants WHERE stock_quantity <= low_stock_threshold AND status = 'active'")->fetchColumn();

// 2. Recent Orders
$recent_orders = $pdo->query("SELECT o.id, o.order_number, o.grand_total, o.order_status, o.payment_status, o.created_at, u.name as customer_name
                              FROM orders o JOIN users u ON o.user_id = u.id
                              ORDER BY o.created_at DESC LIMIT 10")->fetchAll();

// 3. Sales Trend (Last 7 Days)
$trend_stmt = $pdo->prepare("
    SELECT DATE(created_at) as sale_date, COUNT(id) as daily_orders, COALESCE(SUM(grand_total), 0) as daily_revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND order_status NOT IN ('cancelled', 'refunded', 'returned')
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
");
$trend_stmt->execute();
$sales_trend = $trend_stmt->fetchAll();

// 4. Top Products
$top_prod = $pdo->prepare("SELECT i.product_name, SUM(i.quantity) as units_sold, SUM(i.line_total) as revenue
                           FROM order_items i
                           JOIN orders o ON i.order_id = o.id
                           WHERE o.order_status NOT IN ('cancelled', 'refunded', 'returned')
                           GROUP BY i.product_id, i.product_name
                           ORDER BY units_sold DESC LIMIT 5");
$top_prod->execute();
$top_products = $top_prod->fetchAll();

// 5. Recent Inventory Adjustments
$inv_stmt = $pdo->prepare("SELECT t.*, p.name as product_name, v.variant_name
                           FROM inventory_transactions t
                           JOIN products p ON t.product_id = p.id
                           JOIN product_variants v ON t.variant_id = v.id
                           ORDER BY t.created_at DESC LIMIT 5");
$inv_stmt->execute();
$recent_inventory = $inv_stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="w-full max-w-7xl space-y-8">

    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">Overview</h1>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        <!-- Revenue -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo format_price($kpi_revenue); ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center"><i class="fa-solid fa-indian-rupee-sign"></i></div>
        </div>
        <!-- Orders -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Valid Orders</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $kpi_orders; ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-box"></i></div>
        </div>
        <!-- Customers -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Customers</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $kpi_customers; ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center"><i class="fa-solid fa-users"></i></div>
        </div>
        <!-- Products -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Products</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $kpi_products; ?></p>
            </div>
            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center"><i class="fa-solid fa-glasses"></i></div>
        </div>
    </div>

    <!-- Action Alerts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="orders/index.php?status=pending" class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 p-4 rounded-xl flex items-center gap-3 transition-colors">
            <span class="text-yellow-600 font-bold text-xl"><?php echo $kpi_pending_orders; ?></span>
            <span class="text-sm font-bold text-yellow-800">Pending Orders</span>
        </a>
        <a href="returns/index.php" class="bg-orange-50 hover:bg-orange-100 border border-orange-200 p-4 rounded-xl flex items-center gap-3 transition-colors">
            <span class="text-orange-600 font-bold text-xl"><?php echo $kpi_pending_returns; ?></span>
            <span class="text-sm font-bold text-orange-800">Return Requests</span>
        </a>
        <a href="reviews/index.php?status=pending" class="bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 p-4 rounded-xl flex items-center gap-3 transition-colors">
            <span class="text-indigo-600 font-bold text-xl"><?php echo $kpi_pending_reviews; ?></span>
            <span class="text-sm font-bold text-indigo-800">Pending Reviews</span>
        </a>
        <a href="inventory/index.php" class="bg-red-50 hover:bg-red-100 border border-red-200 p-4 rounded-xl flex items-center gap-3 transition-colors">
            <span class="text-red-600 font-bold text-xl"><?php echo $kpi_low_stock; ?></span>
            <span class="text-sm font-bold text-red-800">Low Stock Items</span>
        </a>
    </div>

    <!-- Analytics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

        <!-- Sales Trend -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Sales Trend (Last 7 Days)</h2>
            <?php if(empty($sales_trend)): ?>
                <p class="text-sm text-gray-500">No recent sales data.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach($sales_trend as $st): ?>
                    <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-2 last:border-0">
                        <span class="text-gray-600 font-bold"><?php echo date('M d', strtotime($st['sale_date'])); ?></span>
                        <span class="text-gray-500"><?php echo $st['daily_orders']; ?> orders</span>
                        <span class="text-pcwRed font-bold"><?php echo format_price($st['daily_revenue']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Top Products & Inventory -->
        <div class="space-y-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Top Products</h2>
                <?php if(empty($top_products)): ?>
                    <p class="text-sm text-gray-500">No product sales yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($top_products as $tp): ?>
                        <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-2 last:border-0">
                            <span class="text-gray-800 font-bold truncate max-w-[200px]"><?php echo e($tp['product_name']); ?></span>
                            <span class="text-gray-500"><?php echo $tp['units_sold']; ?> sold</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="text-lg font-bold text-gray-800">Recent Inventory</h2>
                    <a href="inventory/history.php" class="text-xs text-blue-600 hover:underline font-bold">View All</a>
                </div>
                <?php if(empty($recent_inventory)): ?>
                    <p class="text-sm text-gray-500">No recent transactions.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($recent_inventory as $inv): ?>
                        <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-2 last:border-0">
                            <div>
                                <p class="text-gray-800 font-bold truncate max-w-[150px]"><?php echo e($inv['product_name']); ?></p>
                                <p class="text-xs text-gray-500 capitalize"><?php echo e($inv['transaction_type']); ?></p>
                            </div>
                            <span class="font-bold <?php echo $inv['quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $inv['quantity'] > 0 ? '+' : ''; ?><?php echo $inv['quantity']; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800">Recent Orders</h2>
                <a href="orders/index.php" class="text-sm text-blue-600 font-bold hover:underline">View All</a>
            </div>
            <?php if(empty($recent_orders)): ?>
                <div class="p-6 text-center text-gray-500">No recent orders found.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-gray-500">
                                <th class="py-3 px-6 font-bold">Order #</th>
                                <th class="py-3 px-6 font-bold">Customer</th>
                                <th class="py-3 px-6 font-bold">Total</th>
                                <th class="py-3 px-6 font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_orders as $o): ?>
                            <tr class="border-t border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="window.location='orders/view.php?id=<?php echo $o['id']; ?>'">
                                <td class="py-3 px-6 font-bold text-blue-600"><?php echo e($o['order_number']); ?></td>
                                <td class="py-3 px-6"><?php echo e($o['customer_name']); ?></td>
                                <td class="py-3 px-6 font-bold"><?php echo format_price($o['grand_total']); ?></td>
                                <td class="py-3 px-6">
                                    <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-gray-200 text-gray-700"><?php echo e($o['order_status']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h2>

            <a href="products/create.php" class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-colors">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-600 border"><i class="fa-solid fa-plus"></i></div>
                <span class="font-bold text-sm text-gray-700">Add New Product</span>
            </a>

            <a href="categories/create.php" class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-colors">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-600 border"><i class="fa-solid fa-folder-plus"></i></div>
                <span class="font-bold text-sm text-gray-700">Add Category</span>
            </a>

            <a href="inventory/adjustments.php" class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-colors">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-600 border"><i class="fa-solid fa-boxes-stacked"></i></div>
                <span class="font-bold text-sm text-gray-700">Adjust Stock</span>
            </a>

            <a href="reports/index.php" class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-colors">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-600 border"><i class="fa-solid fa-chart-pie"></i></div>
                <span class="font-bold text-sm text-gray-700">View Sales Report</span>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>