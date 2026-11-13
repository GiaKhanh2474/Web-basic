<?php
session_start();

require_once __DIR__ . '/config.php';

// Khởi tạo giỏ hàng
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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

    if (strpos($image, 'assets/images/') === 0) {
        return $image;
    }

    return 'assets/images/' . $image;
}

// Tạo CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// Xử lý các thao tác giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {

        $error = 'Yêu cầu không hợp lệ. Vui lòng thử lại.';

    } else {

        $action = $_POST['action'] ?? '';

        // Xóa một sản phẩm
        if ($action === 'remove') {

            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($id && isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
                $message = 'Đã xóa sản phẩm khỏi giỏ hàng!';
            }

        }

        // Xóa toàn bộ giỏ hàng
        elseif ($action === 'clear') {

            $_SESSION['cart'] = [];
            $message = 'Đã xóa toàn bộ giỏ hàng!';

        }

        // Cập nhật số lượng
        elseif ($action === 'update') {

            $quantities = $_POST['quantities'] ?? [];

            if (!is_array($quantities)) {
                $error = 'Dữ liệu số lượng không hợp lệ.';
            } else {

                $validIds = [];

                foreach ($_SESSION['cart'] as $id => $qty) {
                    $validIds[] = (int)$id;
                }

                // Lấy tồn kho hiện tại từ MySQL
                $stockMap = [];

                if (!empty($validIds)) {

                    $placeholders = implode(
                        ',',
                        array_fill(0, count($validIds), '?')
                    );

                    $stockStmt = $conn->prepare(
                        "SELECT id, quantity
                         FROM laptopstore
                         WHERE id IN ($placeholders)"
                    );

                    $stockStmt->execute($validIds);

                    while ($row = $stockStmt->fetch(PDO::FETCH_ASSOC)) {
                        $stockMap[(int)$row['id']] = (int)$row['quantity'];
                    }
                }

                foreach ($_SESSION['cart'] as $id => $oldQty) {

                    $id = (int)$id;

                    if (!isset($quantities[$id])) {
                        continue;
                    }

                    $newQty = filter_var(
                        $quantities[$id],
                        FILTER_VALIDATE_INT
                    );

                    if ($newQty === false || $newQty < 1) {
                        $error = 'Số lượng phải lớn hơn hoặc bằng 1.';
                        break;
                    }

                    if (!isset($stockMap[$id])) {
                        $error = 'Có sản phẩm không còn tồn tại trong kho.';
                        break;
                    }

                    if ($newQty > $stockMap[$id]) {
                        $error = 'Số lượng vượt quá tồn kho của sản phẩm ID ' . $id . '.';
                        break;
                    }
                }

                if ($error === '') {

                    foreach ($_SESSION['cart'] as $id => $oldQty) {

                        $id = (int)$id;

                        if (isset($quantities[$id])) {
                            $_SESSION['cart'][$id] =
                                (int)$quantities[$id];
                        }
                    }

                    $message = 'Cập nhật giỏ hàng thành công!';
                }
            }
        }
    }

    // Chuyển hướng để tránh gửi lại biểu mẫu
    $_SESSION['cart_message'] = $message;
    $_SESSION['cart_error'] = $error;

    header('Location: cart.php');
    exit;
}

// Lấy thông báo sau chuyển hướng
$message = $_SESSION['cart_message'] ?? '';
$error = $_SESSION['cart_error'] ?? '';

unset(
    $_SESSION['cart_message'],
    $_SESSION['cart_error']
);

// Lấy danh sách ID trong giỏ hàng
$cart = $_SESSION['cart'];
$cartProducts = [];

$totalPrice = 0;
$totalQuantity = 0;

