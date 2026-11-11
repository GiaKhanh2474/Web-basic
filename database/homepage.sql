-- Homepage demo products. Import after scripts/setup-homepage.php has added columns.
-- Prices and configurations below are editable demo data, not a live retailer feed.
-- Existing products (including user edits) are preserved when this file is imported again.
INSERT INTO products
    (slug, name, brand, category, price, old_price, image, screen_size, resolution, panel,
     refresh_rate, response_time, usage_type, cpu, ram, storage, gpu, stock, description,
     featured, rating, review_count)
VALUES
    ('pc-cps-gaming-g01-i3-13100f-rx-6500-xt', 'PC CPS Gaming G01 i3 13100F / 8GB / 256GB / RX 6500 XT', 'CPS', 'pc', 11990000, 13990000,
     'img/pc1_png.webp', 0, '', '', 0, '', 'Gaming', 'Intel Core i3 13100F', '8GB', '256GB SSD', 'AMD Radeon RX 6500 XT', 20,
     'Bộ máy để bàn gọn gàng với vi xử lý Intel Core i3 13100F, card đồ họa RX 6500 XT, RAM 8GB và ổ lưu trữ 256GB trên bo mạch chủ H610M.
Cấu hình phù hợp cho học tập, giải trí và các tựa game phổ biến như Liên Minh Huyền Thoại, FIFA Online 4, GTA V, CS:GO. Thùng máy kính cường lực cùng quạt LED mang lại góc máy nổi bật.', 0, 4.8, 36),
    ('pc-cps-gaming-g02-i3-13100f-rtx-3050', 'PC CPS Gaming G02 i3 13100F / 8GB / 256GB / RTX 3050', 'CPS', 'pc', 13790000, 15990000,
     'img/pc2_png.webp', 0, '', '', 0, '', 'Gaming', 'Intel Core i3 13100F', '8GB', '256GB SSD', 'NVIDIA GeForce RTX 3050', 25,
     'Cấu hình gaming gọn nhẹ với Intel Core i3 13100F, card đồ họa GeForce RTX 3050, RAM 8GB, ổ lưu trữ 256GB và bo mạch chủ H610M.
Phù hợp cho nhu cầu chơi game, học tập và làm việc hằng ngày. Thùng máy kính cường lực với quạt LED giúp góc máy thêm đẹp mắt.', 1, 4.9, 58),
    ('pc-msi-gaming-i7-12700-rtx-5060', 'PC MSI Gaming i7 12700 / 16GB / 256GB / RTX 5060', 'MSI', 'pc', 27990000, 31990000,
     'img/pc3_png.webp', 0, '', '', 0, '', 'Gaming', 'Intel Core i7 12700', '16GB', '256GB SSD', 'NVIDIA GeForce RTX 5060', 12,
     'Bộ PC dùng thùng máy MSI với Intel Core i7 12700, card đồ họa GeForce RTX 5060, RAM 16GB, ổ lưu trữ 256GB và bo mạch chủ B760M.
Cấu hình mạnh mẽ cho game, đa nhiệm và các phần mềm thiết kế. Quạt RGB phía trước cùng mặt kính trong suốt tạo điểm nhấn cho góc máy của bạn.', 1, 4.9, 41),
    ('pc-cps-ultra-5-225f-rtx-5050', 'PC CPS Ultra 5 225F / 16GB / 512GB / RTX 5050', 'CPS', 'pc', 22990000, 25990000,
     'img/pc4_png.webp', 0, '', '', 0, '', 'Lập trình', 'Intel Core Ultra 5 225F', '16GB', '512GB SSD', 'NVIDIA GeForce RTX 5050', 15,
     'Cấu hình thế hệ mới với Intel Core Ultra 5 225F, card đồ họa GeForce RTX 5050, RAM 16GB, ổ lưu trữ 512GB và bo mạch chủ B860M.
Dung lượng RAM và ổ cứng rộng rãi giúp làm việc đa nhiệm, lập trình, học tập và giải trí thoải mái hơn.', 0, 4.8, 22),
    ('pc-cps-creator-ultra-ai-i9-14900k-rtx-5070-ti', 'PC CPS Creator Ultra AI i9 14900K / 64GB / 1TB / RTX 5070 Ti', 'CPS', 'pc', 85490000, 95000000,
     'img/pc5_png.webp', 0, '', '', 0, '', 'Đồ họa', 'Intel Core i9 14900K', '64GB', '1TB SSD', 'NVIDIA GeForce RTX 5070 Ti', 5,
     'Cấu hình cao cấp dành cho sáng tạo nội dung và đồ họa với Intel Core i9 14900K, card đồ họa GeForce RTX 5070 Ti, RAM 64GB, ổ lưu trữ 1TB và bo mạch chủ Z790.
Thùng máy kính cong cùng quạt RGB tạo nên góc máy ấn tượng cho công việc dựng phim, thiết kế và giải trí.', 1, 5.0, 12),
    ('laptop-asus-vivobook-15-demo', 'Laptop ASUS Vivobook 15', 'ASUS', 'laptop', 13490000, NULL,
     'assets/images/home/laptop-asus.jpg', 15.6, 'Full HD', 'IPS', 60, '', 'Văn phòng', 'Intel Core i5', '16GB RAM', 'SSD 512GB', 'Intel Iris Xe', 10,
     'Sản phẩm mẫu cho trang chủ. Laptop màn hình 15.6 inch dành cho học tập và công việc hằng ngày. Giá và cấu hình là dữ liệu minh họa để bạn chỉnh sửa theo sản phẩm thực tế.', 0, 0, 0),
    ('laptop-dell-inspiron-15-demo', 'Laptop Dell Inspiron 15 3530', 'Dell', 'laptop', 14990000, NULL,
     'assets/images/home/laptop-dell.jpg', 15.6, 'Full HD', 'IPS', 120, '', 'Văn phòng', 'Intel Core i5', '16GB RAM', 'SSD 512GB', 'Intel UHD', 10,
     'Sản phẩm mẫu cho trang chủ. Laptop cho nhu cầu làm việc, tra cứu tài liệu và giải trí. Thông tin cấu hình và giá là mẫu, không phải dữ liệu bán hàng cập nhật tự động.', 0, 0, 0),
    ('laptop-hp-15-demo', 'Laptop HP Pavilion 15', 'HP', 'laptop', 12490000, NULL,
     'assets/images/home/laptop-hp.jpg', 15.6, 'Full HD', 'IPS', 60, '', 'Văn phòng', 'Intel Core i5', '8GB RAM', 'SSD 512GB', 'Intel Iris Xe', 10,
     'Sản phẩm mẫu cho trang chủ. Thiết kế laptop quen thuộc cho bàn học và văn phòng. Các thông số, ảnh và giá có thể thay đổi trực tiếp trong MySQL.', 0, 0, 0),
    ('laptop-lenovo-ideapad-slim-3-demo', 'Laptop Lenovo IdeaPad Slim 3', 'Lenovo', 'laptop', 12990000, NULL,
     'assets/images/home/laptop-lenovo.jpg', 15.6, 'Full HD', 'IPS', 60, '', 'Văn phòng', 'AMD Ryzen 5', '16GB RAM', 'SSD 512GB', 'AMD Radeon', 10,
     'Sản phẩm mẫu cho trang chủ. Laptop dành cho học tập, làm việc và sử dụng đa nhiệm. Giá cùng cấu hình trong bản demo dùng để minh họa cách phân nhóm sản phẩm.', 0, 0, 0),
    ('laptop-acer-aspire-5-demo', 'Laptop Acer Aspire 5', 'Acer', 'laptop', 10990000, NULL,
     'assets/images/home/laptop-acer.jpg', 15.6, 'Full HD', 'IPS', 60, '', 'Văn phòng', 'AMD Ryzen 5', '8GB RAM', 'SSD 512GB', 'AMD Radeon', 10,
     'Sản phẩm mẫu cho trang chủ. Một cấu hình laptop minh họa dành cho người dùng phổ thông. Bạn có thể thay bằng sản phẩm thật mà không cần sửa giao diện.', 0, 0, 0)
ON DUPLICATE KEY UPDATE slug = VALUES(slug);
