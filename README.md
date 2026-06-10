# Bar POS & Inventory Management System

A complete point-of-sale and inventory system for small bars and restaurants. Built with PHP, MySQL, and Bootstrap — runs locally on XAMPP (Windows) or Apache + MySQL on any computer.

---

## Table of Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Install from GitHub](#install-from-github)
4. [Install Dependencies](#install-dependencies)
5. [Configure Environment](#configure-environment)
6. [Database Setup](#database-setup)
7. [First Run & Admin Account](#first-run--admin-account)
8. [Printer Setup (Optional)](#printer-setup-optional)
9. [Daily Workflow](#daily-workflow)
10. [Project Structure](#project-structure)
11. [Troubleshooting](#troubleshooting)

---

## Features

- **Authentication** — Admin and Cashier roles
- **POS** — Cart, discounts, cash / mobile money / card payments, thermal receipt printing
- **Shifts** — Open/close shifts, cash reconciliation, float in / cash drop, shift history
- **Inventory** — Products, categories, stock in, adjustments, low-stock alerts
- **Sales** — Search, filter, reprint receipts, void sales (admin)
- **Expenses** — Track and approve business expenses
- **Reports** — Daily sales, end-of-day (sales vs expenses), by product, by category, profit summary
- **Export** — Download reports as Excel-compatible CSV files
- **Settings** — Business info, tax rate, printer, database backup/restore
- **Audit log** — Tracks important actions across the system

---

## Requirements

Install these **before** setting up the app.

### Required Software

| Software | Version | Purpose |
|----------|---------|---------|
| **PHP** | 8.2 or higher | Application runtime |
| **MySQL** or **MariaDB** | MySQL 8+ or MariaDB 10.5+ | Database |
| **Apache** (with `mod_rewrite`) | 2.4+ | Web server |
| **Composer** | 2.x | PHP dependency manager |

### Easiest option on Windows: [XAMPP](https://www.apachefriends.org/)

XAMPP includes PHP, Apache, and MySQL in one installer. Download the version that includes **PHP 8.2+**.

### PHP Extensions (enable in `php.ini`)

These must be enabled (remove the `;` in front of each line in XAMPP’s `php.ini`):

```ini
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=openssl
```

In XAMPP, edit: `C:\xampp\php\php.ini` → search for each extension → remove `;` → restart Apache.

### Composer

Download and install from [getcomposer.org](https://getcomposer.org/download/).

Verify installations:

```bash
php -v          # Should show 8.2 or higher
composer -V     # Should show Composer 2.x
```

---

## Install from GitHub

### 1. Clone the repository

**Option A — XAMPP (recommended on Windows)**

```bash
cd C:\xampp\htdocs
git clone https://github.com/YOUR_USERNAME/bar-pos.git bar-pos
cd bar-pos
```

**Option B — Any folder**

```bash
git clone https://github.com/YOUR_USERNAME/bar-pos.git
cd bar-pos
```

> Replace `YOUR_USERNAME` with the actual GitHub username or organization.

### 2. Enable Apache rewrite (XAMPP)

1. Open `C:\xampp\apache\conf\httpd.conf`
2. Find `#LoadModule rewrite_module` and remove the `#` so it reads:
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Restart Apache from the XAMPP Control Panel.

The project’s `public/.htaccess` file handles URL routing. Without `mod_rewrite`, pages like `/login` will return 404.

---

## Install Dependencies

From the project root folder, run:

```bash
composer install
```

This installs:

| Package | Purpose |
|---------|---------|
| `mike42/escpos-php` | Thermal receipt and shift report printing |

The `vendor/` folder is created automatically and is **not** included in Git — you must run `composer install` after every fresh clone.

---

## Configure Environment

### 1. Create your `.env` file

```bash
# Windows (Command Prompt)
copy .env.example .env

# Windows (PowerShell) / Linux / macOS
cp .env.example .env
```

### 2. Edit `.env`

Open `.env` in a text editor and set these values:

```env
APP_NAME="Bar POS"
APP_URL=http://localhost/bar-pos/public
DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=bar_pos
DB_USER=root
DB_PASS=

SESSION_TIMEOUT=28800
```

| Variable | Description |
|----------|-------------|
| `APP_URL` | Full URL to the `public` folder — **no trailing slash**. Change this if your folder name or port differs. |
| `DB_HOST` | MySQL server address (`127.0.0.1` for local) |
| `DB_PORT` | MySQL port (default `3306`) |
| `DB_NAME` | Database name (`bar_pos`) |
| `DB_USER` | MySQL username (`root` on XAMPP default) |
| `DB_PASS` | MySQL password (empty on XAMPP default) |
| `DEBUG` | Set `true` during setup; set `false` in production |

**APP_URL examples:**

| Setup | APP_URL |
|-------|---------|
| XAMPP, folder `bar-pos` in htdocs | `http://localhost/bar-pos/public` |
| XAMPP, virtual host pointing to `public/` | `http://barpos.local` |
| Custom port 8080 | `http://localhost:8080/bar-pos/public` |

### 3. Storage folders

Ensure these folders exist and are writable by the web server:

```
storage/logs/
storage/backups/
storage/uploads/
```

They are created automatically on first use. On Linux, if you get permission errors:

```bash
chmod -R 775 storage
```

---

## Database Setup

The database must be created and seeded **before** you open the app in a browser.

### Method 1: Command line (recommended)

**Windows (XAMPP)** — run from the project root:

```cmd
C:\xampp\mysql\bin\mysql.exe -u root < database\schema.sql
C:\xampp\mysql\bin\mysql.exe -u root bar_pos < database\seeders.sql
C:\xampp\mysql\bin\mysql.exe -u root bar_pos < database\add_cash_movements.sql
C:\xampp\mysql\bin\mysql.exe -u root bar_pos < database\sample_products.sql
```

**Linux / macOS:**

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p bar_pos < database/seeders.sql
mysql -u root -p bar_pos < database/add_cash_movements.sql
mysql -u root -p bar_pos < database/sample_products.sql
```

### Method 2: phpMyAdmin

1. Start **Apache** and **MySQL** in XAMPP.
2. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Click **Import** and run each file **in this order**:

| Order | File | What it does |
|-------|------|--------------|
| 1 | `database/schema.sql` | Creates database `bar_pos` and all tables |
| 2 | `database/seeders.sql` | Inserts roles (Admin, Cashier) and default settings |
| 3 | `database/add_cash_movements.sql` | Adds shift cash movement tracking table |
| 4 | `database/sample_products.sql` | *(Optional)* Sample categories and products |

### What gets created

- **Database:** `bar_pos`
- **Tables:** roles, users, settings, categories, products, shifts, sales, sale_items, inventory_movements, expenses, audit_logs, shift_cash_movements
- **Roles:** `ADMIN`, `CASHIER`
- **Default settings:** business name, currency, tax rate

### Verify the database

In phpMyAdmin or MySQL CLI:

```sql
USE bar_pos;
SHOW TABLES;
SELECT * FROM roles;
```

You should see 2 roles and 12 tables.

### Reset the database (start fresh)

```cmd
C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS bar_pos;"
```

Then re-run all SQL files from Method 1.

---

## First Run & Admin Account

1. Start **Apache** and **MySQL**.
2. Open your browser:

   ```
   http://localhost/bar-pos/public/setup
   ```

   (Adjust the URL to match your `APP_URL`.)

3. Fill in the **Initial Setup** form:
   - Your name
   - Email address
   - Password (minimum 8 characters)

4. Click **Create Admin Account** — you are logged in automatically.

5. From the dashboard, configure:
   - **Settings → General** — business name, address, phone, currency, tax rate
   - **Settings → Printer** — optional thermal printer (see below)
   - **Products / Categories** — add your menu items

### Alternative: create admin via command line

If the web setup page does not work, run after importing the database:

```bash
php database/create_admin.php
```

Default credentials:

- **Email:** `admin@bar.local`
- **Password:** `Admin@123`

Change this password immediately after first login.

### Login URL

```
http://localhost/bar-pos/public/login
```

---

## Printer Setup (Optional)

Receipts print automatically when you complete a sale (if the printer is enabled).

### Configure in the app (recommended)

1. Log in as **Admin**.
2. Go to **Settings → Printer** tab.
3. Check **Enable thermal printer**.
4. Enter your **exact Windows printer name** (from **Settings → Bluetooth & devices → Printers & scanners** in Windows).
5. Click **Save Printer Settings**, then **Test Print**.

### Or configure in `.env`

```env
PRINTER_ENABLED=true
PRINTER_CONNECTOR=windows
PRINTER_PATH=EPSON TM-T20 Receipt
```

For network printers:

```env
PRINTER_CONNECTOR=network
PRINTER_HOST=192.168.1.100
PRINTER_PORT=9100
```

### Supported printers

Any ESC/POS compatible thermal printer (Epson TM series, Star, Bixolon, etc.) connected via USB, shared Windows printer, or network.

---

## Daily Workflow

### Admin (one-time / periodic)

1. Add categories and products
2. Configure business settings and tax rate
3. Create cashier user accounts (**Users** menu)
4. Review reports and expenses
5. Run database backups (**Settings → Backup**)

### Cashier (each shift)

1. **Open Shift** — enter opening cash float
2. **POS** — ring up sales (receipt prints automatically if printer enabled)
3. **Close Shift** — count cash, review discrepancy
4. View shift report and print if needed

### Quick actions (dashboard)

- **Print Last Receipt** — reprint your most recent sale
- **Shift History** — view and print past shifts (admin sees all cashiers)

---

## Project Structure

```
bar-pos/
├── app/
│   ├── controllers/     # Request handlers
│   ├── models/          # Database models
│   ├── views/           # HTML templates
│   ├── helpers/         # Router, Receipt printer, etc.
│   ├── middleware/      # Auth, CSRF
│   └── config/          # App, database, printer config
├── database/
│   ├── schema.sql       # Main database schema
│   ├── seeders.sql      # Roles and default settings
│   ├── add_cash_movements.sql
│   └── sample_products.sql
├── public/              # Web root (point Apache here)
│   ├── index.php        # Front controller
│   └── assets/          # CSS, JavaScript
├── storage/
│   ├── logs/            # Application logs
│   ├── backups/         # SQL backups
│   └── uploads/         # Product images, receipts
├── vendor/              # Composer packages (run composer install)
├── .env                 # Your local config (not in Git)
├── .env.example         # Template for .env
└── composer.json
```

---

## Troubleshooting

### Blank page or 500 error

- Set `DEBUG=true` in `.env` and reload to see the error message.
- Check `storage/logs/app.log`.
- Confirm `composer install` was run and `vendor/` exists.

### "Database connection failed"

- MySQL is running in XAMPP.
- `.env` credentials match your MySQL setup (`DB_USER`, `DB_PASS`, `DB_NAME`).
- Database was imported: run `database/schema.sql` first.

### 404 on all pages except home

- Enable `mod_rewrite` in Apache (see [Install from GitHub](#install-from-github)).
- Confirm `APP_URL` in `.env` matches your browser URL.

### "Class not found" or vendor errors

```bash
composer install
```

### Setup page says "Admin role not found"

Run the seeders — roles were not imported:

```bash
mysql -u root -p bar_pos < database/seeders.sql
```

### Printer not working

- Only **Admin** can configure the printer (**Settings → Printer** tab).
- Printer name must match Windows **exactly** (case and spacing).
- Click **Test Print** after saving.
- Sales still complete even if printing fails — use **Print Last Receipt** or reprint from **Sales**.

### CSS/JS not loading

- `APP_URL` in `.env` must match the URL you use in the browser.
- Include `/public` in the URL unless Apache document root points directly to `public/`.

### Permission denied on storage

```bash
chmod -R 775 storage
```

On Windows, ensure the folder is not read-only.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+ (custom MVC) |
| Database | MySQL 8 / MariaDB 10.5+ |
| Frontend | Bootstrap 5, vanilla JavaScript |
| Printing | mike42/escpos-php (ESC/POS thermal) |
| Dependencies | Composer |

---

## License

Private / internal use. Update this section if you publish under an open-source license.
