# Cửa hàng công nghệ lấy cảm hứng từ CellphoneS — PHP + MySQL

Khi sao chép hoặc triển khai dự án, giữ cả tệp ẩn `.htaccess`: Apache cần tệp này để xử lý các URL `.html` và chặn truy cập thư mục nội bộ, SQL và Git. Báo cáo QA và cách chạy kiểm thử bổ sung nằm tại `tests/QA-REPORT.md`. Script `database/pc-products.sql` chỉ bổ sung sản phẩm còn thiếu, không tự xóa các PC mẫu đã được chỉnh sửa.

Một website bán hàng bằng tiếng Việt với trang chủ, danh mục màn hình, trang chi tiết sản phẩm và giỏ hàng sử dụng Session.

Trang chủ hiển thị ba hàng sản phẩm có thể cuộn ngang theo thứ tự: **Màn hình → Máy tính → Laptop**.

Tên sản phẩm, giá bán, số lượng tồn kho, mô tả và hình ảnh được lấy từ MySQL.

Website không có chức năng thanh toán, thu tiền, đăng nhập tài khoản hoặc xử lý đơn hàng. Danh mục sản phẩm đi kèm là dữ liệu mẫu có thể chỉnh sửa.

## Chạy bằng XAMPP

1. Đặt thư mục dự án tại `C:\xampp\htdocs\cellphones-shop`.
2. Khởi động Apache và MySQL trong XAMPP Control Panel.
3. Khởi tạo cơ sở dữ liệu bằng lệnh bên dưới. Lệnh này tạo cơ sở dữ liệu `cellphones_shop`, thêm 12 màn hình, 5 máy tính PC mẫu và 5 laptop mẫu. Chạy lại lệnh cài đặt sẽ giữ nguyên những chỉnh sửa sản phẩm hiện có.
4. Mở http://localhost/cellphones-shop/ để truy cập trang chủ.
5. Truy cập http://localhost/cellphones-shop/monitor.php để xem danh mục màn hình, bao gồm chức năng tìm kiếm và bộ lọc chỉ dành cho màn hình.

Khởi tạo bằng PowerShell sau khi MySQL đã được bật:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\scripts\setup.php'
```

Đối với bản cài đặt đã tồn tại, nếu chỉ muốn nâng cấp dữ liệu và các trường của trang chủ, chạy:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\scripts\setup-homepage.php'
```

Lệnh này bổ sung các trường danh mục và thông số kỹ thuật máy tính, giữ nguyên sản phẩm hiện có và thêm các sản phẩm mẫu còn thiếu.

Bạn cũng có thể nhập `database/schema.sql` thông qua phpMyAdmin để cài đặt mới, sau đó chạy `scripts/setup-homepage.php`.

Tệp `database/homepage.sql` chỉ chứa dữ liệu mẫu và yêu cầu các trường dữ liệu mới phải tồn tại trước. Đây không phải là tệp nâng cấp cơ sở dữ liệu độc lập.

Các thay đổi về cấu trúc cơ sở dữ liệu không được thực hiện trong quá trình người dùng truy cập trang web.

Script cài đặt chỉ chạy từ dòng lệnh.

**Yêu cầu hệ thống:**

- PHP 8.1 trở lên, có bật `pdo_mysql` và `mbstring`.
- MySQL 5.7 trở lên hoặc MariaDB 10.4 trở lên.
- Không cần Composer hoặc Node.js.

## Kết nối cơ sở dữ liệu

Chỉnh sửa `config/database.php` nếu cấu hình MySQL của bạn khác với cấu hình mặc định của XAMPP:

| Thông số | Giá trị |
|---|---|
| Host | 127.0.0.1 |
| Port | 3306 |
| Database | cellphones_shop |
| Username | root |
| Password | Để trống (mặc định của XAMPP trên máy cục bộ) |

Các giá trị này cũng có thể được ghi đè bằng các biến môi trường `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` và `DB_PASSWORD`.

Nếu triển khai trên hosting PHP riêng, hãy sử dụng tài khoản cơ sở dữ liệu chuyên dụng và cập nhật cấu hình kết nối cho máy chủ đó.

## Thêm hoặc cập nhật sản phẩm trong MySQL

