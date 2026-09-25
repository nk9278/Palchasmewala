<?php
// config/config.php

// Environment Settings
define('ENVIRONMENT', 'development'); // development or production

// Site Configuration
define('SITE_NAME', 'Pal Chasme Wale');
define('BASE_URL', 'http://localhost:8000');
define('CURRENCY', '₹');

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'pal_chasme_wale');
define('DB_USER', 'testuser');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Directories
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

// Error Reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
