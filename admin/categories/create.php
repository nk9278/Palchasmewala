<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

$cats = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NULL")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF Token verification failed.");
    }

    $name = trim($_POST['name'] ?? '');
    $slug = generate_slug($name);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $status = $_POST['status'] ?? 'active';

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, parent_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $parent_id, $status]);
        redirect('admin/categories/index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-8 max-w-lg">
        <h1 class="text-3xl font-bold mb-6">Add Category</h1>
        <form method="post" class="bg-white p-6 rounded shadow">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Parent Category (Optional)</label>
                <select name="parent_id" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="">None</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo e($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
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
</body>
</html>