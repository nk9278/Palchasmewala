<?php
// includes/functions.php

/**
 * Escape HTML for output protection against XSS
 */
function e($string) {
    if (is_null($string)) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a complete URL
 */
function url($path = '') {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirect to a specific path
 */
function redirect($path) {
    header("Location: " . url($path));
    exit;
}

/**
 * Format price with currency
 */
function format_price($amount) {
    return CURRENCY . number_format($amount, 2);
}

/**
 * Generate a URL-friendly slug
 */
function generate_slug($string) {
    $slug = preg_replace('~[^\pL\d]+~u', '-', $string);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '-', $slug);
    $slug = strtolower($slug);

    if (empty($slug)) {
        return 'n-a';
    }

    return $slug;
}

/**
 * Get Cart Count from Session
 */
function get_cart_count() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function current_user_id() {
    return is_logged_in() ? $_SESSION['user_id'] : null;
}
