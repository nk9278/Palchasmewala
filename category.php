<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    redirect('shop.php');
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    die("Category not found.");
}

// Redirect to the shop page with the category filter applied
redirect('shop.php?category=' . urlencode($category['slug']));