Mở **phpMyAdmin → cellphones_shop → products → Insert**.

Nhập slug duy nhất, tên sản phẩm, thương hiệu, giá bán, đường dẫn hình ảnh, thông số kỹ thuật và số lượng tồn kho.

Đặt trường `category` thành `monitor`, `pc` hoặc `laptop` để chọn hàng sản phẩm tương ứng trên trang chủ.

Lưu dữ liệu và tải lại trang chủ.

Mỗi sản phẩm mới sẽ tự động có trang chi tiết riêng và chức năng thêm vào giỏ hàng mà không cần chỉnh sửa PHP.

Các sản phẩm có `category = monitor` cũng xuất hiện trong danh mục màn hình tại `monitor.php`.

Để tham khảo ví dụ SQL có thể sao chép và chỉnh sửa, xem `database/add-product-example.sql`.

Giá bán được lưu dưới dạng số nguyên, đơn vị Việt Nam đồng (`3790000` = `3.790.000đ`).

Đường dẫn hình ảnh được tính tương đối từ thư mục dự án, ví dụ `assets/images/xiaomi-a27qi.webp`.

Đặt hình ảnh của bạn trong thư mục `assets/images/`.

**Các giá trị được đề xuất cho bộ lọc:**

| Trường | Giá trị ví dụ |
|---|---|
| category | monitor = Màn hình (mặc định), pc = Máy tính, laptop = Laptop |
| cpu | Intel Core i5 (PC/laptop) |
| ram | 16GB (PC/laptop) |
| storage | 512GB SSD (PC/laptop) |
| gpu | Tên card đồ họa hoặc đồ họa tích hợp (PC/laptop) |
| usage_type | Gaming, Văn phòng, Đồ họa, Lập trình |
| resolution | Full HD, 2K, 4K |
| panel | IPS, VA, OLED |
| screen_size | 24, 25, 27, 32 |
| refresh_rate | 100, 120, 144, 165 (đơn vị Hz) |
| stock | Số lượng hiện có; 0 nghĩa là hết hàng |
| featured | 1 để ưu tiên sản phẩm khi sắp xếp theo đề xuất |

Đối với PC, để trống các trường thông tin màn hình dạng văn bản và sử dụng `0` cho kích thước màn hình và tần số quét. Trang chi tiết sẽ hiển thị CPU, RAM, dung lượng lưu trữ và card đồ họa thay thế.

Laptop có thể bao gồm cả thông số máy tính và thông số màn hình.

Chức năng tìm kiếm trên trang chủ áp dụng cho cả ba danh mục. Chức năng tìm kiếm trong `monitor.php` chỉ áp dụng cho màn hình.

Năm PC và năm laptop được thêm vào trang chủ là sản phẩm mẫu.

Giá bán, cấu hình, số lượng tồn kho và mô tả của chúng cần được thay thế bằng thông tin danh mục sản phẩm thực tế trước khi sử dụng để bán hàng.

Slug của các sản phẩm mẫu này kết thúc bằng `-demo`, giúp dễ dàng tìm thấy chúng trong phpMyAdmin.

Dữ liệu màn hình hiện có được giữ nguyên.

## Menu Danh mục — Liên kết các website

Nút **Danh mục** trên thanh đầu trang mở ra ba mục riêng biệt: **Màn hình, PC và Laptop**, mỗi mục có biểu tượng tương ứng.

Menu được sử dụng chung cho trang chủ, trang danh sách sản phẩm, trang chi tiết và giỏ hàng.

Menu hỗ trợ thiết bị di động, có thể đóng bằng cách nhấn ra bên ngoài hoặc nhấn phím `Escape`.

Chỉnh sửa URL tại `config/categories.php` khi các website khác đã sẵn sàng.

Ví dụ:

```php
['category' => 'pc', 'name' => 'PC', 'icon' => 'tower', 'url' => 'https://example.com/pc/'],
['category' => 'laptop', 'name' => 'Laptop', 'icon' => 'laptop', 'url' => '/laptop/'],
```

Đường dẫn bắt đầu bằng `/` được tính từ thư mục gốc của máy chủ.

URL đầy đủ sẽ chuyển hướng đến website tương ứng.

