# CellphoneS-inspired technology store — PHP + MySQL

A Vietnamese storefront with a homepage, monitor catalog, product details, and session-based shopping cart. The homepage displays three horizontally scrollable product rows in this order: **Màn hình → Máy tính → Laptop**. Product names, prices, stock, descriptions and images come from MySQL. There is no checkout, payment collection, account login, or order processing. The included catalog is editable demonstration data.

## Run with XAMPP

1. Place this folder at `C:\xampp\htdocs\cellphones-shop`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Initialize the database with the setup command below. It creates `cellphones_shop`, seeds twelve monitors, and adds five sample PCs and five sample laptops. Running setup again preserves existing product edits.
4. Open **http://localhost/cellphones-shop/** for the homepage. **http://localhost/cellphones-shop/monitor.php** remains the monitor category page; its filename is retained for this project.

Initialize from PowerShell after starting MySQL:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\scripts\setup.php'
```

For an existing installation, upgrade only the homepage data and fields with:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\scripts\setup-homepage.php'
```

This adds the category and computer specification fields, preserves existing products, and inserts missing demo products. You may also import `database/schema.sql` through phpMyAdmin for a fresh installation, then run `scripts/setup-homepage.php`. `database/homepage.sql` contains sample data only and requires the new fields; it is not a standalone migration. Schema changes never run during page requests.

The setup script only runs from the command line. PHP 8.1+ with `pdo_mysql` and `mbstring`, and MySQL 5.7+ / MariaDB 10.4+ are required. No Composer or Node dependencies are needed.

## Database connection

Edit `config/database.php` if your MySQL connection differs from XAMPP defaults:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `cellphones_shop`
- Username: `root`
- Password: empty (local XAMPP default)

The same values may be overridden with `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` environment variables. If deploying to your own PHP hosting, use a dedicated database user and update these settings for that server.

## Add or update products in MySQL

Open **phpMyAdmin → cellphones_shop → products → Insert**. Fill in a unique `slug`, `name`, `brand`, `price`, image path, specifications and stock. Set `category` to `monitor`, `pc`, or `laptop` to choose the homepage row. Save and refresh the homepage. Each new product automatically gets its own working detail page and add-to-cart action; no PHP changes are needed. Products with `category = monitor` also appear in the monitor catalog at `monitor.php`.

For a copy-and-edit SQL example, use `database/add-product-example.sql`. Prices are integer Vietnamese đồng (`3790000` = 3.790.000đ). Image paths are relative to this project, for example `assets/images/xiaomi-a27qi.webp`. Put your own images in `assets/images/`. Recommended filter values are:

| Field | Example values |
| --- | --- |
| `category` | `monitor` = Màn hình (default), `pc` = Máy tính, `laptop` = Laptop |
| `cpu` | `Intel Core i5` (PC/laptop) |
| `ram` | `16GB` (PC/laptop) |
| `storage` | `512GB SSD` (PC/laptop) |
| `gpu` | Graphics card or integrated graphics name (PC/laptop) |
| `usage_type` | `Gaming`, `Văn phòng`, `Đồ họa`, `Lập trình` |
| `resolution` | `Full HD`, `2K`, `4K` |
| `panel` | `IPS`, `VA`, `OLED` |
| `screen_size` | `24`, `25`, `27`, `32` |
| `refresh_rate` | `100`, `120`, `144`, `165` (number, in Hz) |
| `stock` | Available quantity; `0` means sold out |
| `featured` | `1` to prioritize a product in the recommended sort |

For PCs, leave display text fields empty and use `0` for display size/refresh rate; the detail page shows CPU, RAM, storage and graphics instead. Laptops can include both computer and display specifications. Homepage search covers all three categories; search within `monitor.php` covers monitors.

The five PCs and five laptops added for the homepage are **sample entries**. Their prices, configurations, stock and descriptions should be replaced with your own catalog information before using them for sales. Their slugs end in `-demo`, making them easy to find in phpMyAdmin. Existing monitor data is preserved.

## Menu Danh mục — liên kết các website

Nút **Danh mục** trên thanh đầu trang mở ba mục riêng: **Màn hình**, **PC**, **Laptop**, với biểu tượng tương ứng. Menu dùng chung cho trang chủ, danh sách, chi tiết và giỏ hàng; hỗ trợ điện thoại, bấm ra ngoài và phím Escape để đóng.

Chỉnh URL tại `config/categories.php` khi các website khác sẵn sàng. Ví dụ:

```php
['category' => 'pc', 'name' => 'PC', 'icon' => 'tower', 'url' => 'https://example.com/pc/'],
['category' => 'laptop', 'name' => 'Laptop', 'icon' => 'laptop', 'url' => '/laptop/'],
```

