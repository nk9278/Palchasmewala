<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    redirect('shop.php');
}

$stmt = $pdo->prepare("SELECT p.*, c.name as cat_name, c.slug as cat_slug, b.name as brand_name
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN brands b ON p.brand_id = b.id
                      WHERE p.slug = ? AND p.status = 'active'");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found or inactive.");
}

$p_id = $product['id'];

// Images
$img_stmt = $pdo->prepare("SELECT image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$img_stmt->execute([$p_id]);
$images = $img_stmt->fetchAll();
$primary_image = !empty($images) ? $images[0]['image_path'] : 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&q=80';

// Variants
$var_stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? AND status = 'active' ORDER BY variant_name ASC");
$var_stmt->execute([$p_id]);
$variants = $var_stmt->fetchAll();

// Construct JSON for variants to handle dynamic price/stock on frontend
$variant_data = [];
foreach ($variants as $v) {
    $variant_data[$v['id']] = [
        'price' => format_price($v['price']),
        'stock' => (int)$v['stock_quantity'],
        'sku' => $v['sku']
    ];
}
$variant_json = json_encode($variant_data);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="bg-gray-50 py-8 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12">
        <!-- Breadcrumb -->
        <nav class="text-xs sm:text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-pcwRed">Home</a> &gt;
            <a href="shop.php?category=<?php echo urlencode($product['cat_slug']); ?>" class="hover:text-pcwRed"><?php echo e($product['cat_name']); ?></a> &gt;
            <span class="text-gray-800"><?php echo e($product['name']); ?></span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex flex-col md:flex-row">

                <!-- Image Gallery -->
                <div class="w-full md:w-1/2 p-6 border-b md:border-b-0 md:border-r border-gray-100">
                    <div class="product-image-frame !h-[400px] sm:!h-[500px] mb-4">
                        <img id="main-image" src="<?php echo e($primary_image); ?>" alt="<?php echo e($product['name']); ?>" class="w-full h-full object-contain">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <?php foreach($images as $img): ?>
                        <div class="w-20 h-20 flex-shrink-0 border-2 border-transparent hover:border-pcwRed cursor-pointer rounded-lg overflow-hidden" onclick="document.getElementById('main-image').src='<?php echo e($img['image_path']); ?>'">
                            <img src="<?php echo e($img['image_path']); ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="w-full md:w-1/2 p-6 sm:p-10 flex flex-col justify-center">
                    <?php if($product['brand_name']): ?>
                        <p class="text-sm font-bold text-gray-500 tracking-widest uppercase mb-2"><?php echo e($product['brand_name']); ?></p>
                    <?php endif; ?>

                    <h1 class="text-2xl sm:text-4xl font-bold text-pcwBlack mb-2"><?php echo e($product['name']); ?></h1>

                    <p class="text-gray-500 text-sm mb-4" id="display-sku">SKU: <?php echo e($product['sku']); ?></p>

                    <div class="text-3xl font-bold text-pcwRed mb-6" id="display-price">
                        <?php echo format_price($product['base_price']); ?>
                    </div>

                    <?php if($product['short_description']): ?>
                        <p class="text-gray-600 mb-6 leading-relaxed"><?php echo e($product['short_description']); ?></p>
                    <?php endif; ?>

                    <form action="cart.php" method="POST" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $p_id; ?>">

                        <?php if (!empty($variants)): ?>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Options</label>
                            <select name="variant_id" id="variant-select" class="w-full border-2 border-gray-200 rounded-lg p-3 outline-none focus:border-pcwRed transition-colors" required>
                                <option value="">Select an option...</option>
                                <?php foreach($variants as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['variant_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div id="stock-status" class="text-sm font-bold text-green-600 hidden">
                            <i class="fa-solid fa-check-circle"></i> In Stock
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex items-center border-2 border-gray-200 rounded-lg w-32 h-12">
                                <button type="button" onclick="updateQty(-1)" class="w-10 h-full flex items-center justify-center text-gray-500 hover:text-pcwBlack transition-colors focus:outline-none">-</button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" class="w-full h-full text-center outline-none font-bold text-gray-800" readonly>
                                <button type="button" onclick="updateQty(1)" class="w-10 h-full flex items-center justify-center text-gray-500 hover:text-pcwBlack transition-colors focus:outline-none">+</button>
                            </div>

                            <button type="submit" id="add-to-cart-btn" class="flex-grow bg-pcwRed hover:bg-red-800 text-white font-bold h-12 rounded-lg transition-colors shadow-md flex items-center justify-center gap-2" <?php echo empty($variants) ? '' : 'disabled'; ?>>
                                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 border-t border-gray-100 pt-6 text-sm text-gray-600 space-y-3">
                        <p class="flex items-center gap-2"><i class="fa-solid fa-shield-halved text-pcwGold w-5"></i> 1 Year Warranty</p>
                        <p class="flex items-center gap-2"><i class="fa-solid fa-truck-fast text-pcwGold w-5"></i> Free Shipping Available</p>
                        <p class="flex items-center gap-2"><i class="fa-solid fa-rotate-left text-pcwGold w-5"></i> 7 Days Return Policy</p>
                    </div>

                </div>
            </div>
        </div>

        <?php if($product['description']): ?>
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-10">
            <h2 class="text-2xl font-bold text-pcwBlack mb-6">Product Information</h2>
            <div class="prose max-w-none text-gray-600 leading-relaxed">
                <?php echo nl2br(e($product['description'])); ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
    const variantData = <?php echo $variant_json; ?>;
    const variantSelect = document.getElementById('variant-select');
    const priceDisplay = document.getElementById('display-price');
    const skuDisplay = document.getElementById('display-sku');
    const stockStatus = document.getElementById('stock-status');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const qtyInput = document.getElementById('qty');

    let currentMaxStock = 0;

    if (variantSelect) {
        // Disable add to cart initially until variant is selected
        addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');

        variantSelect.addEventListener('change', function() {
            const vId = this.value;
            qtyInput.value = 1;

            if (vId && variantData[vId]) {
                const v = variantData[vId];
                priceDisplay.innerHTML = v.price;
                skuDisplay.innerHTML = 'SKU: ' + v.sku;
                currentMaxStock = v.stock;

                stockStatus.classList.remove('hidden');
                if (v.stock > 0) {
                    stockStatus.innerHTML = '<i class="fa-solid fa-check-circle"></i> In Stock (' + v.stock + ' available)';
                    stockStatus.className = 'text-sm font-bold text-green-600 mb-4 block';
                    addToCartBtn.disabled = false;
                    addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    stockStatus.innerHTML = '<i class="fa-solid fa-xmark-circle"></i> Out of Stock';
                    stockStatus.className = 'text-sm font-bold text-red-600 mb-4 block';
                    addToCartBtn.disabled = true;
                    addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                priceDisplay.innerHTML = '<?php echo format_price($product['base_price']); ?>';
                skuDisplay.innerHTML = 'SKU: <?php echo e($product['sku']); ?>';
                stockStatus.classList.add('hidden');
                addToCartBtn.disabled = true;
                addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
                currentMaxStock = 0;
            }
        });
    } else {
        // Product without variants (assumed always in stock for Phase 3 simple case, or mapped differently later)
        // Usually eyewear has variants. We'll leave the button active for non-variant products.
    }

    function updateQty(change) {
        if (!variantSelect || variantSelect.value) {
            let newVal = parseInt(qtyInput.value) + change;
            if (newVal < 1) newVal = 1;

            // Client side stock check if variant is selected
            if (variantSelect && variantSelect.value) {
                if (newVal > currentMaxStock) {
                    newVal = currentMaxStock;
                    alert('Maximum available stock reached.');
                }
            }

            qtyInput.value = newVal;
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>