Đường dẫn tương đối được tính từ thư mục dự án hiện tại.

Hiện tại, URL của PC và Laptop được để trống (`''`) để cấu hình sau.

Trong menu đầu trang, mỗi mục chỉ hiển thị tên và biểu tượng.

Các liên kết tắt trên trang chủ và chân trang tạm thời dẫn đến hàng sản phẩm tương ứng trên trang chủ.

Khi điền URL, các liên kết danh mục sẽ sử dụng URL mới.

Các thẻ sản phẩm vẫn mở trang chi tiết chung tại `product.php`.

Không có website danh mục PC/Laptop riêng nào được tạo thêm.

## Hoạt động của giỏ hàng

Khi thêm sản phẩm, hệ thống hiển thị thông báo:

**Đã thêm vào giỏ hàng (tên sản phẩm).**

Giỏ hàng vẫn được lưu giữ khi tải lại trang trong cùng một phiên trình duyệt.

Nếu thêm lại cùng một sản phẩm, số lượng của sản phẩm đó sẽ tăng lên.

Người dùng có thể thay đổi số lượng và xóa sản phẩm tại trang giỏ hàng.

Giá bán và số lượng tồn kho được đọc trực tiếp trên máy chủ. Hệ thống không sử dụng giá do phía trình duyệt gửi lên.

Giỏ hàng cho phép tối đa 99 đơn vị cho mỗi sản phẩm và không bao giờ vượt quá số lượng tồn kho của sản phẩm đó.

Số lượng tồn kho không được giữ chỗ hoặc trừ đi vì dự án không có chức năng thanh toán.

API `POST api/cart.php` nhận dữ liệu JSON với các trường:

- `action`: `add`, `update`, `remove`.
- `product_id`: ID sản phẩm.
- `quantity`: Số lượng.
- `csrf_token`: Mã xác thực CSRF.

Phản hồi thành công bao gồm:

- `success`: Trạng thái thành công.
- `message`: Thông báo.
- `count`: Tổng số lượng sản phẩm.
- `total`: Tổng tiền.
- `items`: Danh sách sản phẩm trong giỏ hàng.

Các mã trạng thái lỗi:

| Mã HTTP | Ý nghĩa |
|---|---|
| 422 | Dữ liệu đầu vào không hợp lệ |
| 403 | CSRF không hợp lệ |
| 405 | Phương thức HTTP không được hỗ trợ |
| 503 | MySQL không khả dụng |

## Các tệp trong dự án

| Tệp / Thư mục | Chức năng |
|---|---|
| `index.php` | Trang chủ với ba hàng danh mục có thể cuộn ngang và chức năng tìm kiếm toàn bộ sản phẩm |
| `monitor.php` | Danh mục màn hình và bộ lọc |
| `product.php` | Trang chi tiết sản phẩm lấy dữ liệu từ cơ sở dữ liệu |
| `cart.php` | Giỏ hàng |
| `includes/` | Các thành phần giao diện, danh mục và hàm hỗ trợ Session dùng chung |
| `api/cart.php` | Xử lý thay đổi giỏ hàng có kiểm tra tính hợp lệ |
| `config/database.php` | Cấu hình kết nối PDO |
| `config/categories.php` | Cấu hình đường dẫn danh mục |
| `database/schema.sql` | Cấu trúc cơ sở dữ liệu và danh mục sản phẩm mẫu |
| `database/homepage.sql` | Dữ liệu PC và laptop mẫu |
| `database/add-product-example.sql` | Mẫu thêm sản phẩm |
| `scripts/setup.php` | Khởi tạo cơ sở dữ liệu bằng dòng lệnh |
| `scripts/setup-homepage.php` | Nâng cấp cơ sở dữ liệu hiện có, có thể chạy lại nhiều lần |
| `assets/` | CSS, JavaScript và hình ảnh sản phẩm |

## Kiểm tra cài đặt

Các bài kiểm thử tích hợp bao gồm:

