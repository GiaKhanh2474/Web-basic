# Chạy PHP bằng Docker trên Render Free

Repo có sẵn `Dockerfile` và `render.yaml`. Cấu hình chỉ tạo **một Web Service Free**, kết nối tới **MySQL online bạn đã có**; không tạo database trả phí trên Render. Không cần bật XAMPP để website online hoạt động.

## 1. Chuẩn bị MySQL online

- Trong trang quản lý database, tạo database `cellphones_shop` nếu chưa có, hoặc dùng tên database mà nhà cung cấp đã cấp.
- Lấy hostname, port, database name, username, password và yêu cầu TLS từ nhà cung cấp. `DB_HOST` là hostname database, không có `https://`, không dùng `localhost` và không phải tên miền website.
- Cho phép kết nối từ các **Outbound IP Addresses** của Web Service Render nếu MySQL có IP allowlist. Xem các dải IP tại trang dịch vụ Render rồi thêm vào nhà cung cấp MySQL.
- Tài khoản khởi tạo cần quyền tạo bảng, thêm cột/index và đọc/ghi dữ liệu trong database đã chọn. Không cần quyền `CREATE DATABASE` nếu database đã tồn tại.
- Backup database hiện có trước khi áp dụng thay đổi schema. Sản phẩm mẫu được thêm theo slug; sản phẩm đã sửa không bị ghi đè.

## 2. Tạo dịch vụ trên Render

Sau khi merge PR triển khai vào `main`:

