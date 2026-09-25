<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = current_user_id();
$order_id = $_GET['order_id'] ?? 0;
$item_id = $_GET['item_id'] ?? 0;

// Verify Eligibility
$stmt = $pdo->prepare("SELECT o.order_status, i.product_id, i.product_name, p.slug
                       FROM orders o
                       JOIN order_items i ON o.id = i.order_id
                       JOIN products p ON i.product_id = p.id
                       WHERE o.id = ? AND i.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $item_id, $user_id]);
$details = $stmt->fetch();

if (!$details || !in_array($details['order_status'], ['delivered', 'completed'])) {
    die("You cannot review this item. It may not exist, may not belong to you, or the order is not in a delivered state.");
}

// Check if this item has an approved/received/refunded return
$ret_chk = $pdo->prepare("SELECT SUM(ri.quantity) FROM return_items ri JOIN returns r ON ri.return_id = r.id WHERE ri.order_item_id = ? AND r.status IN ('approved', 'received', 'refunded')");
$ret_chk->execute([$item_id]);
$returned_qty = (int)$ret_chk->fetchColumn();

$orig_qty_chk = $pdo->prepare("SELECT quantity FROM order_items WHERE id = ?");
$orig_qty_chk->execute([$item_id]);
$orig_qty = (int)$orig_qty_chk->fetchColumn();

// If they returned ALL items of this order item, they can't review it.
// If they kept at least one, they can.
if ($returned_qty >= $orig_qty) {
    die("You cannot review this item because it was fully returned or refunded.");
}

$product_id = $details['product_id'];

// Check for existing review
$rev_check = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND order_item_id = ?");
$rev_check->execute([$user_id, $item_id]);
if ($rev_check->fetchColumn()) {
    die("You have already submitted a review for this purchase.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = "CSRF validation failed.";
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $error = "Please provide a valid rating between 1 and 5.";
        } elseif (strlen($review_text) < 5 || strlen($review_text) > 2000) {
            $error = "Review must be between 5 and 2000 characters.";
        } else {
            $image_path = null;
            if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === UPLOAD_ERR_OK) {
                $val = validate_image_upload($_FILES['review_image'], ['jpg', 'jpeg', 'png', 'webp']);
                if ($val['success']) {
                    $ext = pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('rev_') . '.' . $ext;
                    if(!is_dir(UPLOAD_DIR . 'reviews/')) { mkdir(UPLOAD_DIR . 'reviews/', 0777, true); }
                    $target = UPLOAD_DIR . 'reviews/' . $filename;
                    if (move_uploaded_file($_FILES['review_image']['tmp_name'], $target)) {
                        $image_path = '/assets/uploads/reviews/' . $filename;
                    }
                } else {
                    $error = $val['error'];
                }
            }

            if (!$error) {
                try {
                    $ins = $pdo->prepare("INSERT INTO product_reviews (user_id, product_id, order_id, order_item_id, rating, review, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                    $ins->execute([$user_id, $product_id, $order_id, $item_id, $rating, $review_text, $image_path]);
                    $success = "Review submitted successfully! It is pending approval.";
                } catch (PDOException $e) {
                    // Check for duplicate constraint
                    if ($e->getCode() == 23000) {
                        $error = "You have already submitted a review for this purchase.";
                    } else {
                        $error = "A database error occurred. Please try again.";
                    }
                }
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container mx-auto px-4 sm:px-8 lg:px-12 max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-pcwBlack">Write a Review</h1>
            <a href="order.php?id=<?php echo $order_id; ?>" class="text-gray-500 hover:text-pcwBlack text-sm font-bold">&larr; Back to Order</a>
        </div>

        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-6 rounded-xl mb-6 border border-green-200 text-center">
                <p class="font-bold text-lg mb-2"><?php echo e($success); ?></p>
                <a href="../product.php?slug=<?php echo urlencode($details['slug']); ?>" class="text-blue-600 underline">Return to Product Page</a>
            </div>
        <?php else: ?>
            <?php if($error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-6 text-sm border border-red-200"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100">
                <h2 class="font-bold text-lg mb-2">Product: <span class="text-gray-600"><?php echo e($details['product_name']); ?></span></h2>
                <p class="text-sm text-gray-500 mb-6 border-b pb-4">Share your experience with this product.</p>

                <form method="post" enctype="multipart/form-data" class="space-y-6">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Overall Rating *</label>
                        <div class="flex gap-4">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <label class="cursor-pointer flex flex-col items-center group">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" required>
                                <div class="text-gray-300 peer-checked:text-pcwGold text-3xl group-hover:text-pcwGold transition-colors">★</div>
                                <span class="text-xs text-gray-500 mt-1"><?php echo $i; ?></span>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Review *</label>
                        <textarea name="review" rows="5" required minlength="5" maxlength="2000" class="w-full border-2 border-gray-200 rounded-lg p-3 outline-none focus:border-pcwRed transition-colors text-sm" placeholder="What did you like or dislike?"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Add Photo (Optional)</label>
                        <input type="file" name="review_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-2">Max size: 2MB. Supported formats: JPG, PNG, WebP.</p>
                    </div>

                    <button type="submit" class="w-full bg-pcwRed hover:bg-red-800 text-white font-bold py-3 rounded-lg transition-colors shadow-md mt-4">
                        Submit Review
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>