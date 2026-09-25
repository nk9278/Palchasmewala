<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

require_admin($pdo);


$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
$stmt->execute([$id]);
$brand = $stmt->fetch();

if (!$brand) {
    die("Brand not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF Token verification failed.");
    }

    $name = trim($_POST['name'] ?? '');
    $slug = generate_slug($name);
    $status = $_POST['status'] ?? 'active';

    if ($name) {
        $stmt = $pdo->prepare("UPDATE brands SET name=?, slug=?, status=? WHERE id=?");
        $stmt->execute([$name, $slug, $status, $id]);
        redirect('admin/brands/index.php');
    }
}
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full max-w-lg">
        <h1 class="text-3xl font-bold mb-6">Edit Brand</h1>
        <form method="post" class="bg-white p-6 rounded shadow">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="<?php echo e($brand['name']); ?>" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="active" <?php echo $brand['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $brand['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Update</button>
        </form>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