- Kiểm tra các trang PHP đang hoạt động.
- Kiểm tra thêm sản phẩm vào MySQL.
- Kiểm tra xử lý ký tự HTML an toàn.
- Kiểm tra tính độc lập giữa các Session.
- Kiểm tra số lượng tồn kho.
- Kiểm tra tổng tiền giỏ hàng.
- Kiểm tra các danh mục trên trang chủ.
- Kiểm tra điều hướng đến danh mục màn hình.
- Kiểm tra hiển thị sản phẩm.

Những sản phẩm tạm thời được thêm vào trong quá trình kiểm thử sẽ được xóa sau khi hoàn thành.

Các bài kiểm thử giỏ hàng sử dụng Session riêng.

Chạy các lệnh sau:

```powershell
& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\tests\smoke.php' 'http://localhost/cellphones-shop/'

& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\tests\homepage.php' 'http://localhost/cellphones-shop/'

& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\tests\regressions.php' 'http://localhost/cellphones-shop/'

& 'C:\xampp\php\php.exe' 'C:\xampp\htdocs\cellphones-shop\scripts\verify-assets.php' 'http://localhost/cellphones-shop/'
```

Các bài kiểm thử thông thường không thêm lại dữ liệu mẫu vào danh mục sản phẩm.

Để kiểm tra khả năng chạy nâng cấp nhiều lần mà không tạo dữ liệu trùng lặp, thêm tham số `--check-migration` vào `tests/homepage.php`.

Chỉ chạy kiểm tra này trên cơ sở dữ liệu thử nghiệm có thể xóa bỏ, vì quá trình cài đặt có thể khôi phục các sản phẩm mẫu đã bị xóa.

Apache cũng hỗ trợ các URL theo phong cách website tham khảo:

- `/cellphones-shop/man-hinh.html`
- `/cellphones-shop/man-hinh-xiaomi-a27qi-2026-27-inch.html`
- `/cellphones-shop/gio-hang.html`

Các liên kết `.php` thông thường vẫn hoạt động mà không cần cấu hình viết lại URL.

Hình ảnh sản phẩm đã được lưu sẵn trong dự án.

Script tùy chọn `scripts/download-assets.ps1` dùng để tải lại hình ảnh màn hình.

Script `scripts/download-home-assets.ps1` dùng để tải hình ảnh PC và laptop.

Chỉ sử dụng tham số `-Force` khi muốn thay thế những hình ảnh đã có.

Thông tin ghi nhận nguồn hình ảnh trang chủ được lưu tại `assets/images/home/SOURCES.md`.

Các sản phẩm riêng có thể sử dụng bất kỳ định dạng hình ảnh nào được trình duyệt hỗ trợ.

Google Fonts là tùy chọn. Nếu không thể tải bảng định kiểu Google Fonts, website sẽ sử dụng font Arial thay thế.

## Tham khảo

**Giao diện tham khảo:**

https://cellphones.com.vn/man-hinh.html

**Trang chi tiết Xiaomi tham khảo:**

https://cellphones.com.vn/man-hinh-xiaomi-a27qi-2026-27-inch.html

Đây là phiên bản tái tạo cục bộ phục vụ dự án được yêu cầu, không phải website chính thức của nhà bán lẻ CellphoneS.

Hình ảnh và dữ liệu sản phẩm được sử dụng cho mục đích minh họa và có thể thay thế bằng danh mục sản phẩm riêng của bạn.


## Triển khai online bằng Render Free

Xem [hướng dẫn Docker + MySQL online](DEPLOY_RENDER.md). Repo có sẵn `Dockerfile`, `render.yaml` với gói Free, TLS cho MySQL và script khởi tạo dữ liệu. Bạn tự nhập thông tin MySQL tại Render Environment; không cần bật XAMPP và chưa cần mua tên miền. Render cấp địa chỉ `onrender.com`.

## Danh mục PC

Trang máy tính để bàn nằm tại `pc.php` và được nối từ menu danh mục. Bộ lọc CPU, RAM, ổ cứng và khoảng giá dùng chung bảng `products`; trang màn hình vẫn dùng `monitor.php`. Dữ liệu cài đặt mới gồm 5 PC từ nhánh NgoHuyBinh và 5 laptop mẫu. Khi nâng cấp database có sẵn, chạy `scripts/setup-homepage.php` để thêm các PC mới mà không xóa sản phẩm đã chỉnh sửa.
