<?php
session_start();

// Kết nối cơ sở dữ liệu
require_once __DIR__ . '/config.php';

// Kiểm tra ID sản phẩm
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    die("ID sản phẩm không hợp lệ! <a href='index.php'>Quay lại</a>");
}

// Lấy thông tin sản phẩm
try {
    $stmt = $conn->prepare(
        "SELECT * FROM laptopstore WHERE id = :id LIMIT 1"
    );

    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}

// Nếu không tìm thấy sản phẩm
if (!$product) {
    http_response_code(404);
    die("Không tìm thấy sản phẩm! <a href='index.php'>Quay lại trang chủ</a>");
}

// Hàm bảo vệ dữ liệu
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Xử lý hình ảnh
function getProductImage($image) {
    if (empty($image)) {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    if (strpos($image, 'images') === 0) {
        return $image;
    }

    return 'images' . $image;
}

$imagePath = getProductImage($product['image'] ?? '');

// Khởi tạo giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tạo mã bảo vệ form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Thông báo
$success = '';
$error = '';

// Xử lý thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['add_to_cart'])) {

    // Kiểm tra mã bảo vệ
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {

        $error = "Yêu cầu không hợp lệ. Vui lòng thử lại.";

    } else {

        $quantity = filter_var(
            $_POST['quantity'] ?? 1,
            FILTER_VALIDATE_INT
        );

        $stock = (int)$product['quantity'];
        $currentQuantity = (int)($_SESSION['cart'][$id] ?? 0);

        if ($quantity === false || $quantity < 1) {

            $error = "Số lượng sản phẩm không hợp lệ!";

        } elseif ($stock <= 0) {

            $error = "Sản phẩm hiện đã hết hàng!";

        } elseif ($currentQuantity + $quantity > $stock) {

            $error = "Số lượng vượt quá số sản phẩm còn trong kho!";

        } else {

            $_SESSION['cart'][$id] = $currentQuantity + $quantity;

            $success = "Đã thêm sản phẩm vào giỏ hàng thành công!";
        }
    }
}

// Số lượng trong giỏ
$cartCount = array_sum($_SESSION['cart']);

// Số lượng tối đa có thể thêm
$availableQuantity = max(
    0,
    (int)$product['quantity'] - (int)($_SESSION['cart'][$id] ?? 0)
);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product['name']) ?> - CellphoneS</title>
    <link rel="stylesheet" href="assets/header_style.css">
    <link rel="stylesheet" href="assets/product_detail_style.css">

</head>
<body>
<!-- HEADER -->
<header class="header">
    <div class="header-container">

        <a href="index.php" class="logo">
            cellphones<span>S</span>
        </a>

        <button type="button" class="header-button category-button">
            <span class="menu-icon">☷</span>
            Danh mục
            <span>v</span>
        </button>

        <form action="index.php" method="GET" class="search-box">
            <input
                type="text"
                name="search"
                placeholder="Bạn cần tìm sản phẩm gì?"
                value="<?= e($keyword ?? '') ?>"
            >
            <button type="submit">⌕</button>
        </form>

        <a href="#" class="header-action">
            <span>Góc làm việc<br>của bạn</span>
        </a>

        <a href="#" class="header-action">
            <span>Sản phẩm<br>yêu thích</span>
        </a>

        <a href="cart.php" class="header-action cart-button">
            <span>Giỏ hàng</span>
        </a>
    </div>
