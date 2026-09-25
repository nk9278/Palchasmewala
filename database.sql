-- database.sql
-- Pal Chasme Wale Foundation Database (Phase 1 & 2)

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text,
  `description` text,
  `product_type` varchar(50) NOT NULL DEFAULT 'standard',
  `sku` varchar(100) NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `stock_status` enum('in_stock','out_of_stock','preorder') NOT NULL DEFAULT 'in_stock',
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `bestseller` tinyint(1) NOT NULL DEFAULT 0,
  `new_arrival` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `variant_name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `frame_color` varchar(50) DEFAULT NULL,
  `lens_color` varchar(50) DEFAULT NULL,
  `lens_type` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) NOT NULL DEFAULT 5,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `transaction_type` enum('opening','purchase','adjustment','sale','cancellation','return','manual') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `note` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- FOREIGN KEYS (if engine supports them safely in schema creation)
ALTER TABLE `categories` ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL;
ALTER TABLE `products` ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT;
ALTER TABLE `products` ADD CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL;
ALTER TABLE `product_variants` ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE;
ALTER TABLE `product_images` ADD CONSTRAINT `fk_image_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE;
ALTER TABLE `product_images` ADD CONSTRAINT `fk_image_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE;
ALTER TABLE `inventory_transactions` ADD CONSTRAINT `fk_inv_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE;
ALTER TABLE `inventory_transactions` ADD CONSTRAINT `fk_inv_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE;

-- SEED DATA
INSERT INTO `brands` (`id`, `name`, `slug`, `status`) VALUES
(1, 'Ray-Ban', 'ray-ban', 'active'),
(2, 'Oakley', 'oakley', 'active'),
(3, 'Gucci', 'gucci', 'active'),
(4, 'Vogue', 'vogue', 'active'),
(5, 'Fastrack', 'fastrack', 'active');

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `status`, `image`) VALUES
(1, NULL, 'Eyeglasses', 'eyeglasses', 'active', NULL),
(2, NULL, 'Sunglasses', 'sunglasses', 'active', NULL),
(3, NULL, 'Computer Glasses', 'computer-glasses', 'active', NULL),
(4, NULL, 'Contact Lenses', 'contact-lenses', 'active', NULL),
(5, 1, 'Men (Eyeglasses)', 'men-eyeglasses', 'active', 'https://images.unsplash.com/photo-1580482228658-def52f18f4b1?auto=format&fit=crop&q=85&w=1400'),
(6, 1, 'Women (Eyeglasses)', 'women-eyeglasses', 'active', 'https://images.unsplash.com/photo-1509695507497-903c140c43b0?auto=format&fit=crop&w=800&q=80'),
(7, 1, 'Kids (Eyeglasses)', 'kids-eyeglasses', 'active', 'https://images.unsplash.com/photo-1685950925275-281298061f98?auto=format&fit=crop&q=85&w=1400');

-- Insert seed products
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `base_price`, `status`, `bestseller`, `new_arrival`) VALUES
(1, 2, 1, 'Classic Gold Aviator', 'classic-gold-aviator', 'RB-AVI-GLD', 2499.00, 'active', 1, 0),
(2, 2, 1, 'Matte Black Wayfarer', 'matte-black-wayfarer', 'RB-WAY-BLK', 1899.00, 'active', 1, 0),
(3, 3, 5, 'Retro Round Frames', 'retro-round-frames', 'FT-RND-BLU', 1499.00, 'active', 1, 0),
(4, 1, 2, 'Titanium Rimless', 'titanium-rimless', 'OK-TIT-RIM', 3299.00, 'active', 1, 1),
(5, 2, 2, 'Sports Wrap-Around', 'sports-wrap-around', 'OK-SPT-WRP', 1999.00, 'active', 1, 0),
(6, 1, 4, 'Clear Frame Vintage', 'clear-frame-vintage', 'VG-CLR-VIN', 2199.00, 'active', 1, 0),
(7, 2, 1, 'Polarized Wayfarer', 'polarized-wayfarer', 'RB-WAY-POL', 2199.00, 'active', 0, 0),
(8, 2, 1, 'Classic Aviator Gold', 'classic-aviator-gold-2', 'RB-AVI-GLD-2', 2599.00, 'active', 0, 0),
(9, 2, 5, 'Retro Round Shades', 'retro-round-shades', 'FT-RND-SHD', 1899.00, 'active', 0, 0),
(10, 2, 3, 'Tortoise Shell Square', 'tortoise-shell-square', 'GC-TRT-SQR', 2299.00, 'active', 0, 0);

-- Insert seed variants (1-to-1 for now to represent the product)
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `variant_name`, `price`, `stock_quantity`) VALUES
(1, 1, 'RB-AVI-GLD-V1', 'Standard', 2499.00, 50),
(2, 2, 'RB-WAY-BLK-V1', 'Standard', 1899.00, 30),
(3, 3, 'FT-RND-BLU-V1', 'Standard', 1499.00, 100),
(4, 4, 'OK-TIT-RIM-V1', 'Standard', 3299.00, 20),
(5, 5, 'OK-SPT-WRP-V1', 'Standard', 1999.00, 45),
(6, 6, 'VG-CLR-VIN-V1', 'Standard', 2199.00, 60),
(7, 7, 'RB-WAY-POL-V1', 'Standard', 2199.00, 25),
(8, 8, 'RB-AVI-GLD-2-V1', 'Standard', 2599.00, 15),
(9, 9, 'FT-RND-SHD-V1', 'Standard', 1899.00, 35),
(10, 10, 'GC-TRT-SQR-V1', 'Standard', 2299.00, 10);

-- Insert seed images
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`) VALUES
(1, 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=500&q=80', 1),
(2, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80', 1),
(3, 'https://images.unsplash.com/photo-1582142407894-ec85a1260a46?auto=format&fit=crop&w=500&q=80', 1),
(4, 'https://images.unsplash.com/photo-1577803645773-f96470509666?auto=format&fit=crop&w=500&q=80', 1),
(5, 'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=500&q=80', 1),
(6, 'https://images.unsplash.com/photo-1589642380614-4a8c2147b857?auto=format&fit=crop&w=500&q=80', 1),
(7, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80', 1),
(8, 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=500&q=80', 1),
(9, 'https://images.unsplash.com/photo-1589642380614-4a8c2147b857?auto=format&fit=crop&w=500&q=80', 1),
(10, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80', 1);


-- PHASE 3: Users, Addresses, Wishlist

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `address_type` enum('home','work','other') NOT NULL DEFAULT 'home',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wishlist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user_addresses` ADD CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;
ALTER TABLE `wishlist_items` ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;
ALTER TABLE `wishlist_items` ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE;


-- PHASE 4: Orders, Payments, Returns, History

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `payment_method` enum('COD', 'ONLINE') NOT NULL DEFAULT 'COD',
  `payment_status` enum('pending', 'authorized', 'paid', 'failed', 'refund_pending', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded') NOT NULL DEFAULT 'pending',
  `shipping_status` varchar(50) DEFAULT NULL,
  `customer_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `order_status` (`order_status`),
  KEY `payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `address_type` varchar(20) DEFAULT 'home',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `variant_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `status` varchar(50) NOT NULL,
  `gateway_response` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('requested', 'approved', 'rejected', 'received', 'refunded', 'cancelled') NOT NULL DEFAULT 'requested',
  `reason` varchar(255) NOT NULL,
  `customer_notes` text,
  `admin_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `refund_reference` varchar(255) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foreign Keys
ALTER TABLE `orders` ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT;
ALTER TABLE `order_addresses` ADD CONSTRAINT `fk_orderaddr_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
ALTER TABLE `order_items` ADD CONSTRAINT `fk_orderitem_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
ALTER TABLE `order_status_history` ADD CONSTRAINT `fk_orderhist_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
ALTER TABLE `payments` ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
ALTER TABLE `returns` ADD CONSTRAINT `fk_return_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
ALTER TABLE `returns` ADD CONSTRAINT `fk_return_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT;
ALTER TABLE `return_items` ADD CONSTRAINT `fk_returnitem_return` FOREIGN KEY (`return_id`) REFERENCES `returns`(`id`) ON DELETE CASCADE;
ALTER TABLE `return_items` ADD CONSTRAINT `fk_returnitem_orderitem` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE RESTRICT;
ALTER TABLE `refunds` ADD CONSTRAINT `fk_refund_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;

-- PHASE 5: Reviews and Ratings

CREATE TABLE IF NOT EXISTS `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `review` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_item` (`user_id`, `order_item_id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `product_reviews` ADD CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE;
ALTER TABLE `product_reviews` ADD CONSTRAINT `fk_rev_prod` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE;
ALTER TABLE `product_reviews` ADD CONSTRAINT `fk_rev_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE;
ALTER TABLE `product_reviews` ADD CONSTRAINT `fk_rev_orderitem` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE CASCADE;

-- PHASE 6: Admin Roles
ALTER TABLE `users` ADD COLUMN `role` enum('customer', 'admin') NOT NULL DEFAULT 'customer' AFTER `password`;
