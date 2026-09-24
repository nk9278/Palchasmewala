<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';

$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Categories</h1>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded">Add Category</a>
        </div>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Parent ID</th>
                        <th class="py-3 px-4 text-left">Slug</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr class="border-t">
                        <td class="py-3 px-4"><?php echo e($c['name']); ?></td>
                        <td class="py-3 px-4"><?php echo e($c['parent_id'] ? $c['parent_id'] : '-'); ?></td>
                        <td class="py-3 px-4"><?php echo e($c['slug']); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded text-xs text-white <?php echo $c['status'] == 'active' ? 'bg-green-500' : 'bg-red-500'; ?>">
                                <?php echo e(ucfirst($c['status'])); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="edit.php?id=<?php echo $c['id']; ?>" class="text-blue-500 hover:underline">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><a href="../index.php" class="text-gray-500">&larr; Back to Dashboard</a></div>
    </div>
</body>
</html>