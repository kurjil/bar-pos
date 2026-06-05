-- Seed data for Bar POS system
USE bar_pos;

INSERT INTO roles (name, description) VALUES
    ('ADMIN', 'Full system access'),
    ('CASHIER', 'POS and shift operations');

INSERT INTO settings (key_name, value) VALUES
    ('business_name', 'My Bar'),
    ('business_address', ''),
    ('business_phone', ''),
    ('currency', 'USD'),
    ('tax_rate', '0');

-- After running seeders, visit /setup to create the first admin account.