</header>
<!-- MAIN -->
<main class="container">

    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        /
        Laptop
        /
        <?= e($product['name']) ?>
    </div>

    <!-- THONG TIN SAN PHAM -->
    <div class="product-detail">

        <!-- HINH ANH -->
        <div class="product-image">

            <?php if ($imagePath !== ''): ?>

                <img
                    src="<?= e($imagePath) ?>"
                    alt="<?= e($product['name']) ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                >

                <span class="no-image" style="display:none">
                    💻
                </span>

            <?php else: ?>

                <span class="no-image">💻</span>

            <?php endif; ?>

        </div>

        <!-- THONG TIN -->
        <div class="product-info">

            <h1><?= e($product['name']) ?></h1>

            <p class="product-brand">
                Thương hiệu:
                <strong><?= e($product['brand']) ?></strong>
                |
                Mã sản phẩm: <?= (int)$product['Id'] ?>
            </p>

            <div class="product-price">

                <p class="label">Giá bán:</p>

                <p class="price">
                    <?= number_format(
                        (float)$product['price'],
                        0,
                        ',',
                        '.'
                    ) ?>đ
                </p>

            </div>

            <?php if ((int)$product['quantity'] > 0): ?>

                <p class="stock available">
                    ✔ Còn hàng:
                    <?= (int)$product['quantity'] ?> sản phẩm
                </p>

            <?php else: ?>

                <p class="stock unavailable">
                    ✖ Sản phẩm hiện đã hết hàng
                </p>

            <?php endif; ?>

            <!-- THONG BAO -->
            <?php if ($success !== ''): ?>

                <div class="message success">
                    <?= e($success) ?>
                    <br>
                    <a href="cart.php">
                    Xem giỏ hàng
                    </a>
                </div>

            <?php endif; ?>

            <?php if ($error !== ''): ?>

                <div class="message error">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <!-- QUYEN LOI -->
            <div class="benefits">

                <h3> · Quyền lợi khách hàng</h3>

                <p>✔ Sản phẩm được quản lý trong hệ thống</p>

                <p>✔ Thông tin sản phẩm rõ ràng</p>

                <p>✔ Hỗ trợ khách hàng tận tình</p>

                <p>✔ Giỏ hàng không đồng nghĩa với thanh toán</p>

            </div>

            <!-- THEM GIO HANG -->
            <form method="POST" action="product_detail.php?id=<?= (int)$product['Id'] ?>">

                <div class="quantity-area">

                    <label for="quantity">
                        Chọn số lượng:
                    </label>

                    <div class="quantity-control">

                        <button
                            type="button"
                            onclick="changeQuantity(-1)"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= $availableQuantity ?>"
                            required
                            <?= $availableQuantity <= 0 ? 'disabled' : '' ?>
                        >

                        <button
                            type="button"
                            onclick="changeQuantity(1)"
                        >
                            +
                        </button>

                    </div>

                    <small>
                        Có thể thêm tối đa <?= $availableQuantity ?> sản phẩm vào giỏ hàng.
                    </small>

                </div>

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <div class="button-group">

                    <button
                        type="submit"
                        name="add_to_cart"
                        class="btn btn-cart"
                        <?= $availableQuantity <= 0 ? 'disabled' : '' ?>
                    >
                        Thêm vào giỏ hàng
                    </button>

                    <a href="index.php" class="btn btn-home">
                        Tiếp tục mua sắm
                    </a>

                </div>

            </form>

        </div>

    </div>

    <!-- MO TA SAN PHAM -->
    <section class="description-section">

        <h2>Thông tin chi tiết sản phẩm</h2>

        <div class="description-content">

            <?php if (!empty($product['description'])): ?>

                <?= nl2br(e($product['description'])) ?>

            <?php else: ?>

                Hiện tại sản phẩm chưa có mô tả chi tiết.

            <?php endif; ?>

        </div>

    </section>

</main>

<!-- FOOTER -->
<footer class="footer">

    <h2>CELLPHONES</h2>

    <p>Hệ thống bán laptop chính hãng</p>

    <p>© 2026 CellphoneS - Website bán laptop</p>

</footer>

<!-- JAVASCRIPT -->

<script>
function changeQuantity(value) {

        const input = document.getElementById('quantity');

        if (input.disabled) return;

        const min = parseInt(input.min);
        const max = parseInt(input.max);

        let current = parseInt(input.value) || 1;

        current += value;

        if (current < min) {
            current = min;
        }

        if (current > max) {
            current = max;
        }

        input.value = current;
    }
</script>
</body>
</html>
