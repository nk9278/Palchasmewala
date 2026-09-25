<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

require_admin($pdo);


$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT r.*, p.name as product_name, u.name as customer_name, u.email, o.order_number
                       FROM product_reviews r
                       JOIN products p ON r.product_id = p.id
                       JOIN users u ON r.user_id = u.id
                       JOIN orders o ON r.order_id = o.id
                       WHERE r.id = ?");
$stmt->execute([$id]);
$review = $stmt->fetch();

if (!$review) die("Review not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) die("CSRF validation failed.");

    $status = $_POST['status'] ?? '';
    $admin_note = trim($_POST['admin_note'] ?? '');

    if (in_array($status, ['pending', 'approved', 'rejected'])) {
        $pdo->prepare("UPDATE product_reviews SET status = ?, admin_note = ? WHERE id = ?")->execute([$status, $admin_note, $id]);
        redirect("admin/reviews/view.php?id=$id");
    }
}

?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Review Details</h1>
            <a href="index.php" class="bg-gray-300 px-4 py-2 rounded">Back</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="space-y-6">
                <!-- Content -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Review Content</h2>
                    <div class="text-yellow-500 text-2xl mb-2"><?php echo str_repeat('★', $review['rating']); ?><span class="text-gray-300"><?php echo str_repeat('★', 5 - $review['rating']); ?></span></div>
                    <p class="text-gray-700 italic leading-relaxed mb-4">"<?php echo nl2br(e($review['review'])); ?>"</p>

                    <?php if($review['image_path']): ?>
                        <h3 class="font-bold text-sm mb-2 border-t pt-4">Attached Image</h3>
                        <a href="<?php echo e($review['image_path']); ?>" target="_blank">
                            <img src="<?php echo e($review['image_path']); ?>" class="w-full max-w-[200px] border rounded object-contain">
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="bg-white shadow rounded-lg p-6 text-sm">
                    <h2 class="text-lg font-bold mb-4 border-b pb-2">Context</h2>
                    <p class="mb-2"><strong>Product:</strong> <?php echo e($review['product_name']); ?></p>
                    <p class="mb-2"><strong>Customer:</strong> <?php echo e($review['customer_name']); ?> (<?php echo e($review['email']); ?>)</p>
                    <p class="mb-2"><strong>Order:</strong> <a href="../orders/view.php?id=<?php echo $review['order_id']; ?>" class="text-blue-600 underline"><?php echo e($review['order_number']); ?></a></p>
                    <p><strong>Date:</strong> <?php echo date('d M Y H:i', strtotime($review['created_at'])); ?></p>
                </div>
            </div>

            <!-- Moderation -->
            <div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Moderation</h2>
                    <form method="post" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-sm font-bold mb-1">Status</label>
                            <select name="status" class="w-full border p-2 rounded">
                                <option value="pending" <?php echo $review['status']=='pending'?'selected':''; ?>>Pending</option>
                                <option value="approved" <?php echo $review['status']=='approved'?'selected':''; ?>>Approved</option>
                                <option value="rejected" <?php echo $review['status']=='rejected'?'selected':''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Admin Notes (Internal)</label>
                            <textarea name="admin_note" rows="4" class="w-full border p-2 rounded"><?php echo e($review['admin_note']); ?></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded mt-2 hover:bg-blue-700">Update Review</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
