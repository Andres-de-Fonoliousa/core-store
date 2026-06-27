SHOW VARIABLES LIKE 'character_set_server';
SHOW VARIABLES LIKE 'collation_server';
SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'StoreApp' AND TABLE_NAME IN ('categories', 'products');
SELECT COLUMN_NAME, COLLATION_NAME, CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'StoreApp' AND TABLE_NAME = 'categories' AND COLUMN_NAME IN ('name', 'slug');
SELECT COLUMN_NAME, COLLATION_NAME, CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'StoreApp' AND TABLE_NAME = 'products' AND COLUMN_NAME IN ('name', 'description');
SELECT id, name FROM categories LIMIT 5;
SELECT id, name FROM products LIMIT 5;