if (!empty($cart)) {

    $ids = array_map('intval', array_keys($cart));

    $placeholders = implode(
        ',',
        array_fill(0, count($ids), '?')
    );

    $stmt = $conn->prepare(
        "SELECT * FROM laptopstore
         WHERE id IN ($placeholders)"
    );

    $stmt->execute($ids);

    $cartProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Đồng bộ giỏ hàng nếu sản phẩm đã bị xóa khỏi database
    $foundIds = [];

    foreach ($cartProducts as $product) {
        $foundIds[] = (int)$product['Id'];
    }

    foreach ($ids as $id) {
        if (!in_array($id, $foundIds, true)) {
            unset($_SESSION['cart'][$id]);
        }
    }

    $cart = $_SESSION['cart'];
}

// Tính tổng tiền
foreach ($cartProducts as $product) {

    $id = (int)$product['Id'];

    $quantity = (int)($cart[$id] ?? 0);
    $price = (float)$product['price'];

    $totalQuantity += $quantity;
    $totalPrice += $price * $quantity;
}

// Tổng số lượng sản phẩm trong giỏ
$cartCount = array_sum($_SESSION['cart']);

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Giỏ hàng - CellphoneS</title>
    <link rel="stylesheet" href="assets/cart_style.css">
    <link rel="stylesheet" href="assets/header_style.css">

</head>

<body>
<!-- HEADER -->

<div class="top-banner">
    <div>Sản phẩm chính hãng <span>·</span> An tâm lựa chọn</div> 
    <div>Nâng tầm góc máy <span>·</span> Trọn vẹn trải nghiệm</div>
    <div>Màn hình tốt <span>·</span> Giá thật hời</div>
</div>

