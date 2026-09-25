<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_admin($pdo);

$period = $_GET['period'] ?? 'this_month';
$start_date = '';
$end_date = '';

// Handle preset dates
if ($period === 'today') {
    $start_date = date('Y-m-d 00:00:00');
    $end_date = date('Y-m-d 23:59:59');
} elseif ($period === 'yesterday') {
    $start_date = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $end_date = date('Y-m-d 23:59:59', strtotime('-1 day'));
} elseif ($period === 'last_7_days') {
    $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
    $end_date = date('Y-m-d 23:59:59');
} elseif ($period === 'last_30_days') {
    $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
    $end_date = date('Y-m-d 23:59:59');
} elseif ($period === 'this_month') {
    $start_date = date('Y-m-01 00:00:00');
    $end_date = date('Y-m-t 23:59:59');
} elseif ($period === 'custom') {
    $start_date = !empty($_GET['start_date']) ? date('Y-m-d 00:00:00', strtotime($_GET['start_date'])) : date('Y-m-01 00:00:00');
    $end_date = !empty($_GET['end_date']) ? date('Y-m-d 23:59:59', strtotime($_GET['end_date'])) : date('Y-m-t 23:59:59');
}

// 1. Sales Overview
// Only counting non-cancelled, non-refunded orders as realized revenue
$sales_stmt = $pdo->prepare("SELECT
    COUNT(id) as total_orders,
    COALESCE(SUM(grand_total), 0) as gross_revenue
    FROM orders
    WHERE created_at BETWEEN ? AND ?
    AND order_status NOT IN ('cancelled', 'refunded', 'returned')");
$sales_stmt->execute([$start_date, $end_date]);
$sales = $sales_stmt->fetch();

// 2. Order Status Breakdown
$status_stmt = $pdo->prepare("SELECT order_status, COUNT(id) as count
                              FROM orders
                              WHERE created_at BETWEEN ? AND ?
                              GROUP BY order_status");
$status_stmt->execute([$start_date, $end_date]);
$statuses = $status_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 3. Top Products
$top_prod = $pdo->prepare("SELECT i.product_name, SUM(i.quantity) as units_sold, SUM(i.line_total) as revenue
                           FROM order_items i
                           JOIN orders o ON i.order_id = o.id
                           WHERE o.created_at BETWEEN ? AND ?
                           AND o.order_status NOT IN ('cancelled', 'refunded', 'returned')
                           GROUP BY i.product_id, i.product_name
                           ORDER BY units_sold DESC LIMIT 10");
$top_prod->execute([$start_date, $end_date]);
$top_products = $top_prod->fetchAll();

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="w-full max-w-7xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Business Reports</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm mb-6 border border-gray-100">
        <form method="get" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Period</label>
                <select name="period" class="border border-gray-300 rounded p-2" onchange="if(this.value=='custom') document.getElementById('custom-dates').style.display='flex'; else document.getElementById('custom-dates').style.display='none';">
                    <option value="today" <?php echo $period=='today'?'selected':''; ?>>Today</option>
                    <option value="yesterday" <?php echo $period=='yesterday'?'selected':''; ?>>Yesterday</option>
                    <option value="last_7_days" <?php echo $period=='last_7_days'?'selected':''; ?>>Last 7 Days</option>
                    <option value="last_30_days" <?php echo $period=='last_30_days'?'selected':''; ?>>Last 30 Days</option>
                    <option value="this_month" <?php echo $period=='this_month'?'selected':''; ?>>This Month</option>
                    <option value="custom" <?php echo $period=='custom'?'selected':''; ?>>Custom Range</option>
                </select>
            </div>

            <div id="custom-dates" style="display: <?php echo $period=='custom'?'flex':'none'; ?>;" class="gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? e($_GET['start_date']) : ''; ?>" class="border border-gray-300 rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? e($_GET['end_date']) : ''; ?>" class="border border-gray-300 rounded p-2">
                </div>
            </div>

            <button type="submit" class="bg-pcwRed text-white px-6 py-2 rounded font-bold hover:bg-red-800 transition-colors">Generate</button>
        </form>
    </div>

    <div class="mb-6">
        <h2 class="text-gray-500 text-sm font-bold">Report Period: <span class="text-gray-800"><?php echo date('d M Y', strtotime($start_date)); ?></span> to <span class="text-gray-800"><?php echo date('d M Y', strtotime($end_date)); ?></span></h2>
    </div>

    <!-- Sales Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Valid Orders</p>
            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $sales['total_orders']; ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-pcwGold">
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Gross Revenue</p>
            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo format_price($sales['gross_revenue']); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Avg Order Value</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">
                <?php echo $sales['total_orders'] > 0 ? format_price($sales['gross_revenue'] / $sales['total_orders']) : format_price(0); ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Status Breakdown -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">Order Status Breakdown</h2>
            <?php if(empty($statuses)): ?>
                <p class="text-gray-500 text-sm">No orders found in this period.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php
                    $colors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'confirmed' => 'bg-blue-100 text-blue-800',
                        'processing' => 'bg-blue-100 text-blue-800',
                        'packed' => 'bg-indigo-100 text-indigo-800',
                        'shipped' => 'bg-purple-100 text-purple-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        'returned' => 'bg-red-100 text-red-800',
                        'refunded' => 'bg-gray-200 text-gray-800'
                    ];
                    foreach($statuses as $stat => $count):
                        $c = $colors[$stat] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                        <span class="px-2 py-1 text-xs font-bold uppercase rounded <?php echo $c; ?>"><?php echo e($stat); ?></span>
                        <span class="font-bold text-gray-800"><?php echo $count; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Top Products -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">Top Selling Products</h2>
            <?php if(empty($top_products)): ?>
                <p class="text-gray-500 text-sm">No sales found in this period.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="pb-2">Product</th>
                                <th class="pb-2 text-center">Units</th>
                                <th class="pb-2 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_products as $tp): ?>
                            <tr class="border-b border-gray-50">
                                <td class="py-2 font-bold text-gray-800"><?php echo e($tp['product_name']); ?></td>
                                <td class="py-2 text-center"><?php echo $tp['units_sold']; ?></td>
                                <td class="py-2 text-right text-pcwRed font-bold"><?php echo format_price($tp['revenue']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>