1. Vào [Render Dashboard](https://dashboard.render.com), chọn **New → Blueprint**.
2. Kết nối repo `GiaKhanh2474/Web-basic`, chọn nhánh `main` và file `render.yaml`.
3. Điền `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` vào các ô Render yêu cầu.
4. Kiểm tra dịch vụ dùng **Free**. Blueprint không khai báo disk hoặc dịch vụ database nào.
5. Nếu database dùng tên/port khác mặc định, chỉnh `DB_DATABASE` / `DB_PORT` trong Environment rồi redeploy. Nếu lần triển khai đầu chưa kết nối được vì các giá trị này, sửa Environment rồi **Manual Deploy → Deploy latest commit**.

Nếu đã tạo Web Service trước đó: dùng repo/nhánh trên, chọn runtime **Docker**, Dockerfile Path `./Dockerfile`, Docker Build Context `.`, instance **Free**, Health Check Path `/health.php`. Để trống Docker Command cho image dùng lệnh khởi động có sẵn. Blueprint không tự cập nhật một Web Service tạo thủ công.

## 3. Environment cần điền

| Biến | Giá trị |
| --- | --- |
| `APP_ENV` | `production` |
| `DB_HOST` | Host MySQL online của bạn |
| `DB_PORT` | Port do nhà cung cấp cấp; mặc định `3306` |
| `DB_DATABASE` | Tên database đã tạo; mặc định `cellphones_shop` |
| `DB_USERNAME` | User MySQL của bạn |
| `DB_PASSWORD` | Mật khẩu MySQL; chỉ nhập trong Render |
| `DB_SSL_CA` | `/etc/ssl/certs/ca-certificates.crt` nếu chứng chỉ DB dùng CA công cộng |
| `DB_AUTO_SETUP` | `true` để tạo bảng và thêm 22 sản phẩm mẫu khi khởi động |

`PORT` do Render cấp; Docker tự cấu hình Apache theo biến này, mặc định `10000`.

Nếu nhà cung cấp cấp **CA riêng**, tải CA từ trang quản lý của họ, thêm Render **Secret File** tên `mysql-ca.pem`, và đặt `DB_SSL_CA=/etc/secrets/mysql-ca.pem`. Không tắt kiểm tra chứng chỉ TLS. Trong production, bỏ trống `DB_SSL_CA` sẽ dùng bộ CA hệ thống. MySQL online phải hỗ trợ TLS và cho phép kết nối từ Render.

`.env.example` chỉ là mẫu tham khảo. PHP trong dự án **không tự đọc file `.env`**; trên Render phải nhập biến trong Environment. Không commit mật khẩu hoặc file `.env` vào GitHub.

## 4. Tạo bảng và dữ liệu

Khi `DB_AUTO_SETUP=true`, container chạy:

```sh
php scripts/setup.php --existing-database
```

Lệnh sử dụng database đã tồn tại trong `DB_DATABASE`, tạo bảng nếu thiếu, thêm các cột danh mục còn thiếu và nạp sản phẩm mẫu. Chạy lại không xóa dữ liệu hoặc ghi đè các sản phẩm có cùng slug. Không khởi tạo database trong bước Docker build; thông tin bí mật không được đưa vào image.

Sau khi deploy thành công lần đầu, có thể đổi `DB_AUTO_SETUP=false` để những lần khởi động sau không chạy seed/DDL, đồng thời dùng user database chỉ có quyền đọc/ghi dữ liệu. Nếu bổ sung schema sau này, cần chạy migration thích hợp trước khi chạy bản mới.

Thêm sản phẩm bằng công cụ SQL của nhà cung cấp hoặc MySQL Workbench/DBeaver: thêm dòng vào bảng `products`, dùng `category` là `monitor`, `pc` hoặc `laptop`, và `slug` duy nhất. Ảnh nên dùng đường dẫn HTTPS công khai hoặc ảnh đã commit vào repo; các file tạo bên trong container Free không tồn tại bền vững. Xem ví dụ SQL và các trường sản phẩm trong README.

## 5. Địa chỉ website và kiểm tra

Render cấp một địa chỉ dạng `https://<ten-dich-vu>.onrender.com`; dùng **địa chỉ thực tế Render hiển thị** vì tên mong muốn có thể đã được dùng. Chưa mua domain nên chưa cấu hình `group1-cellphonesclone.com`. Khi mua sau này, thêm domain tại Settings → Custom Domains rồi cấu hình DNS theo Render.

Sau khi dịch vụ báo Live:

- Mở `/health.php`: phải trả HTTP 200 và `{"status":"ok"}`.
- Mở `/`, `/monitor.php`, `/pc.php` và một trang sản phẩm.
- Thêm sản phẩm, kiểm tra thông báo “Đã thêm vào giỏ hàng” và trang giỏ hàng.
- Nếu health check trả 503: kiểm tra tên database, port, credentials, CA, allowlist và Logs. Endpoint công khai không trả thông tin kết nối.

Render Free có thể ngủ sau 15 phút không hoạt động, nên lần mở tiếp theo có thể chậm. Dữ liệu sản phẩm vẫn ở MySQL online; giỏ hàng hiện dùng PHP session trên filesystem nên có thể mất khi container ngủ/khởi động lại hoặc redeploy. Chưa có thanh toán trong dự án.

## 6. Kiểm thử Docker

GitHub Actions `.github/workflows/docker.yml` build image PHP, khởi động MySQL 8.4 riêng, chạy kiểm thử trang chủ, sản phẩm, giỏ hàng, bảo vệ file, health check và khởi động lại container. CI dùng dữ liệu tạm và HTTP nội bộ (`APP_ENV=testing`); không sử dụng credentials của bạn. Kết nối TLS tới MySQL online vẫn cần được kiểm tra sau khi bạn nhập cấu hình thật trên Render.

Nếu có Docker trên máy:

```sh
docker build -t cellphones-shop .
docker run --rm -p 10000:10000 --env-file .env cellphones-shop
```

Tự tạo `.env` từ `.env.example` và điền thông tin của bạn. Để thử giỏ hàng trên `http://localhost:10000`, đặt `APP_ENV=development` trong file thử nghiệm; giữ `DB_SSL_CA` để kết nối MySQL online qua TLS. Trên Render HTTPS luôn dùng `APP_ENV=production`.

Tài liệu chính thức: [Render Docker](https://render.com/docs/docker), [giới hạn Free](https://render.com/docs/free), [Blueprint](https://render.com/docs/blueprint-spec), [Outbound IP](https://render.com/docs/outbound-ip-addresses), [Secret Files](https://render.com/docs/configure-environment-variables#secret-files), [Custom Domains](https://render.com/docs/custom-domains).
