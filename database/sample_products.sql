-- Sample products for demo (run after schema + seeders)
USE bar_pos;

INSERT INTO categories (name, description) VALUES
    ('Beer', 'Local and imported beers'),
    ('Spirits', 'Whisky, vodka, gin'),
    ('Soft Drinks', 'Non-alcoholic beverages'),
    ('Snacks', 'Bar snacks and nuts');

INSERT INTO products (category_id, name, purchase_price, selling_price, stock_quantity, minimum_stock, is_favorite) VALUES
    (1, 'Local Lager', 1.50, 3.00, 48, 10, 1),
    (1, 'Imported Beer', 2.00, 4.50, 24, 6, 1),
    (2, 'House Whisky (shot)', 0.80, 2.50, 100, 20, 1),
    (2, 'Premium Gin (shot)', 1.20, 3.50, 80, 15, 0),
    (3, 'Cola', 0.60, 1.50, 36, 12, 0),
    (3, 'Mineral Water', 0.40, 1.00, 48, 12, 0),
    (4, 'Peanuts', 0.50, 1.50, 30, 10, 0),
    (4, 'Chips', 0.70, 2.00, 25, 8, 0);
