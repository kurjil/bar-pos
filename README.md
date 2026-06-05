# Bar POS & Inventory Management System

Complete POS and inventory system for small bars. Runs on XAMPP (Windows) or Apache + MySQL on LAN.

## XAMPP Quick Start

1. **Start XAMPP** — Apache and MySQL must be running.

2. **Project location** — Linked at `C:\xampp\htdocs\bar-pos` (junction to `C:\Users\USER\bar-pos`).

3. **Open in browser:** [http://localhost/bar-pos/public/setup](http://localhost/bar-pos/public/setup)  
   Or login: [http://localhost/bar-pos/public/login](http://localhost/bar-pos/public/login)

4. **Default admin** (if seeded):
   - Email: `admin@bar.local`
   - Password: `Admin@123`

## Database

Already created: `bar_pos` with all 11 tables.

Re-import if needed:
```bash
C:\xampp\mysql\bin\mysql.exe -u root < database\schema.sql
C:\xampp\mysql\bin\mysql.exe -u root < database\seeders.sql
C:\xampp\mysql\bin\mysql.exe -u root < database\sample_products.sql
```

## Features (All Phases)

- Authentication & roles (Admin, Cashier)
- Products & categories CRUD
- Inventory (stock in, adjustments, history)
- POS with cart, discounts, multiple payment methods
- Shift open/close with cash reconciliation
- Sales list, detail, void (admin)
- Expenses tracking & approval
- Reports (daily sales, by product/category, inventory, expenses, profit)
- User management & password reset
- Settings (business info, tax, printer, backup/restore)
- ESC/POS thermal printing (optional, configure in Settings)
- Audit logging

## Workflow

1. **Admin** — Add categories/products, configure settings
2. **Cashier** — Open shift → POS → Close shift
3. **Admin** — Review reports, expenses, backups

## Printer

Enable in `.env` or Settings → Printer:
```
PRINTER_ENABLED=true
PRINTER_CONNECTOR=windows
PRINTER_PATH=EPSON TM-T20 Receipt
```

## Stack

PHP 8.2+ | MySQL 8 | Bootstrap 5 | Vanilla JS | mike42/escpos-php
