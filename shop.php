<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$search = $_GET['search'] ?? '';
$cat_id = $_GET['category'] ?? '';
$brand_id = $_GET['brand'] ?? '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) && $_GET['min_price'] >= 0 ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] >= 0 ? $_GET['max_price'] : '';
$sort = $_GET['sort'] ?? 'recommended';
$page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$per_page = 12;

// Build query
$query = "SELECT p.*, c.name as cat_name, b.name as brand_name,
          (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN brands b ON p.brand_id = b.id
          WHERE p.status = 'active'";
$count_query = "SELECT COUNT(*) FROM products p WHERE p.status = 'active'";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR b.name LIKE ?)";
    $count_query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR (SELECT name FROM brands WHERE id = p.brand_id) LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cat_id) {
    // Basic category filter (slug or id)
    if (is_numeric($cat_id)) {
        $query .= " AND p.category_id = ?";
        $count_query .= " AND p.category_id = ?";
    } else {
        $query .= " AND c.slug = ?";
        $count_query .= " AND p.category_id IN (SELECT id FROM categories WHERE slug = ?)";
    }
    $params[] = $cat_id;
}

if ($brand_id) {
    if (is_numeric($brand_id)) {
        $query .= " AND p.brand_id = ?";
        $count_query .= " AND p.brand_id = ?";
    } else {
        $query .= " AND b.slug = ?";
        $count_query .= " AND p.brand_id IN (SELECT id FROM brands WHERE slug = ?)";
    }
    $params[] = $brand_id;
}

if ($min_price !== '') {
    $query .= " AND p.base_price >= ?";
    $count_query .= " AND p.base_price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '') {
    $query .= " AND p.base_price <= ?";
    $count_query .= " AND p.base_price <= ?";
    $params[] = $max_price;
}

// Sorting
switch ($sort) {
    case 'newest':
        $query .= " ORDER BY p.created_at DESC";
        break;
    case 'price_low':
        $query .= " ORDER BY p.base_price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.base_price DESC";
        break;
    case 'name_az':
        $query .= " ORDER BY p.name ASC";
        break;
    default:
        $query .= " ORDER BY p.sort_order ASC, p.id DESC";
}

// Pagination
$offset = ($page - 1) * $per_page;
$query .= " LIMIT $per_page OFFSET $offset";

// Execute
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$c_stmt = $pdo->prepare($count_query);
$c_stmt->execute($params);
$total_products = $c_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Filter data
$categories = $pdo->query("SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT id, name, slug FROM brands WHERE status = 'active' ORDER BY name")->fetchAll();

