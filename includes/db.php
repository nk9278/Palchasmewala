<?php
// includes/db.php

// Ensure config is loaded
require_once __DIR__ . '/../config/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // We don't want to actually connect for this prototype phase if the db doesn't exist yet,
    // so we handle the exception gracefully without exposing details in production.
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Database Connection failed: " . $e->getMessage());
    } else {
        die("A database error occurred. Please try again later.");
    }
}
