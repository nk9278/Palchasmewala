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

/**
 * Cancel an order safely, restoring inventory and logging transactions.
 * Designed to be idempotent to prevent duplicate stock restoration.
 */
function cancel_order($pdo, $order_id, $reason, $changed_by = null) {
    try {
        $pdo->beginTransaction();

        // Lock the order row to prevent concurrent modifications
        $lock = $pdo->prepare("SELECT order_status, payment_method, payment_status, grand_total, user_id FROM orders WHERE id = ? FOR UPDATE");
        $lock->execute([$order_id]);
        $order = $lock->fetch();

        if (!$order) {
            throw new Exception("Order not found.");
        }

        // Prevent duplicate cancellation
        if (in_array($order['order_status'], ['cancelled', 'returned', 'refunded'])) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Order is already cancelled or returned.'];
        }

        // Only allow cancellation for pending or confirmed orders
        // (If admin overrides this rule in the future, we could add a flag, but this is standard)
        if (!in_array($order['order_status'], ['pending', 'confirmed', 'processing', 'packed'])) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Order cannot be cancelled at this stage.'];
        }

        // Update Order Status
        $pdo->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?")->execute([$order_id]);

        // Fetch Items
        $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$order_id]);

        $upd_stock = $pdo->prepare("UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $ins_inv_trans = $pdo->prepare("INSERT INTO inventory_transactions (product_id, variant_id, transaction_type, quantity, previous_quantity, new_quantity, reference_type, reference_id, note, created_by) VALUES (?, ?, 'cancellation', ?, ?, ?, 'order', ?, ?, ?)");

        foreach ($items->fetchAll() as $item) {
            if ($item['variant_id']) {
                // Lock the variant to check current stock and log properly
                $stk = $pdo->prepare("SELECT stock_quantity FROM product_variants WHERE id = ? FOR UPDATE");
                $stk->execute([$item['variant_id']]);
                $curr_stk = $stk->fetchColumn();

                // Restore Stock
                $upd_stock->execute([$item['quantity'], $item['variant_id']]);
                $new_stock = $curr_stk + $item['quantity'];

                // Log Transaction
                $ins_inv_trans->execute([$item['product_id'], $item['variant_id'], $item['quantity'], $curr_stk, $new_stock, $order_id, $reason, $changed_by]);
            }
        }

        // Status History
        $pdo->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note) VALUES (?, ?, 'cancelled', ?, ?)")->execute([$order_id, $order['order_status'], $changed_by, $reason]);

        // Handle Payments
        if ($order['payment_method'] === 'ONLINE' && in_array($order['payment_status'], ['authorized', 'paid'])) {
            $pdo->prepare("UPDATE orders SET payment_status = 'refund_pending' WHERE id = ?")->execute([$order_id]);
            $pdo->prepare("INSERT INTO refunds (order_id, amount, reason, status) VALUES (?, ?, ?, 'pending')")->execute([$order_id, $order['grand_total'], $reason]);
        }

        $pdo->commit();
        return ['success' => true];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => "Failed to cancel order: " . $e->getMessage()];
    }
}

/**
 * Check if current user is an admin
 */
function is_admin($pdo) {
    if (!is_logged_in()) return false;
    $user_id = current_user_id();
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();
    return $role === 'admin';
}

/**
 * Require admin access or redirect
 */
function require_admin($pdo) {
    if (!is_admin($pdo)) {
        header("HTTP/1.1 403 Forbidden");
        die("403 Forbidden - Administrator access required.");
    }
}