Đường dẫn bắt đầu bằng `/` tính từ gốc máy chủ; URL đầy đủ chuyển đến website tương ứng; đường dẫn tương đối tính từ thư mục dự án hiện tại. Hiện URL của PC và Laptop để trống (`''`) để cấu hình sau: trong menu đầu trang, mỗi mục chỉ hiển thị tên và biểu tượng. Các lối tắt trên trang chủ và chân trang tạm dẫn đến dãy sản phẩm tương ứng trên trang chủ. Khi điền URL, các liên kết danh mục sẽ dùng URL mới. Thẻ sản phẩm vẫn mở trang chi tiết chung tại `product.php`. Không có website danh mục PC/Laptop riêng được tạo thêm.

## Cart behavior

Adding a product shows **Đã thêm vào giỏ hàng (product name)**. The cart remains available across page reloads within the same browser session. Adding the same product increases its quantity. Quantities can be changed and items removed on the cart page. Prices and available stock are read on the server; client-submitted prices are never used. The cart accepts at most 99 units of one product and never exceeds that product's stock. Stock is not reserved or deducted because this project has no checkout.

`POST api/cart.php` accepts JSON with `action` (`add`, `update`, `remove`), `product_id`, `quantity`, and `csrf_token`. Successful responses include `success`, `message`, `count`, `total`, and `items`. Invalid input returns 422, invalid CSRF returns 403, unsupported methods return 405, and unavailable MySQL returns 503.

## Files

- `index.php`: homepage with three scrollable category rows and global search
- `monitor.php`: monitor catalog and filters (retained filename)
- `product.php`: database-driven product detail
- `cart.php`: shopping cart
- `includes/`: shared layout, catalog and session helpers
- `api/cart.php`: validated cart mutations
- `config/database.php`: PDO database settings
- `config/categories.php`: configurable category destinations
- `database/schema.sql`: schema and sample catalog
- `database/homepage.sql`: sample PC and laptop data
- `database/add-product-example.sql`: template for adding products
- `scripts/setup.php`: command-line database initialization
- `scripts/setup-homepage.php`: repeatable upgrade for existing databases
- `assets/`: styles, JavaScript and product images

## Verify the installation

The integration checks cover live PHP pages, MySQL product insertion, HTML escaping, session isolation, stock validation, cart totals, homepage categories, and the repeatable homepage migration. The smoke test creates one temporary product and removes it after the checks. Cart checks use their own sessions:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\tests\smoke.php' 'http://localhost/cellphones-shop/'
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\tests\homepage.php' 'http://localhost/cellphones-shop/'
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\scripts\verify-assets.php' 'http://localhost/cellphones-shop/'
```

Apache also supports the reference-style URLs `/cellphones-shop/man-hinh.html`, `/cellphones-shop/man-hinh-xiaomi-a27qi-2026-27-inch.html`, and `/cellphones-shop/gio-hang.html`. Standard `.php` links work without rewrite configuration.

The product photos are included locally. The optional `scripts/download-assets.ps1` downloads monitor photos again; `scripts/download-home-assets.ps1` downloads the PC/laptop photos. Use `-Force` only to replace the bundled photos. Homepage image credits are in `assets/images/home/SOURCES.md`. Your own products may use any browser-supported image type. The Google Fonts stylesheet is optional; the site falls back to Arial if it is unavailable.

## Reference

Layout reference: https://cellphones.com.vn/man-hinh.html

Xiaomi detail reference: https://cellphones.com.vn/man-hinh-xiaomi-a27qi-2026-27-inch.html

This is a local recreation for the requested project, not the official retailer website. Images and product data are for this demonstration and can be replaced in your own catalog.

## Trang PC (`pc.php`)

`pc.php` là phiên bản máy tính để bàn của `monitor.php`: cùng bố cục, bộ lọc, sắp xếp, thẻ sản phẩm, trang chi tiết (`product.php`), yêu thích và giỏ hàng. Chỉ khác ảnh và nội dung mô tả; bộ lọc dùng CPU, RAM, ổ cứng và khoảng giá của PC.

- Ảnh nằm trong thư mục `img/` (`pc1_png.webp` ... `pc5_png.webp`). Đường dẫn ảnh trong MySQL là `img/pc1_png.webp`, v.v.
- Nhập `database/pc-products.sql` bằng phpMyAdmin (chọn cơ sở dữ liệu `cellphones_shop` → Import) để thêm 5 PC và xóa các PC mẫu cũ. `database/homepage.sql` đã dùng cùng 5 PC.
- Giá, tồn kho, đánh giá và `usage_type` là dữ liệu mẫu, có thể sửa trong bảng `products`.
- Menu **Danh mục → PC** (`config/categories.php`), lối tắt ở trang chủ và chân trang đều mở `pc.php`.