?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="bg-gray-50 py-8 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 flex flex-col md:flex-row gap-8">

        <!-- Sidebar Filters -->
        <aside class="w-full md:w-1/4 bg-white p-6 rounded-lg shadow-sm border border-gray-100 h-fit">
            <h2 class="text-xl font-bold text-pcwBlack mb-6">Filters</h2>
            <form method="get" action="shop.php">
                <?php if($search): ?>
                    <input type="hidden" name="search" value="<?php echo e($search); ?>">
                <?php endif; ?>

                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3 border-b pb-2">Category</h3>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <?php foreach($categories as $c): ?>
                            <label class="flex items-center text-sm text-gray-600">
                                <input type="radio" name="category" value="<?php echo e($c['slug']); ?>" <?php echo $cat_id == $c['slug'] ? 'checked' : ''; ?> class="mr-2 text-pcwRed">
                                <?php echo e($c['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3 border-b pb-2">Brand</h3>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <?php foreach($brands as $b): ?>
                            <label class="flex items-center text-sm text-gray-600">
                                <input type="radio" name="brand" value="<?php echo e($b['slug']); ?>" <?php echo $brand_id == $b['slug'] ? 'checked' : ''; ?> class="mr-2 text-pcwRed">
                                <?php echo e($b['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3 border-b pb-2">Price (<?php echo CURRENCY; ?>)</h3>
                    <div class="flex gap-2">
                        <input type="number" min="0" name="min_price" value="<?php echo e($min_price); ?>" placeholder="Min" class="w-full border border-gray-300 rounded p-1 text-sm">
                        <span class="text-gray-500">-</span>
                        <input type="number" min="0" name="max_price" value="<?php echo e($max_price); ?>" placeholder="Max" class="w-full border border-gray-300 rounded p-1 text-sm">
                    </div>
                </div>

                <button type="submit" class="w-full bg-pcwRed hover:bg-red-800 text-white font-bold py-2 rounded transition-colors text-sm">Apply Filters</button>
                <a href="shop.php" class="block text-center mt-3 text-xs text-gray-500 hover:text-pcwBlack">Clear All</a>
            </form>
        </aside>

        <!-- Product Grid -->
        <main class="w-full md:w-3/4">

            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <p class="text-sm text-gray-600 mb-4 sm:mb-0">Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products</p>
                <form method="get" action="shop.php" class="flex items-center gap-2">
                    <!-- Preserve existing filters -->
                    <?php if($search): ?><input type="hidden" name="search" value="<?php echo e($search); ?>"><?php endif; ?>
                    <?php if($cat_id): ?><input type="hidden" name="category" value="<?php echo e($cat_id); ?>"><?php endif; ?>
                    <?php if($brand_id): ?><input type="hidden" name="brand" value="<?php echo e($brand_id); ?>"><?php endif; ?>
                    <?php if($min_price !== ''): ?><input type="hidden" name="min_price" value="<?php echo e($min_price); ?>"><?php endif; ?>
                    <?php if($max_price !== ''): ?><input type="hidden" name="max_price" value="<?php echo e($max_price); ?>"><?php endif; ?>

                    <label class="text-sm text-gray-600">Sort by:</label>
                    <select name="sort" onchange="this.form.submit()" class="border border-gray-300 rounded p-1 text-sm">
                        <option value="recommended" <?php echo $sort == 'recommended' ? 'selected' : ''; ?>>Recommended</option>
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_az" <?php echo $sort == 'name_az' ? 'selected' : ''; ?>>Name: A-Z</option>
                    </select>
                </form>
            </div>

            <?php if(empty($products)): ?>
                <div class="bg-white p-12 text-center rounded-lg shadow-sm border border-gray-100">
                    <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-700">No products found</h3>
                    <p class="text-gray-500 mt-2">Try adjusting your filters or search query.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                    <?php foreach($products as $p): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition-shadow relative">
                        <?php if($p['new_arrival']): ?>
                            <div class="absolute top-2 left-2 sm:top-3 sm:left-3 bg-pcwRed text-white text-[9px] sm:text-xs font-bold px-1.5 py-0.5 sm:px-2 sm:py-1 z-10 rounded">New</div>
                        <?php endif; ?>

                        <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="block product-image-frame">
                            <img src="<?php echo e($p['primary_image'] ?? 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80'); ?>" alt="<?php echo e($p['name']); ?>" class="hover-scale">
                        </a>

                        <div class="p-2 sm:p-4 text-center">
                            <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="block font-semibold text-xs sm:text-base text-gray-900 mb-0.5 sm:mb-1 truncate hover:text-pcwRed">
                                <?php echo e($p['name']); ?>
                            </a>
                            <p class="text-gray-500 text-[10px] sm:text-xs mb-1 sm:mb-2 truncate"><?php echo e($p['brand_name'] ?? $p['cat_name']); ?></p>
                            <p class="font-bold text-sm sm:text-lg text-pcwBlack mb-2 sm:mb-3"><?php echo format_price($p['base_price']); ?></p>
                            <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="w-full bg-white border border-pcwBlack text-pcwBlack hover:bg-pcwBlack hover:text-white text-[11px] sm:text-sm font-bold py-1.5 sm:py-2 rounded transition-colors flex items-center justify-center gap-1">
                                View Details
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="mt-10 flex justify-center gap-2">
                <?php
                // Rebuild query string for pagination links
                $qs = $_GET;
                unset($qs['page']);
                $qstr = http_build_query($qs);
                $qstr = $qstr ? '&' . $qstr : '';

                for($i=1; $i<=$total_pages; $i++):
                    $active = $i == $page ? 'bg-pcwRed text-white border-pcwRed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
                ?>
                    <a href="shop.php?page=<?php echo $i; ?><?php echo $qstr; ?>" class="px-4 py-2 border rounded font-semibold text-sm transition-colors <?php echo $active; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>