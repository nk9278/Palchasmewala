<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$error = '';
$success = '';

// Handle Cart Actions
if ($action) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            die("CSRF verification failed.");
        }
    }

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
    $qty = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['remove_key'])) {
            $action = 'remove';
            $_GET['key'] = $_POST['remove_key'];
        }
        if (isset($_POST['clear_cart'])) {
            $action = 'clear';
        }
    }

    if ($action === 'add' && $product_id) {
        // Fetch current active product and variant data from DB securely
        $p_stmt = $pdo->prepare("SELECT status FROM products WHERE id = ?");
        $p_stmt->execute([$product_id]);
        $prod = $p_stmt->fetch();

        if ($prod && $prod['status'] === 'active') {

            // If variant is provided, validate it
            $stock_limit = 999;
            if ($variant_id) {
                $v_stmt = $pdo->prepare("SELECT status, stock_quantity FROM product_variants WHERE id = ? AND product_id = ?");
                $v_stmt->execute([$variant_id, $product_id]);
                $var = $v_stmt->fetch();

                if (!$var || $var['status'] !== 'active') {
                    $error = "Selected option is not available.";
                } else {
                    $stock_limit = $var['stock_quantity'];
                }
            } else {
                // If it's a product that HAS variants, force variant selection
                $check_v = $pdo->prepare("SELECT id FROM product_variants WHERE product_id = ? AND status='active'");
                $check_v->execute([$product_id]);
                if ($check_v->rowCount() > 0) {
                    $error = "Please select a valid option before adding to cart.";
                }
            }

            if (!$error) {
                $cart_key = $product_id . '_' . $variant_id;
                $current_qty = isset($_SESSION['cart'][$cart_key]) ? $_SESSION['cart'][$cart_key]['quantity'] : 0;

                if ($current_qty + $qty > $stock_limit) {
                    $error = "Cannot add that quantity. Not enough stock available.";
                } else {
                    $_SESSION['cart'][$cart_key] = [
                        'product_id' => $product_id,
                        'variant_id' => $variant_id,
                        'quantity' => $current_qty + $qty
                    ];
                    $success = "Item added to cart successfully.";

                    // PRG Pattern
                    if($_SERVER['REQUEST_METHOD'] === 'POST'){
                        redirect('cart.php');
                    }
                }
            }
        } else {
            $error = "Product is currently unavailable.";
        }
    }
    elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['cart_items']) && is_array($_POST['cart_items'])){
            foreach($_POST['cart_items'] as $key => $new_qty) {
                $new_qty = (int)$new_qty;
                if (isset($_SESSION['cart'][$key])) {
                    if ($new_qty <= 0) {
                        unset($_SESSION['cart'][$key]);
                    } else {
                        // Stock Check
                        $v_id = $_SESSION['cart'][$key]['variant_id'];
                        if ($v_id) {
                            $st = $pdo->prepare("SELECT stock_quantity FROM product_variants WHERE id=?");
                            $st->execute([$v_id]);
                            $stck = $st->fetchColumn();
                            if ($new_qty > $stck) {
                                $error = "Some items had insufficient stock and were adjusted to maximum available.";
                                $new_qty = $stck;
                            }
                        }
                        $_SESSION['cart'][$key]['quantity'] = $new_qty;
                    }
                }
            }
        }
        if(!$error) $success = "Cart updated successfully.";
        redirect('cart.php');
    }
    elseif ($action === 'remove' && isset($_GET['key']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $key = $_GET['key'];
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
            redirect('cart.php');
        }
    }
    elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
        redirect('cart.php');
    }
}

// Load Cart Details for Display
$cart_items = [];
$cart_subtotal = 0;
$cart_warnings = [];

