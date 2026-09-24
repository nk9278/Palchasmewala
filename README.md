# Pal Chasme Wale E-Commerce - Phase 1 & 2

This is the Core PHP foundation for the Pal Chasme Wale e-commerce website.
It converts the original static index into a scalable, dynamic PHP structure using PDO and standard configurations.

## Architecture & File Structure
```text
/
├── index.php             # Main homepage, dynamic products injection
├── config/
│   └── config.php        # Application configuration (DB credentials, base URL, etc.)
├── includes/
│   ├── db.php            # PDO database connection
│   ├── functions.php     # Helper functions (e.g., e(), url(), slugs)
│   ├── header.php        # Site header and navigation
│   ├── footer.php        # Site footer
│   └── security.php      # CSRF and file upload validation logic
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/          # Contains /products and /brands
├── admin/
│   ├── brands/           # Brand CRUD
│   ├── categories/       # Category CRUD
│   ├── products/         # Product, Variant, and Image management
│   └── inventory/        # Inventory and Stock Adjustments
└── database.sql          # Full Database schema and Seed Data
```

## Installation Instructions
1. **Upload project files** to your server.
2. **Create MySQL database**.
3. **Import `database.sql`** into the new database.
4. **Configure database credentials** in `config/config.php` (set `DB_NAME`, `DB_USER`, `DB_PASS`).
5. **Configure base URL** in `config/config.php` (`BASE_URL`).
6. **Open the website** in your browser.

## Phase 2 Features Added
- Relational schema added via `database.sql` covering `categories`, `brands`, `products`, `product_variants`, `product_images`, and `inventory_transactions`.
- Populated database with realistic dummy eyewear seed data.
- Implemented robust Admin panels without heavy frameworks, utilizing Tailwind classes.
- Integrated database data fetching into `index.php` preserving the pristine front-end design entirely.
- Safe file uploads for product imagery securely mapping extension and MIME type.
- Inventory modification uses ACID compliant `FOR UPDATE` query patterns securely recording transaction history.

## Limitations & Assumptions
- Checkout, Cart, and full User authentication are placeholders leading to future files (`/cart.php`, `/login.php`). They will be implemented in subsequent phases.
