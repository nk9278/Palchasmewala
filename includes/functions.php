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