<header class="header">
    <div class="header-container">

        <a href="index.php" class="logo">
            cellphones<span>S</span>
        </a>

        <button type="button" class="header-button category-button">
            <span class="menu-icon">☷</span>
            Danh mục
            <span>⌄</span>
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
        / Giỏ hàng
    </div>

    <h1 class="page-title">
        Giỏ hàng của bạn
    </h1>

    <?php if ($message !== ''): ?>

        <div class="message success">
            <?= e($message) ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="message error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <?php if (!empty($cartProducts)): ?>

        <form method="POST" action="cart.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="update"
            >

            <div class="cart-wrapper">

                <!-- DANH SACH SAN PHAM -->

                <section class="cart-list">

                    <h2 class="cart-heading">
                        Sản phẩm trong giỏ (<?= $cartCount ?>)
                    </h2>

                    <?php foreach ($cartProducts as $product): ?>

                        <?php

                        $id = (int)$product['Id'];

                        $quantity = (int)($cart[$id] ?? 0);

                        $price = (float)$product['price'];

                        $subtotal = $price * $quantity;

                        $imagePath = getProductImage(
                            $product['image'] ?? ''
                        );

                        ?>

                        <div class="cart-item">

                            <!-- ANH SAN PHAM -->

                            <a
                                href="product_detail.php?id=<?= $id ?>"
                                class="cart-image"
                            >

                                <?php if ($imagePath !== ''): ?>

                                    <img
                                        src="<?= e($imagePath) ?>"
                                        alt="<?= e($product['name']) ?>"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                    >

                                    <span
                                        class="no-image"
                                        style="display:none"
                                    >
                                        💻
                                    </span>

                                <?php else: ?>

                                    <span class="no-image">💻</span>

                                <?php endif; ?>

                            </a>

                            <!-- THONG TIN -->

                            <div>

                                <a
                                    href="product_detail.php?id=<?= $id ?>"
                                    class="item-name"
                                >
                                    <?= e($product['name']) ?>
                                </a>

                                <p class="item-brand">
                                    Thương hiệu:
                                    <?= e($product['brand']) ?>
                                </p>

                                <p class="item-price">
                                    <?= number_format(
                                        $price,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>đ
                                </p>

                                <div class="item-bottom">

                                    <div>

                                        <label for="quantity<?= $id ?>">
                                            Số lượng:
                                        </label>

                                        <input
                                            class="quantity-input"
                                            type="number"
                                            id="quantity<?= $id ?>"
                                            name="quantities[<?= $id ?>]"
                                            value="<?= $quantity ?>"
                                            min="1"
                                            max="<?= (int)$product['quantity'] ?>"
                                            required
                                        >

                                    </div>

                                    <strong>
                                        Thành tiền:
                                        <?= number_format(
                                            $subtotal,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>đ
                                    </strong>

                                    <!-- NUT XOA -->

                                    <button
                                        type="button"
                                        class="remove-button"
                                        onclick="removeProduct(<?= $id ?>)"
                                    >
                                        Xóa
                                    </button>

                                </div>

                                <small style="color:#777">
                                    Tồn kho: <?= (int)$product['quantity'] ?>
                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                    <!-- CAC NUT THAO TAC -->

                    <div class="cart-actions">

                        <a href="index.php" class="btn btn-home">
                            ← Tiếp tục mua sắm
                        </a>

                        <button
                            type="submit"
                            class="btn btn-update"
                        >
                            Cập nhật giỏ hàng
                        </button>

                    </div>

                </section>

                <!-- TONG TIEN -->

                <aside class="cart-summary">

                    <h3>Thông tin đơn hàng</h3>

                    <div class="summary-row">

                        <span>Số lượng sản phẩm</span>

                        <strong><?= $totalQuantity ?></strong>

                    </div>

                    <div class="summary-row">

                        <span>Tạm tính</span>

                        <strong>
                            <?= number_format(
                                $totalPrice,
                                0,
                                ',',
                                '.'
                            ) ?>đ
                        </strong>

                    </div>

                    <div class="summary-row">

                        <span>Phí vận chuyển</span>

                        <strong>Chưa tính</strong>

                    </div>

                    <div class="summary-row summary-total">

                        <span>Tổng tiền</span>

                        <strong>
                            <?= number_format(
                                $totalPrice,
                                0,
                                ',',
                                '.'
                            ) ?>đ
                        </strong>

                    </div>

                    <div class="checkout-note">

                        Đây là giỏ hàng thử nghiệm của website.

                        <br>

                        Chưa có chức năng đặt hàng hoặc thanh toán trực tuyến.

                    </div>

                    <br>

                    <button
                        type="button"
                        class="btn btn-clear"
                        style="width:100%"
                        onclick="clearCart()"
                    >
                        Xóa toàn bộ giỏ hàng
                    </button>

                </aside>

            </div>

        </form>

        <!-- FORM XOA SAN PHAM -->

        <form
            method="POST"
            action="cart.php"
            id="removeForm"
            style="display:none"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="remove"
            >

            <input
                type="hidden"
                name="id"
                id="removeId"
            >

        </form>

        <!-- FORM XOA TAT CA -->

        <form
            method="POST"
            action="cart.php"
            id="clearForm"
            style="display:none"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="clear"
            >

        </form>

    <?php else: ?>

        <!-- GIO HANG TRONG -->

        <div class="empty-cart">

            <h2>Giỏ hàng đang trống!</h2>

            <p>
                Bạn chưa thêm sản phẩm laptop nào vào giỏ hàng.
            </p>

            <a href="index.php" class="btn btn-home">
                Khám phá sản phẩm
            </a>

        </div>

    <?php endif; ?>

</main>

<!-- FOOTER -->

<footer class="footer">

    <h2>CELLPHONES</h2>

    <p>Hệ thống bán laptop chính hãng</p>

    <p>© 2026 CellphoneS - Website bán laptop</p>

</footer>

<!-- JAVASCRIPT -->
<script>

    // Xóa một sản phẩm
    function removeProduct(id) {

        const confirmDelete = confirm(
            'Bạn có chắc chắn muốn xóa sản phẩm này không?'
        );

        if (confirmDelete) {

            document.getElementById('removeId').value = id;

            document.getElementById('removeForm').submit();

        }

    }
    // Xóa toàn bộ giỏ hàng
    function clearCart() {

        const confirmClear = confirm(
            'Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng không?'
        );

        if (confirmClear) {

            document.getElementById('clearForm').submit();

        }
    }
</script>
</body>
</html>
