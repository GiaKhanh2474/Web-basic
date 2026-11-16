-- Import this file in phpMyAdmin (SQL or Import). Existing products are preserved.
CREATE DATABASE IF NOT EXISTS cellphones_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cellphones_shop;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(190) NOT NULL,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(80) NOT NULL,
    category VARCHAR(20) NOT NULL DEFAULT 'monitor',
    price INT UNSIGNED NOT NULL,
    old_price INT UNSIGNED DEFAULT NULL,
    image VARCHAR(500) NOT NULL DEFAULT 'assets/images/xiaomi-a27qi.webp',
    screen_size DECIMAL(4,1) NOT NULL DEFAULT 27,
    resolution VARCHAR(40) NOT NULL DEFAULT '2K',
    panel VARCHAR(40) NOT NULL DEFAULT 'IPS',
    refresh_rate INT UNSIGNED NOT NULL DEFAULT 120,
    response_time VARCHAR(30) NOT NULL DEFAULT '6ms',
    usage_type VARCHAR(60) NOT NULL DEFAULT 'Văn phòng',
    cpu VARCHAR(100) NOT NULL DEFAULT '',
    ram VARCHAR(100) NOT NULL DEFAULT '',
    storage VARCHAR(100) NOT NULL DEFAULT '',
    gpu VARCHAR(100) NOT NULL DEFAULT '',
    stock INT UNSIGNED NOT NULL DEFAULT 20,
    description TEXT DEFAULT NULL,
    featured TINYINT UNSIGNED NOT NULL DEFAULT 0,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    review_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY products_slug_unique (slug),
    KEY products_brand_index (brand),
    KEY products_category_index (category),
    KEY products_price_index (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo catalog. Prices and stock are editable sample data, not a live price feed.
INSERT INTO products (slug,name,brand,price,old_price,image,screen_size,resolution,panel,refresh_rate,response_time,usage_type,stock,description,featured,rating,review_count) VALUES
('man-hinh-xiaomi-a27qi-2026-27-inch','Màn hình Xiaomi A27QI 2026 27 inch','Xiaomi',3790000,4490000,'assets/images/xiaomi-a27qi.webp',27,'2K','IPS',120,'6ms','Văn phòng',30,'Không gian hiển thị 27 inch rộng rãi với độ phân giải 2560 × 1440. Tấm nền IPS mang lại màu sắc hài hòa và góc nhìn rộng. Tần số quét 120Hz giúp thao tác cuộn trang, làm việc và giải trí mượt mà hơn. Thiết kế viền mỏng dễ kết hợp với góc làm việc hiện đại.',1,4.9,128),
('man-hinh-gaming-lg-ultragear-27g411a-b-27-inch','Màn hình Gaming LG UltraGear 27G411A-B 27 inch','LG',2490000,3990000,'assets/images/lg-ultragear.webp',27,'Full HD','IPS',144,'1ms','Gaming',25,'Màn hình 27 inch dành cho góc chơi game và giải trí. Thiết kế mạnh mẽ cùng hình ảnh mượt mà giúp bạn tập trung vào từng khoảnh khắc.',1,4.9,96),
('man-hinh-samsung-odyssey-g5-27-inch','Màn hình Gaming Samsung Odyssey G5 27 inch','Samsung',4990000,6490000,'assets/images/samsung-odyssey.webp',27,'2K','VA',165,'1ms','Gaming',15,'Khám phá khung hình 2K sắc nét trên màn hình gaming 27 inch. Tần số quét cao hỗ trợ trải nghiệm giải trí và làm việc đa nhiệm.',1,4.8,84),
('man-hinh-asus-tuf-vg259q5a-25-inch','Màn hình Gaming ASUS TUF VG259Q5A 25 inch','ASUS',2790000,3390000,'assets/images/asus-tuf.webp',25,'Full HD','IPS',200,'1ms','Gaming',20,'Màn hình gaming ASUS TUF với thiết kế gọn gàng. Tấm nền IPS phù hợp cho giải trí hằng ngày, học tập và các tác vụ cần nhiều chuyển động.',1,4.9,73),
('man-hinh-dell-ultrasharp-u2424h-24-inch','Màn hình Dell UltraSharp U2424H 24 inch','Dell',5190000,6990000,'assets/images/dell-ultrasharp.webp',24,'Full HD','IPS',120,'5ms','Đồ họa',18,'Màn hình Dell 24 inch cho góc làm việc chuyên nghiệp. Thiết kế tối giản và không gian hiển thị rõ ràng hỗ trợ xử lý tài liệu và công việc sáng tạo.',0,4.9,62),
('man-hinh-msi-pro-mp243l-e14-24-inch','Màn hình MSI Pro MP243L E14 24 inch','MSI',2190000,2690000,'assets/images/msi-pro.webp',24,'Full HD','IPS',144,'1ms','Văn phòng',40,'Màn hình văn phòng 24 inch với tấm nền IPS. Kích thước gọn gàng phù hợp bàn học và bàn làm việc tại nhà.',0,4.8,54),
('man-hinh-xiaomi-a24i-24-inch','Màn hình Xiaomi A24i 24 inch','Xiaomi',1990000,2490000,'assets/images/xiaomi-a24i.webp',24,'Full HD','IPS',100,'6ms','Văn phòng',35,'Màn hình Xiaomi với kiểu dáng thanh lịch, phù hợp học tập và làm việc mỗi ngày. Kích thước 24 inch giúp bố trí góc bàn gọn gàng.',0,4.8,48),
('man-hinh-asus-proart-pa278qev-27-inch','Màn hình ASUS ProArt PA278QEV 27 inch','ASUS',6790000,8990000,'assets/images/asus-proart.webp',27,'2K','IPS',75,'5ms','Đồ họa',12,'Màn hình 27 inch cho công việc sáng tạo. Độ phân giải 2K cung cấp thêm không gian cho các công cụ thiết kế và chỉnh sửa hình ảnh.',0,4.9,41),
('man-hinh-aoc-24g50z2-24-inch','Màn hình Gaming AOC 24G50Z2 24 inch','AOC',2690000,3490000,'assets/images/aoc-gaming.webp',24,'Full HD','VA',180,'1ms','Gaming',22,'Màn hình AOC 24 inch dành cho không gian giải trí nhỏ gọn. Tần số quét cao giúp chuyển động được thể hiện liên tục hơn.',0,4.7,36),
('man-hinh-lg-32ur500-32-inch','Màn hình LG 32UR500 32 inch 4K','LG',6390000,7990000,'assets/images/lg-4k.webp',32,'4K','VA',60,'4ms','Đồ họa',10,'Không gian hiển thị 32 inch với độ phân giải 4K cho hình ảnh chi tiết. Phù hợp xem nội dung đa phương tiện và mở nhiều cửa sổ công việc.',0,4.8,31),
('man-hinh-msi-g275-e14-27-inch','Màn hình Gaming MSI G275 E14 27 inch','MSI',2590000,3090000,'assets/images/msi-gaming.webp',27,'Full HD','IPS',144,'1ms','Gaming',28,'Màn hình gaming 27 inch với không gian hiển thị thoải mái. Tấm nền IPS hỗ trợ góc nhìn rộng cho làm việc và giải trí.',0,4.8,27),
('man-hinh-dell-p2725h-27-inch','Màn hình Dell P2725H 27 inch','Dell',4690000,5590000,'assets/images/dell-office.webp',27,'Full HD','IPS',100,'5ms','Lập trình',16,'Màn hình 27 inch phù hợp công việc lập trình, tài liệu và đa nhiệm. Thiết kế chuyên nghiệp dễ phối với nhiều không gian làm việc.',0,4.9,24)
ON DUPLICATE KEY UPDATE slug = VALUES(slug);
