<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

require_admin($pdo);


$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF Token verification failed.");
    }

    $name = trim($_POST['name'] ?? '');
    $slug = generate_slug($name);
    $sku = trim($_POST['sku'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $brand_id = !empty($_POST['brand_id']) ? $_POST['brand_id'] : null;
    $base_price = $_POST['base_price'] ?? 0;

    if ($name && $sku && $category_id) {
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, sku, category_id, brand_id, base_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $sku, $category_id, $brand_id, $base_price]);
        $new_id = $pdo->lastInsertId();
        redirect("admin/products/view.php?id=$new_id");
    }
}
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full max-w-xl">
        <h1 class="text-3xl font-bold mb-6">Add Product</h1>
        <form method="post" class="bg-white p-6 rounded shadow space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700">Product Name</label>
                <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">SKU</label>
                <input type="text" name="sku" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" required class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="">Select Category...</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Brand</label>
                <select name="brand_id" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="">None / No Brand</option>
                    <?php foreach($brands as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo e($b['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Base Price (<?php echo CURRENCY; ?>)</label>
                <input type="number" step="0.01" min="0" name="base_price" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Create & Continue Setup</button>
        </form>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
