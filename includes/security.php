<?php
// includes/security.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * CSRF HTML Input
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(generate_csrf_token()) . '">';
}

/**
 * Basic upload validation
 */
function validate_image_upload($file, $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error code: ' . $file['error']];
    }

    // Check file size (e.g. max 2MB)
    if ($file['size'] > 2097152) {
         return ['success' => false, 'error' => 'File size exceeds 2MB limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions)];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed_mimes)) {
        return ['success' => false, 'error' => 'Invalid MIME type.'];
    }

    return ['success' => true];
}
