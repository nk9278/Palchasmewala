# Pal Chasme Wale E-Commerce - Phase 1

This is the Core PHP Phase 1 foundation for the Pal Chasme Wale e-commerce website.
It converts the original static index into a scalable, dynamic PHP structure using PDO and standard configurations.

## Architecture & File Structure
```text
/
├── index.php             # The main homepage, merging header and footer
├── config/
│   └── config.php        # Application configuration (DB credentials, base URL, etc.)
├── includes/
│   ├── db.php            # PDO database connection
│   ├── functions.php     # Common helper functions (e.g. e() for escaping)
│   ├── header.php        # Site header and navigation
│   ├── footer.php        # Site footer
│   └── security.php      # Security helper functions (to be expanded)
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   ├── images/           # Application images
│   └── uploads/          # Upload directory for dynamic content
├── admin/
│   └── index.php         # Admin panel placeholder
└── database.sql          # Foundation database schema
```

## Installation Instructions
1. **Upload project files** to your server.
2. **Create MySQL database**.
3. **Import `database.sql`** into the new database.
4. **Configure database credentials** in `config/config.php` (set `DB_NAME`, `DB_USER`, `DB_PASS`).
5. **Configure base URL** in `config/config.php` (`BASE_URL`).
6. **Open the website** in your browser.

## Features Completed
- Scalable directory structure separated into configs, includes, assets, and admin.
- `index.php` created utilizing `include`s for dynamic header and footer injection.
- Reusable functions defined for HTML escaping (`e()`), URL handling (`url()`), and formatters.
- Secure, centralized PDO implementation for Database connections.
- Retained absolute design fidelity of the provided HTML with existing tailwind/styling/js.