foreach ($_SESSION['cart'] as $key => $item) {
    $p_id = $item['product_id'];
    $v_id = $item['variant_id'];
    $qty = $item['quantity'];

    // Fetch fresh live data
    $q = "SELECT p.name, p.status as p_status,
          v.variant_name, v.price, v.stock_quantity, v.status as v_status,
          (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM products p
          LEFT JOIN product_variants v ON v.id = ?
          WHERE p.id = ?";

    $stmt = $pdo->prepare($q);
    $stmt->execute([$v_id, $p_id]);
    $row = $stmt->fetch();

    if ($row) {
        if ($row['p_status'] !== 'active' || ($v_id && $row['v_status'] !== 'active')) {
            $cart_warnings[] = "An item in your cart is no longer available and has been removed.";
            unset($_SESSION['cart'][$key]);
            continue;
        }

        if ($v_id && $qty > $row['stock_quantity']) {
            $cart_warnings[] = "Stock for '{$row['name']}' has changed. Adjusted quantity to available stock.";
            $qty = $row['stock_quantity'];
            if ($qty <= 0) {
                 unset($_SESSION['cart'][$key]);
                 continue;
            } else {
                 $_SESSION['cart'][$key]['quantity'] = $qty;
            }
        }

        $price = $row['price']; // variant price
        $subtotal = $price * $qty;
        $cart_subtotal += $subtotal;

        $cart_items[$key] = [
            'key' => $key,
            'name' => $row['name'],
            'variant_name' => $row['variant_name'],
            'price' => $price,
            'quantity' => $qty,
            'subtotal' => $subtotal,
            'image' => $row['primary_image'] ?? 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80',
            'max_stock' => $v_id ? $row['stock_quantity'] : 999
        ];
    } else {
        unset($_SESSION['cart'][$key]);
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 max-w-6xl">
        <h1 class="text-3xl font-bold text-pcwBlack mb-8">Shopping Cart</h1>

        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <?php foreach($cart_warnings as $w): ?>
             <div class="bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded relative mb-4">
                <?php echo e($w); ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white p-12 text-center rounded-2xl shadow-sm border border-gray-100">
                <i class="fa-solid fa-cart-shopping text-6xl text-gray-200 mb-6 block"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Your cart is currently empty</h2>
                <p class="text-gray-500 mb-8">Looks like you haven't added anything yet.</p>
                <a href="shop.php" class="inline-block bg-pcwRed hover:bg-red-800 text-white font-bold py-3 px-8 rounded transition-colors shadow-md">
                    Return to Shop
                </a>
            </div>
        <?php else: ?>
            <form method="post" action="cart.php" class="flex flex-col lg:flex-row gap-8">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update">

                <!-- Cart Items -->
                <div class="w-full lg:w-2/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                        <!-- Desktop Header -->
                        <div class="hidden sm:grid grid-cols-12 gap-4 p-4 border-b bg-gray-50 font-bold text-gray-600 text-sm uppercase">
                            <div class="col-span-6">Product</div>
                            <div class="col-span-2 text-center">Price</div>
                            <div class="col-span-2 text-center">Quantity</div>
                            <div class="col-span-2 text-right">Total</div>
                        </div>

                        <!-- Items -->
                        <div class="divide-y divide-gray-100">
                            <?php foreach($cart_items as $item): ?>
                            <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                                <!-- Mobile Image + Details -->
                                <div class="col-span-1 sm:col-span-6 flex gap-4 items-center">
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 rounded-lg border border-gray-100 p-2 flex-shrink-0">
                                        <img src="<?php echo e($item['image']); ?>" alt="Img" class="w-full h-full object-contain">
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-sm sm:text-base"><?php echo e($item['name']); ?></h3>
                                        <?php if($item['variant_name']): ?>
                                            <p class="text-xs text-gray-500 mt-1">Option: <?php echo e($item['variant_name']); ?></p>
                                        <?php endif; ?>
                                        <button type="submit" name="remove_key" value="<?php echo $item['key']; ?>" class="text-red-500 text-xs sm:text-sm hover:underline mt-2 inline-block"><i class="fa-solid fa-trash-can"></i> Remove</button>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="col-span-1 sm:col-span-2 sm:text-center text-sm font-bold text-gray-700 hidden sm:block">
                                    <?php echo format_price($item['price']); ?>
                                </div>

                                <!-- Qty -->
                                <div class="col-span-1 sm:col-span-2 flex items-center sm:justify-center">
                                    <div class="flex items-center border border-gray-300 rounded h-8 w-24">
                                        <button type="button" onclick="this.nextElementSibling.stepDown()" class="w-8 h-full flex items-center justify-center text-gray-600 hover:bg-gray-100">-</button>
                                        <input type="number" name="cart_items[<?php echo $item['key']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['max_stock']; ?>" class="w-8 h-full text-center outline-none text-sm font-bold no-spinners">
                                        <button type="button" onclick="this.previousElementSibling.stepUp()" class="w-8 h-full flex items-center justify-center text-gray-600 hover:bg-gray-100">+</button>
                                    </div>
                                </div>

                                <!-- Subtotal -->
                                <div class="col-span-1 sm:col-span-2 text-right font-bold text-pcwBlack sm:text-base">
                                    <span class="sm:hidden text-gray-500 text-xs font-normal">Total: </span>
                                    <?php echo format_price($item['subtotal']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-4 sm:p-6 bg-gray-50 border-t flex justify-between items-center">
                            <button type="submit" name="clear_cart" value="1" class="text-sm text-gray-500 hover:text-red-600 underline cursor-pointer bg-transparent border-none">Clear Cart</button>
                            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded font-semibold hover:bg-black transition-colors text-sm">Update Cart</button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Order Summary</h3>

                        <div class="space-y-4 mb-6 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-bold"><?php echo format_price($cart_subtotal); ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span class="text-green-600 font-bold">Free</span>
                            </div>
                        </div>

                        <div class="border-t pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-lg text-gray-800">Total</span>
                                <span class="font-bold text-xl text-pcwRed"><?php echo format_price($cart_subtotal); ?></span>
                            </div>
                        </div>

                        <a href="checkout.php" class="block w-full bg-pcwRed hover:bg-red-800 text-white text-center font-bold py-3 rounded-lg transition-colors shadow-md text-sm sm:text-base mb-3">
                            Proceed to Checkout
                        </a>
                        <a href="shop.php" class="block text-center text-sm text-gray-500 hover:text-pcwBlack hover:underline">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>

<style>
/* Remove increment arrows from number inputs safely */
.no-spinners::-webkit-outer-spin-button,
.no-spinners::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.no-spinners {
  -moz-appearance: textfield;
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>