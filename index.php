<?php include __DIR__ . "/includes/header.php"; ?>


<?php
// includes/db.php is already included via header.php which includes config and functions.
// Actually header.php only includes config and functions. We should include db.php in header or index.

$bestsellers = [];
$premium = [];
$active_brands = [];

if (isset($pdo)) {
    try {
        $bestsellers = $pdo->query("SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image FROM products p WHERE p.bestseller = 1 AND p.status = 'active' ORDER BY p.sort_order ASC LIMIT 6")->fetchAll();
        $premium = $pdo->query("SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image FROM products p JOIN categories c ON p.category_id = c.id WHERE c.slug = 'sunglasses' AND p.status = 'active' ORDER BY p.sort_order ASC LIMIT 10")->fetchAll();
        $active_brands = $pdo->query("SELECT name FROM brands WHERE status = 'active' ORDER BY sort_order ASC")->fetchAll();
    } catch (Exception $e) {}
}
?>



    <!-- Hero Banner Slider Section -->
    <section class="relative w-full h-[60vh] min-h-[400px] sm:min-h-[500px] bg-gray-900 overflow-hidden" id="hero-slider">

        <!-- Slide 1 -->
        <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 opacity-100">
            <img src="https://images.unsplash.com/photo-1577803645773-f96470509666?auto=format&fit=crop&q=80&w=2000" alt="Model wearing glasses" class="w-full h-full object-cover opacity-60">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center px-4 max-w-4xl mx-auto">
                    <h2 class="text-pcwGold font-serif text-lg md:text-2xl italic mb-2 sm:mb-3 tracking-widest">Est. 1991</h2>
                    <h1 class="text-3xl sm:text-4xl md:text-6xl font-bold text-white mb-4 sm:mb-6 leading-tight">Crafting Luxury Vision<br>Since 1991*</h1>
                    <p class="text-gray-200 text-sm sm:text-lg md:text-xl mb-6 sm:mb-8 max-w-2xl mx-auto font-light">Experience the perfect blend of style, comfort, and precision.</p>
                    <a href="/shop.php" class="inline-block bg-pcwRed hover:bg-red-800 text-white font-semibold py-2.5 sm:py-3 px-8 sm:px-10 rounded-full transition-colors text-sm sm:text-lg shadow-lg">Shop Collection</a>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 opacity-0">
            <img src="https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=2000" alt="Stylish Sunglasses" class="w-full h-full object-cover opacity-60">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center px-4 max-w-4xl mx-auto">
                    <h2 class="text-pcwGold font-serif text-lg md:text-2xl italic mb-2 sm:mb-3 tracking-widest">Premium Collection</h2>
                    <h1 class="text-3xl sm:text-4xl md:text-6xl font-bold text-white mb-4 sm:mb-6 leading-tight">Discover Your Perfect<br>Summer Look</h1>
                    <p class="text-gray-200 text-sm sm:text-lg md:text-xl mb-6 sm:mb-8 max-w-2xl mx-auto font-light">Explore our wide range of UV protected designer sunglasses.</p>
                    <a href="/shop.php?category=sunglasses" class="inline-block bg-pcwRed hover:bg-red-800 text-white font-semibold py-2.5 sm:py-3 px-8 sm:px-10 rounded-full transition-colors text-sm sm:text-lg shadow-lg">Explore Sunglasses</a>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 opacity-0">
            <img src="https://images.unsplash.com/photo-1509695507497-903c140c43b0?auto=format&fit=crop&q=80&w=2000" alt="Tested Lenses" class="w-full h-full object-cover opacity-60">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center px-4 max-w-4xl mx-auto">
                    <h2 class="text-pcwGold font-serif text-lg md:text-2xl italic mb-2 sm:mb-3 tracking-widest">Precision & Care</h2>
                    <h1 class="text-3xl sm:text-4xl md:text-6xl font-bold text-white mb-4 sm:mb-6 leading-tight">Advanced Tested Lenses<br>For Clear Vision</h1>
                    <p class="text-gray-200 text-sm sm:text-lg md:text-xl mb-6 sm:mb-8 max-w-2xl mx-auto font-light">Crystal clear lenses crafted for your eye's ultimate comfort.</p>
                    <a href="/shop.php?category=tested-lenses" class="inline-block bg-pcwRed hover:bg-red-800 text-white font-semibold py-2.5 sm:py-3 px-8 sm:px-10 rounded-full transition-colors text-sm sm:text-lg shadow-lg">View Lenses</a>
                </div>
            </div>
        </div>

        <!-- Slider Controls -->
        <button onclick="prevSlide()" class="absolute left-2 sm:left-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-2 md:p-4 rounded-full hover:bg-pcwRed z-20 transition-colors">
            <i class="fa-solid fa-chevron-left text-sm sm:text-base"></i>
        </button>
        <button onclick="nextSlide()" class="absolute right-2 sm:right-4 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-2 md:p-4 rounded-full hover:bg-pcwRed z-20 transition-colors">
            <i class="fa-solid fa-chevron-right text-sm sm:text-base"></i>
        </button>
    </section>

    <!-- Scrolling Marquee Strip -->
    <div class="bg-pcwRed text-white py-2 sm:py-3 overflow-hidden flex whitespace-nowrap shadow-md w-full">
        <div class="animate-marquee inline-block font-semibold tracking-widest uppercase text-xs md:text-base">
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-star text-pcwGold mr-1 sm:mr-2"></i> FLAT 20% OFF ON PREMIUM SUNGLASSES</span>
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-eye text-pcwGold mr-1 sm:mr-2"></i> FREE EYE TESTING AT OUR SULTANPUR BRANCH</span>
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-glasses text-pcwGold mr-1 sm:mr-2"></i> NEW ARRIVALS: TITANIUM RIMLESS FRAMES</span>
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-crown text-pcwGold mr-1 sm:mr-2"></i> CRAFTING LUXURY VISION SINCE 1991</span>
            <!-- Duplicated for seamless loop -->
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-star text-pcwGold mr-1 sm:mr-2"></i> FLAT 20% OFF ON PREMIUM SUNGLASSES</span>
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-eye text-pcwGold mr-1 sm:mr-2"></i> FREE EYE TESTING AT OUR SULTANPUR BRANCH</span>
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-glasses text-pcwGold mr-1 sm:mr-2"></i> NEW ARRIVALS: TITANIUM RIMLESS FRAMES</span>
            <span class="mx-4 sm:mx-6"><i class="fa-solid fa-crown text-pcwGold mr-1 sm:mr-2"></i> CRAFTING LUXURY VISION SINCE 1991</span>
        </div>
    </div>

    <!-- Best Sellers Section (Full Width, 6 Columns) -->
    <section class="py-12 sm:py-20 w-full px-3 sm:px-8 lg:px-12">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-xl sm:text-3xl font-bold text-pcwBlack mb-1 sm:mb-2 uppercase tracking-wide">Best Sellers</h2>
            <div class="w-12 sm:w-16 h-1 bg-pcwRed mx-auto mb-3 sm:mb-4"></div>
            <p class="text-gray-500 text-xs sm:text-base">Our most loved eyewear styles of the season.</p>
        </div>

        <!-- grid-cols-2 for mobile, then md:3, lg:6 -->

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-6">
            <?php foreach($bestsellers as $p): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition-shadow relative">
                <?php if($p['new_arrival']): ?>
                    <div class="absolute top-2 left-2 sm:top-3 sm:left-3 bg-pcwRed text-white text-[9px] sm:text-xs font-bold px-1.5 py-0.5 sm:px-2 sm:py-1 z-10 rounded">New</div>
                <?php else: ?>
                    <div class="absolute top-2 left-2 sm:top-3 sm:left-3 bg-pcwBlack text-white text-[9px] sm:text-xs font-bold px-1.5 py-0.5 sm:px-2 sm:py-1 z-10 rounded">Bestseller</div>
                <?php endif; ?>
                <div class="product-image-frame">
                    <img src="<?php echo e($p['primary_image'] ?? 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80'); ?>" alt="<?php echo e($p['name']); ?>" class="hover-scale">
                </div>
                <div class="p-2 sm:p-4 text-center">
                    <h3 class="font-semibold text-xs sm:text-base text-gray-900 mb-0.5 sm:mb-1 truncate"><?php echo e($p['name']); ?></h3>
                    <p class="text-gray-500 text-[10px] sm:text-xs mb-1 sm:mb-2 truncate"><?php echo e($p['sku']); ?></p>
                    <p class="font-bold text-sm sm:text-lg text-pcwBlack mb-2 sm:mb-3"><?php echo format_price($p['base_price']); ?></p>
                    <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="w-full bg-white border border-pcwRed text-pcwRed hover:bg-pcwRed hover:text-white text-[11px] sm:text-sm font-bold py-1.5 sm:py-2 rounded transition-colors flex items-center justify-center gap-1">
                        View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8 sm:mt-12">

            <a href="/shop.php" class="inline-block border border-pcwRed sm:border-2 text-pcwRed hover:bg-pcwRed hover:text-white text-sm sm:text-base font-bold py-2 sm:py-3 px-6 sm:px-8 rounded transition-colors">
                View All Products
            </a>
        </div>
    </section>

    <!-- Premium Sunglasses Section (Full Width Continuous Slider) -->
    <section class="py-10 sm:py-16 bg-gray-100">
        <div class="w-full px-3 sm:px-8 lg:px-12">
            <div class="flex justify-between items-end mb-6 sm:mb-10 w-full">
                <div>
                    <h2 class="text-xl sm:text-3xl font-bold text-pcwBlack uppercase tracking-wide">Premium Sunglasses</h2>
                    <div class="w-12 sm:w-16 h-1 bg-pcwRed mt-1 sm:mt-2"></div>
                </div>
                <a href="/shop.php?category=sunglasses" class="text-pcwRed text-sm sm:text-base font-semibold hover:underline hidden sm:block">View All <i class="fa-solid fa-arrow-right text-xs sm:text-sm"></i></a>
            </div>

            <!-- Continuous Slider Container -->
            <div class="slider-container">
                <div class="slider-track">
                    <?php foreach($premium as $p): ?>
                    <div class="bg-white p-2 sm:p-4 rounded-lg shadow-sm border border-gray-200 group product-card">
                        <div class="premium-product-image-frame">
                            <img src="<?php echo e($p['primary_image'] ?? 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80'); ?>" alt="<?php echo e($p['name']); ?>" class="hover-scale">
                        </div>
                        <h3 class="font-bold text-xs sm:text-lg text-pcwBlack truncate mt-1"><?php echo e($p['name']); ?></h3>
                        <p class="text-pcwRed font-bold text-sm sm:text-xl mt-0.5 sm:mt-1"><?php echo format_price($p['base_price']); ?></p>
                        <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="w-full mt-2 sm:mt-3 bg-white border border-pcwRed text-pcwRed hover:bg-pcwRed hover:text-white text-[11px] sm:text-sm font-bold py-1.5 sm:py-2 rounded transition-colors flex items-center justify-center gap-1">
                            View Details
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <!-- Duplicate for infinite scroll -->
                    <?php foreach($premium as $p): ?>
                    <div class="bg-white p-2 sm:p-4 rounded-lg shadow-sm border border-gray-200 group product-card">
                        <div class="premium-product-image-frame">
                            <img src="<?php echo e($p['primary_image'] ?? 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80'); ?>" alt="<?php echo e($p['name']); ?>" class="hover-scale">
                        </div>
                        <h3 class="font-bold text-xs sm:text-lg text-pcwBlack truncate mt-1"><?php echo e($p['name']); ?></h3>
                        <p class="text-pcwRed font-bold text-sm sm:text-xl mt-0.5 sm:mt-1"><?php echo format_price($p['base_price']); ?></p>
                        <a href="product.php?slug=<?php echo e($p['slug']); ?>" class="w-full mt-2 sm:mt-3 bg-white border border-pcwRed text-pcwRed hover:bg-pcwRed hover:text-white text-[11px] sm:text-sm font-bold py-1.5 sm:py-2 rounded transition-colors flex items-center justify-center gap-1">
                            View Details
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-6 text-center sm:hidden">
                <a href="/shop.php?category=sunglasses" class="text-pcwRed text-sm font-semibold hover:underline">View All <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>
        </div>
    </section>

    <!-- Tested Lenses Section (Grid-cols-2 for mobile) -->
    <section id="tested-lenses" class="py-12 sm:py-16 w-full px-3 sm:px-8 lg:px-12">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-xl sm:text-3xl font-bold text-pcwBlack mb-1 sm:mb-2 uppercase tracking-wide">Tested Lenses</h2>
            <div class="w-12 sm:w-16 h-1 bg-pcwRed mx-auto mb-3 sm:mb-4"></div>
            <p class="text-gray-500 text-xs sm:text-base max-w-2xl mx-auto px-2">Advanced optical technology for crystal clear vision. Customized to your exact prescription.</p>
        </div>

        <!-- Changed to grid-cols-2 for mobile -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            <!-- Lens 1 -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                <img src="https://images.unsplash.com/photo-1582142407894-ec85a1260a46?auto=format&fit=crop&q=80&w=900" alt="Blue Control Lenses" class="w-full h-24 sm:h-48 object-cover">
                <div class="p-2 sm:p-5 flex flex-col flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1.5 sm:mb-3">
                        <i class="fa-solid fa-laptop text-pcwRed text-sm sm:text-lg"></i>
                        <h3 class="text-xs sm:text-lg font-bold text-pcwBlack">Blue Light Protection</h3>
                    </div>
                    <p class="text-gray-600 text-[10px] sm:text-sm mb-2 sm:mb-4 leading-snug sm:leading-relaxed flex-grow">Protect your eyes from digital screens with our advanced blue block technology.</p>
                    <button class="text-pcwRed text-[10px] sm:text-base font-bold hover:text-pcwBlack transition-colors tracking-wide text-left mt-auto">Learn More &rarr;</button>
                </div>
            </div>
            <!-- Lens 2 -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                <img src="https://images.unsplash.com/photo-1577803645773-f96470509666?auto=format&fit=crop&q=80&w=900" alt="Progressive Lenses" class="w-full h-24 sm:h-48 object-cover">
                <div class="p-2 sm:p-5 flex flex-col flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1.5 sm:mb-3">
                        <i class="fa-solid fa-arrows-to-eye text-pcwRed text-sm sm:text-lg"></i>
                        <h3 class="text-xs sm:text-lg font-bold text-pcwBlack">Premium Progressives</h3>
                    </div>
                    <p class="text-gray-600 text-[10px] sm:text-sm mb-2 sm:mb-4 leading-snug sm:leading-relaxed flex-grow">Seamless transition between near, intermediate, and distance vision without any visible lines.</p>
                    <button class="text-pcwRed text-[10px] sm:text-base font-bold hover:text-pcwBlack transition-colors tracking-wide text-left mt-auto">Learn More &rarr;</button>
                </div>
            </div>
            <!-- Lens 3 -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                <img src="https://images.unsplash.com/photo-1582142407894-ec85a1260a46?auto=format&fit=crop&w=600&q=80" alt="Anti-Glare Lenses" class="w-full h-24 sm:h-48 object-cover">
                <div class="p-2 sm:p-5 flex flex-col flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1.5 sm:mb-3">
                        <i class="fa-regular fa-sun text-pcwRed text-sm sm:text-lg"></i>
                        <h3 class="text-xs sm:text-lg font-bold text-pcwBlack">Anti-Glare Coating</h3>
                    </div>
                    <p class="text-gray-600 text-[10px] sm:text-sm mb-2 sm:mb-4 leading-snug sm:leading-relaxed flex-grow">Reduce reflections and improve night driving with our tested anti-reflective coating.</p>
                    <button class="text-pcwRed text-[10px] sm:text-base font-bold hover:text-pcwBlack transition-colors tracking-wide text-left mt-auto">Learn More &rarr;</button>
                </div>
            </div>
            <!-- Lens 4 (Added for full width balance) -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                <img src="https://images.unsplash.com/photo-1509695507497-903c140c43b0?auto=format&fit=crop&q=80&w=900" alt="Photochromic Lenses" class="w-full h-24 sm:h-48 object-cover">
                <div class="p-2 sm:p-5 flex flex-col flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1.5 sm:mb-3">
                        <i class="fa-solid fa-cloud-sun text-pcwRed text-sm sm:text-lg"></i>
                        <h3 class="text-xs sm:text-lg font-bold text-pcwBlack">Photochromic Lenses</h3>
                    </div>
                    <p class="text-gray-600 text-[10px] sm:text-sm mb-2 sm:mb-4 leading-snug sm:leading-relaxed flex-grow">Lenses that automatically darken in sunlight and turn clear indoors for all-day comfort.</p>
                    <button class="text-pcwRed text-[10px] sm:text-base font-bold hover:text-pcwBlack transition-colors tracking-wide text-left mt-auto">Learn More &rarr;</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop By Category (Full Width) -->
    <section id="shop-categories" class="bg-white py-12 sm:py-16 w-full px-4 sm:px-8 lg:px-12">
        <h2 class="text-xl sm:text-3xl font-bold text-center text-pcwBlack mb-6 sm:mb-10 uppercase tracking-wide">Shop By Category</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            <!-- Men Category (Updated Image) -->
            <div class="relative h-48 sm:h-96 group overflow-hidden rounded-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1580482228658-def52f18f4b1?auto=format&fit=crop&q=85&w=1400" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1577803645773-f96470509666?auto=format&fit=crop&q=80&w=1400';" alt="Men Eyewear" class="category-image w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-4 left-4 sm:bottom-8 sm:left-8">
                    <h3 class="text-xl sm:text-3xl font-bold text-white mb-1 sm:mb-2">Men</h3>
                    <span class="text-pcwGold text-xs sm:text-base font-semibold group-hover:text-white transition-colors">Explore <i class="fa-solid fa-arrow-right ml-1"></i></span>
                </div>
            </div>

            <!-- Women Category (Updated Image) -->
            <div class="relative h-48 sm:h-96 group overflow-hidden rounded-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1509695507497-903c140c43b0?auto=format&fit=crop&w=800&q=80" alt="Women Eyewear" class="category-image w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-4 left-4 sm:bottom-8 sm:left-8">
                    <h3 class="text-xl sm:text-3xl font-bold text-white mb-1 sm:mb-2">Women</h3>
                    <span class="text-pcwGold text-xs sm:text-base font-semibold group-hover:text-white transition-colors">Explore <i class="fa-solid fa-arrow-right ml-1"></i></span>
                </div>
            </div>

            <!-- Kids Category (Updated Image) -->
            <div class="relative h-48 sm:h-96 group overflow-hidden rounded-lg cursor-pointer">
                <img src="https://images.unsplash.com/photo-1685950925275-281298061f98?auto=format&fit=crop&q=85&w=1400" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1577803645773-f96470509666?auto=format&fit=crop&q=80&w=1400';" alt="Kids Eyewear" class="category-image w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-4 left-4 sm:bottom-8 sm:left-8">
                    <h3 class="text-xl sm:text-3xl font-bold text-white mb-1 sm:mb-2">Kids</h3>
                    <span class="text-pcwGold text-xs sm:text-base font-semibold group-hover:text-white transition-colors">Explore <i class="fa-solid fa-arrow-right ml-1"></i></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Large Eyewear Lifestyle Banner -->
    <section class="w-full py-10 sm:py-16 bg-gray-50">
        <div class="w-full px-4 sm:px-8 lg:px-12">
            <div class="relative min-h-[300px] sm:min-h-[420px] md:min-h-[520px] rounded-2xl overflow-hidden shadow-2xl bg-pcwBlack">
                <img loading="eager"
                    src="https://images.unsplash.com/photo-1525748822304-6807cb1348ab?auto=format&fit=crop&q=85&w=1800"
                    onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1580482228658-def52f18f4b1?auto=format&fit=crop&q=85&w=1400';"
                    alt="Person wearing stylish eyeglasses"
                    class="absolute inset-0 w-full h-full object-cover object-center"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/65 sm:via-black/55 to-black/30 sm:to-black/15"></div>

                <div class="relative z-10 min-h-[300px] sm:min-h-[420px] md:min-h-[520px] flex items-center">
                    <div class="max-w-2xl px-5 py-8 sm:px-10 md:px-16">
                        <p class="text-pcwGold font-serif italic text-sm sm:text-lg md:text-xl tracking-[0.2em] uppercase mb-2 sm:mb-4">
                            See The Difference
                        </p>
                        <h2 class="text-2xl sm:text-4xl md:text-6xl font-bold text-white leading-tight mb-3 sm:mb-5">
                            Style That Frames<br>
                            <span class="text-pcwGold">Your Vision</span>
                        </h2>
                        <p class="text-gray-200 text-xs sm:text-base md:text-lg leading-relaxed max-w-xl mb-5 sm:mb-8">
                            Premium frames, carefully selected styles and tested lenses designed
                            to bring together clear vision, comfort and confidence.
                        </p>
                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <a href="#tested-lenses"
                               class="inline-flex items-center gap-1 sm:gap-2 bg-pcwRed hover:bg-red-800 text-white font-bold px-4 py-2 sm:px-7 sm:py-3 rounded-full transition-colors shadow-lg text-xs sm:text-base">
                                Explore Tested Lenses
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="/shop.php" class="inline-flex items-center gap-2 border border-white/70 hover:bg-white hover:text-pcwBlack text-white font-semibold px-4 py-2 sm:px-7 sm:py-3 rounded-full transition-colors text-xs sm:text-base">
                                Shop Eyewear
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand Showcase Banner (Full Width) -->
    <section class="bg-pcwBlack py-10 sm:py-16 mt-6 sm:mt-8 w-full">
        <div class="w-full px-4 sm:px-8 lg:px-12 text-center">
            <h2 class="text-pcwGold text-lg sm:text-2xl md:text-4xl font-serif mb-4 sm:mb-6">Discover Premium Global Brands</h2>
            <p class="text-gray-400 text-xs sm:text-base mb-6 sm:mb-10 max-w-2xl mx-auto">We house an exclusive collection of luxury eyewear brands to give you the perfect look and ultimate comfort.</p>

            <div class="flex flex-wrap justify-center items-center gap-6 sm:gap-10 md:gap-20 opacity-70">
                <?php foreach($active_brands as $b): ?>
                    <span class="text-white text-base sm:text-2xl font-bold tracking-widest uppercase"><?php echo e($b['name']); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Service Excellence / Tested Lens Promise -->
    <section class="relative overflow-hidden bg-white py-12 sm:py-20 border-t border-gray-100">
        <div class="absolute -top-12 -right-12 sm:-top-24 sm:-right-24 w-40 h-40 sm:w-72 sm:h-72 bg-pcwGold/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-12 -left-12 sm:-bottom-24 sm:-left-24 w-40 h-40 sm:w-72 sm:h-72 bg-pcwRed/10 rounded-full blur-3xl"></div>

        <div class="relative w-full px-4 sm:px-8 lg:px-12">
            <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
                <span class="inline-flex items-center gap-1 sm:gap-2 text-pcwRed font-bold uppercase tracking-[0.2em] text-[10px] md:text-sm mb-2 sm:mb-4">
                    <i class="fa-solid fa-star text-[10px] sm:text-sm"></i>
                    The Pal Chasme Wale Promise
                    <i class="fa-solid fa-star text-[10px] sm:text-sm"></i>
                </span>
                <h2 class="text-xl sm:text-3xl md:text-5xl font-bold text-pcwBlack leading-tight">
                    Exceptional Eyewear. <span class="text-pcwRed">Tested Lenses.</span>
                </h2>
                <div class="w-12 sm:w-20 h-1 bg-pcwRed mx-auto mt-3 mb-3 sm:mt-5 sm:mb-5"></div>
                <p class="text-gray-600 text-xs sm:text-base md:text-lg leading-relaxed px-2">
                    From frame selection to lens care, we focus on comfort, clarity and a dependable
                    eyewear experience for every customer.
                </p>
            </div>

            <!-- Changed to grid-cols-2 for mobile -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
                <div class="group bg-gray-50 hover:bg-pcwBlack rounded-2xl p-4 sm:p-7 md:p-8 text-center border border-gray-200 hover:border-pcwBlack transition-all duration-300 hover:-translate-y-1 flex flex-col items-center">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 mb-3 sm:mb-5 rounded-full bg-pcwRed/10 group-hover:bg-pcwRed flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-glasses text-xl sm:text-3xl text-pcwRed group-hover:text-white"></i>
                    </div>
                    <h3 class="text-xs sm:text-xl font-bold text-pcwBlack group-hover:text-white mb-1 sm:mb-2">Premium Eyewear</h3>
                    <p class="text-gray-600 group-hover:text-gray-300 text-[10px] sm:text-sm leading-relaxed">
                        Carefully selected frames for everyday style and comfort.
                    </p>
                </div>

                <div class="group bg-gray-50 hover:bg-pcwBlack rounded-2xl p-4 sm:p-7 md:p-8 text-center border border-gray-200 hover:border-pcwBlack transition-all duration-300 hover:-translate-y-1 flex flex-col items-center">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 mb-3 sm:mb-5 rounded-full bg-pcwGold/15 group-hover:bg-pcwGold flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-eye text-xl sm:text-3xl text-pcwGold group-hover:text-pcwBlack"></i>
                    </div>
                    <h3 class="text-xs sm:text-xl font-bold text-pcwBlack group-hover:text-white mb-1 sm:mb-2">Eye Testing</h3>
                    <p class="text-gray-600 group-hover:text-gray-300 text-[10px] sm:text-sm leading-relaxed">
                        Eye testing is available at our Sultanpur branch.
                    </p>
                </div>

                <div class="group bg-gray-50 hover:bg-pcwBlack rounded-2xl p-4 sm:p-7 md:p-8 text-center border border-gray-200 hover:border-pcwBlack transition-all duration-300 hover:-translate-y-1 flex flex-col items-center">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 mb-3 sm:mb-5 rounded-full bg-pcwRed/10 group-hover:bg-pcwRed flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-certificate text-xl sm:text-3xl text-pcwRed group-hover:text-white"></i>
                    </div>
                    <h3 class="text-xs sm:text-xl font-bold text-pcwBlack group-hover:text-white mb-1 sm:mb-2">Tested Lenses</h3>
                    <p class="text-gray-600 group-hover:text-gray-300 text-[10px] sm:text-sm leading-relaxed">
                        Blue-light, progressive, anti-glare and photochromic options.
                    </p>
                </div>

                <div class="group bg-gray-50 hover:bg-pcwBlack rounded-2xl p-4 sm:p-7 md:p-8 text-center border border-gray-200 hover:border-pcwBlack transition-all duration-300 hover:-translate-y-1 flex flex-col items-center">
                    <div class="w-12 h-12 sm:w-20 sm:h-20 mb-3 sm:mb-5 rounded-full bg-pcwGold/15 group-hover:bg-pcwGold flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-headset text-xl sm:text-3xl text-pcwGold group-hover:text-pcwBlack"></i>
                    </div>
                    <h3 class="text-xs sm:text-xl font-bold text-pcwBlack group-hover:text-white mb-1 sm:mb-2">Personal Service</h3>
                    <p class="text-gray-600 group-hover:text-gray-300 text-[10px] sm:text-sm leading-relaxed">
                        Assistance in choosing frames that suit your needs.
                    </p>
                </div>
            </div>

            <div class="mt-8 sm:mt-10 flex flex-wrap justify-center gap-2 sm:gap-3 text-[10px] sm:text-sm font-semibold">
                <span class="inline-flex items-center gap-1 sm:gap-2 bg-white border border-gray-200 rounded-full px-3 py-1.5 sm:px-5 sm:py-3 shadow-sm">
                    <i class="fa-solid fa-check text-pcwRed"></i> Clear Vision
                </span>
                <span class="inline-flex items-center gap-1 sm:gap-2 bg-white border border-gray-200 rounded-full px-3 py-1.5 sm:px-5 sm:py-3 shadow-sm">
                    <i class="fa-solid fa-check text-pcwRed"></i> Comfortable Fit
                </span>
                <span class="inline-flex items-center gap-1 sm:gap-2 bg-white border border-gray-200 rounded-full px-3 py-1.5 sm:px-5 sm:py-3 shadow-sm">
                    <i class="fa-solid fa-check text-pcwRed"></i> Premium Styles
                </span>
                <span class="inline-flex items-center gap-1 sm:gap-2 bg-white border border-gray-200 rounded-full px-3 py-1.5 sm:px-5 sm:py-3 shadow-sm">
                    <i class="fa-solid fa-check text-pcwRed"></i> Since 1991
                </span>
            </div>
        </div>
    </section>

    <!-- Footer Section (Full Width) -->
    <?php include __DIR__ . "/includes/footer.php"; ?>
