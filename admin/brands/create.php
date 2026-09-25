<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

require_admin($pdo);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF Token verification failed.");
    }

    $name = trim($_POST['name'] ?? '');
    $slug = generate_slug($name);
    $status = $_POST['status'] ?? 'active';

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO brands (name, slug, status) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $status]);
        redirect('admin/brands/index.php');
    }
}
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
    <div class="w-full max-w-lg">
        <h1 class="text-3xl font-bold mb-6">Add Brand</h1>
        <form method="post" class="bg-white p-6 rounded shadow">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded">Save</button>
        </form>
    </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
