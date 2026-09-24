<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, c.name as cat_name, b.name as brand_name
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN brands b ON p.brand_id = b.id
                      WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF Token verification failed.");
    }

    // Add Variant Logic
    if (isset($_POST['add_variant'])) {
        $v_sku = trim($_POST['v_sku']);
        $v_name = trim($_POST['v_name']);
        $v_price = $_POST['v_price'] ?? 0;

        $v_stmt = $pdo->prepare("INSERT INTO product_variants (product_id, sku, variant_name, price) VALUES (?, ?, ?, ?)");
        $v_stmt->execute([$id, $v_sku, $v_name, $v_price]);

        // redirect to self to clear post
        redirect("admin/products/view.php?id=$id");
    }

    // Add Image Logic
    if (isset($_POST['upload_image'])) {
        if (isset($_FILES['p_image'])) {
            $val = validate_image_upload($_FILES['p_image']);
            if ($val['success']) {
                $ext = pathinfo($_FILES['p_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prod_') . '.' . $ext;
                $target = UPLOAD_DIR . 'products/' . $filename;

                if (move_uploaded_file($_FILES['p_image']['tmp_name'], $target)) {
                    $img_path = '/assets/uploads/products/' . $filename;
                    $is_primary = isset($_POST['is_primary']) ? 1 : 0;

                    if ($is_primary) {
                        // unset existing primary for this product
                        $pdo->prepare("UPDATE product_images SET is_primary=0 WHERE product_id=?")->execute([$id]);
                    }

                    $i_stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                    $i_stmt->execute([$id, $img_path, $is_primary]);
                }
            } else {
                $error = $val['error'];
            }
        }
    }
}

// Fetch Variants & Images
$variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$variants->execute([$id]);
$variants = $variants->fetchAll();

$images = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$images->execute([$id]);
$images = $images->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Product - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-8">
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Product: <?php echo e($product['name']); ?></h1>
            <div>
                <a href="edit.php?id=<?php echo $id; ?>" class="bg-blue-600 text-white px-4 py-2 rounded mr-2">Edit Product</a>
                <a href="index.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Back</a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-200 text-red-800 p-3 mb-4 rounded"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Details -->
            <div class="bg-white shadow p-6 rounded">
                <h2 class="text-xl font-bold mb-4">Details</h2>
                <p><strong>SKU:</strong> <?php echo e($product['sku']); ?></p>
                <p><strong>Category:</strong> <?php echo e($product['cat_name']); ?></p>
                <p><strong>Brand:</strong> <?php echo e($product['brand_name'] ?? '-'); ?></p>
                <p><strong>Base Price:</strong> <?php echo format_price($product['base_price']); ?></p>
                <p><strong>Status:</strong> <?php echo e($product['status']); ?></p>
            </div>

            <!-- Images -->
            <div class="bg-white shadow p-6 rounded">
                <h2 class="text-xl font-bold mb-4">Images</h2>
                <div class="flex gap-2 flex-wrap mb-4">
                    <?php foreach ($images as $img): ?>
                        <div class="relative border p-1 rounded">
                            <img src="<?php echo e($img['image_path']); ?>" alt="Img" class="h-20 w-20 object-contain">
                            <?php if ($img['is_primary']): ?>
                                <span class="absolute top-0 left-0 bg-yellow-400 text-xs px-1 text-white font-bold">Primary</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="post" enctype="multipart/form-data" class="space-y-2 border-t pt-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="upload_image" value="1">
                    <input type="file" name="p_image" accept="image/*" required class="block w-full">
                    <label class="block"><input type="checkbox" name="is_primary"> Set as Primary</label>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Upload Image</button>
                </form>
            </div>
        </div>

        <!-- Variants -->
        <div class="bg-white shadow p-6 rounded mt-8">
            <h2 class="text-xl font-bold mb-4">Variants & Inventory Overview</h2>
            <table class="min-w-full text-sm mb-6">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-3 text-left">Variant Name</th>
                        <th class="py-2 px-3 text-left">SKU</th>
                        <th class="py-2 px-3 text-left">Price</th>
                        <th class="py-2 px-3 text-left">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variants as $v): ?>
                    <tr class="border-t">
                        <td class="py-2 px-3"><?php echo e($v['variant_name']); ?></td>
                        <td class="py-2 px-3"><?php echo e($v['sku']); ?></td>
                        <td class="py-2 px-3"><?php echo format_price($v['price']); ?></td>
                        <td class="py-2 px-3 font-bold"><?php echo e($v['stock_quantity']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3 class="font-bold border-b pb-2 mb-4">Add Variant</h3>
            <form method="post" class="flex gap-4 items-end">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_variant" value="1">
                <div>
                    <label class="block text-xs">Name (e.g. Red / Medium)</label>
                    <input type="text" name="v_name" required class="border rounded p-1 w-full">
                </div>
                <div>
                    <label class="block text-xs">SKU</label>
                    <input type="text" name="v_sku" required class="border rounded p-1 w-full">
                </div>
                <div>
                    <label class="block text-xs">Price</label>
                    <input type="number" step="0.01" name="v_price" required class="border rounded p-1 w-full">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded">Add</button>
            </form>
        </div>

    </div>
</body>
</html>