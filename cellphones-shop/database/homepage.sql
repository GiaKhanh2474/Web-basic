-- Homepage demo products. Import after scripts/setup-homepage.php has added columns.
-- Prices and configurations below are editable demo data, not a live retailer feed.
-- Existing products (including user edits) are preserved when this file is imported again.
INSERT INTO products
    (slug, name, brand, category, price, old_price, image, screen_size, resolution, panel,
     refresh_rate, response_time, usage_type, cpu, ram, storage, gpu, stock, description,
     featured, rating, review_count)
VALUES
    ('pc-dell-optiplex-7010-demo', 'Máy tính Dell OptiPlex 7010', 'Dell', 'pc', 12990000, NULL,
     'assets/images/home/pc-dell.jpg', 0, '', '', 0, '', 'Văn phòng', 'Intel Core i5', '16GB RAM', 'SSD 512GB', 'Intel UHD', 10,
     'Sản phẩm mẫu cho trang chủ. Máy tính để bàn dành cho công việc văn phòng, học tập và xử lý tài liệu. Cấu hình và giá là dữ liệu minh họa, có thể chỉnh sửa trực tiếp trong MySQL.', 0, 0, 0),
    ('pc-hp-pro-tower-400-g9-demo', 'Máy tính HP Pro Tower 400 G9', 'HP', 'pc', 11490000, NULL,
     'assets/images/home/pc-hp.jpg', 0, '', '', 0, '', 'Văn phòng', 'Intel Core i5', '8GB RAM', 'SSD 512GB', 'Intel UHD', 10,
     'Sản phẩm mẫu cho trang chủ. Thiết kế máy tính dạng tháp phù hợp bàn làm việc và không gian học tập. Cấu hình và giá là dữ liệu minh họa để bạn thay bằng thông tin thực tế.', 0, 0, 0),
    ('pc-asus-expertcenter-d500-demo', 'Máy tính ASUS ExpertCenter D500', 'ASUS', 'pc', 13990000, NULL,
     'assets/images/home/pc-asus.jpg', 0, '', '', 0, '', 'Văn phòng', 'Intel Core i5', '16GB RAM', 'SSD 512GB', 'Intel UHD', 10,
     'Sản phẩm mẫu cho trang chủ. Máy tính để bàn cho các tác vụ công việc thường ngày. Thông số và giá trong bản demo có thể cập nhật trong bảng products.', 0, 0, 0),
    ('pc-msi-mag-infinite-s3-demo', 'Máy tính MSI MAG Infinite S3', 'MSI', 'pc', 21990000, NULL,
     'assets/images/home/pc-msi.jpg', 0, '', '', 0, '', 'Gaming', 'Intel Core i5', '16GB RAM', 'SSD 1TB', 'GeForce RTX 4060', 10,
     'Sản phẩm mẫu cho trang chủ. Một lựa chọn minh họa dành cho góc gaming và giải trí. Cấu hình và giá không phải báo giá bán hàng trực tiếp; hãy cập nhật theo sản phẩm của bạn.', 0, 0, 0),
    ('pc-lenovo-legion-t5-demo', 'PC Gaming Lenovo Legion T5', 'Lenovo', 'pc', 16990000, NULL,
     'assets/images/home/pc-gaming.jpg', 0, '', '', 0, '', 'Gaming', 'AMD Ryzen 5', '16GB RAM', 'SSD 512GB', 'GeForce RTX 3060', 10,
     'Sản phẩm mẫu cho trang chủ. Cấu hình PC minh họa cho nhu cầu chơi game và làm việc đa nhiệm. Bạn có thể đổi tên, thông số, ảnh, giá và số lượng trực tiếp trong MySQL.', 0, 0, 0),
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
