-- 1. Put the product image inside cellphones-shop/assets/images/.
-- 2. Choose the cellphones_shop database in phpMyAdmin, open SQL, and run your INSERT.
-- 3. Use a UNIQUE slug. Reload the storefront: the product and its detail page are ready.
-- 4. category selects the homepage row: monitor, pc, or laptop.
-- Run scripts/setup-homepage.php first if upgrading an older database.
USE cellphones_shop;

INSERT INTO products (
    category, slug, name, brand, price, old_price, image,
    screen_size, resolution, panel, refresh_rate, response_time,
    usage_type, stock, description
) VALUES (
    'monitor',
    'man-hinh-moi-27-inch',
    'Màn hình mới 27 inch',
    'Xiaomi',
    3290000,
    3990000,
    'assets/images/xiaomi-a27qi.webp',
    27,
    '2K',
    'IPS',
    120,
    '6ms',
    'Văn phòng',
    20,
    'Mô tả sản phẩm mới của bạn.'
);

-- Edit existing products directly in phpMyAdmin, or run:
-- UPDATE products SET price = 3190000, stock = 25 WHERE slug = 'man-hinh-moi-27-inch';
-- Set stock = 0 for an out-of-stock product.

-- Optional PC example: remove the surrounding /* and */ to run.
-- Replace the sample name, price, specifications, image and stock with your own.
/*
INSERT INTO products (
    category, slug, name, brand, price, old_price, image,
    cpu, ram, storage, gpu,
    screen_size, resolution, panel, refresh_rate, response_time,
    usage_type, stock, description
) VALUES (
    'pc', 'pc-van-phong-cua-ban', 'PC văn phòng của bạn', 'Dell',
    12990000, NULL, 'assets/images/home/pc-dell.jpg',
    'Intel Core i5', '16GB', '512GB SSD', 'Intel UHD Graphics',
    0, '', '', 0, '',
    'Văn phòng', 10,
    'Dữ liệu mẫu: thay cấu hình, giá và mô tả bằng thông tin sản phẩm của bạn.'
);
*/

-- Optional laptop example: remove the surrounding /* and */ to run.
/*
INSERT INTO products (
    category, slug, name, brand, price, old_price, image,
    cpu, ram, storage, gpu,
    screen_size, resolution, panel, refresh_rate, response_time,
    usage_type, stock, description
) VALUES (
    'laptop', 'laptop-hoc-tap-cua-ban', 'Laptop học tập của bạn', 'ASUS',
    13990000, NULL, 'assets/images/home/laptop-asus.jpg',
    'Intel Core i5', '16GB', '512GB SSD', 'Intel Iris Xe Graphics',
    15.6, 'Full HD', 'IPS', 60, '',
    'Văn phòng', 10,
    'Dữ liệu mẫu: thay cấu hình, giá và mô tả bằng thông tin sản phẩm của bạn.'
);
*/
