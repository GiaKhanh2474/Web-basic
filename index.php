<?php
// Kết nối cơ sở dữ liệu
require_once __DIR__ . '/config.php';

// Lấy từ khóa tìm kiếm
$keyword = trim($_GET['search'] ?? '');

// Lấy thương hiệu được chọn
$brand = trim($_GET['brand'] ?? '');

try {
    // Lấy danh sách thương hiệu
    $brandQuery = $conn->query(
        "SELECT DISTINCT brand
         FROM laptopstore
         WHERE brand IS NOT NULL AND brand != ''
         ORDER BY brand ASC"
    );

    $brands = $brandQuery->fetchAll(PDO::FETCH_COLUMN);

    // Truy vấn sản phẩm
    $sql = "SELECT * FROM laptopstore WHERE 1=1";
    $params = [];

    if ($keyword !== '') {
        $sql .= " AND (name LIKE :keyword OR brand LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }

    if ($brand !== '') {
        $sql .= " AND brand = :brand";
        $params[':brand'] = $brand;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}

// Hàm xử lý hình ảnh
function getProductImage($image) {
    if (empty($image)) {
        return '';
    }

    // Nếu là đường dẫn ảnh trực tuyến
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    // Nếu đã có đường dẫn assets/images
    if (strpos($image, 'assets/images/') === 0) {
        return $image;
    }

    // Nếu chỉ có tên file ảnh
    return 'assets/images/' . $image;
}

// Hàm bảo vệ dữ liệu hiển thị
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CellphoneS - Laptop chính hãng</title>
    <link rel="stylesheet" href="assets/index_style.css">
    <link rel="stylesheet" href="assets/header_style.css">
    <link rel="stylesheet" href="assets/footer_style.css">
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

<!-- NOI DUNG CHINH -->
<main class="container">

    <div class="breadcrumb">
        Trang chủ / Laptop
    </div>

    <!-- DANH MUC -->
    <nav class="category-menu">
        <a href="index.php" class="active">Laptop</a>
        <a href="#">PC</a>
        <a href="#">Màn hình</a>
    </nav>

    <!-- BANNER -->
    <div class="banner-area">
        <div class="banner banner-one">
            <h2>DEAL LAPTOP SIÊU HỜI</h2>
            <p>Laptop chính hãng - Giá cực tốt</p>
            <p>Ưu đãi hấp dẫn mỗi ngày!</p>
        </div>

        <div class="banner banner-two">
            <h2>LAPTOP GAMING</h2>
            <p>Hiệu năng mạnh mẽ - Chiến game cực đỉnh</p>
            <p>Khám phá ngay hôm nay!</p>
        </div>
    </div>

    <!-- THUONG HIEU -->
    <section class="section">
        <h2 class="section-title">Máy tính laptop</h2>

        <div class="brand-list">

            <a href="index.php" class="brand-item">
                Tất cả
            </a>

            <?php foreach ($brands as $item): ?>
                <a
                    href="index.php?brand=<?= urlencode($item) ?>"
                    class="brand-item <?= $brand === $item ? 'selected' : '' ?>"
                >
                    <?= e($item) ?>
                </a>
            <?php endforeach; ?>

        </div>
    </section>

    <!-- UU DAI -->
    <section class="section">
        <h3 class="section-title">Ưu đãi & voucher</h3>

        <div class="voucher-list">
            <div class="voucher">
                <strong>GIẢM ĐẾN 5 TRIỆU</strong>
                <p>Ưu đãi mua laptop chính hãng.</p>
            </div>

            <div class="voucher">
                <strong>GIẢM ĐẾN 3 TRIỆU</strong>
                <p>Ưu đãi dành cho học sinh, sinh viên.</p>
            </div>

            <div class="voucher">
                <strong>ƯU ĐÃI ĐẶC BIỆT</strong>
                <p>Khuyến mãi mua laptop hôm nay.</p>
            </div>

            <div class="voucher">
                <strong>TRẢ GÓP 0%</strong>
                <p>Mua laptop dễ dàng, tiết kiệm.</p>
            </div>
        </div>
    </section>

    <!-- CHON THEO NHU CAU -->
    <section class="section">
        <h2 class="section-title">Chọn theo nhu cầu</h2>

        <div class="demand-list">

            <a href="index.php?search=Văn phòng" class="demand-item">
                Văn phòng
            </a>

            <a href="index.php?search=Gaming" class="demand-item">
                Gaming
            </a>

            <a href="index.php?search=MacBook" class="demand-item">
                Mỏng nhẹ
            </a>

            <a href="index.php?search=Gaming" class="demand-item">
                Đồ họa - Kỹ thuật
            </a>

            <a href="index.php?search=Lenovo" class="demand-item">
                Sinh viên
            </a>

            <a href="index.php?search=HP" class="demand-item">
                Cảm ứng
            </a>

            <a href="index.php?search=AI" class="demand-item">
                Laptop AI
            </a>

        </div>
    </section>

    <!-- DANH SACH SAN PHAM -->
    <section class="section">

        <div class="product-header">

            <div>
                <h2 class="section-title">
                    <?= $keyword !== '' ? 'Kết quả tìm kiếm' : 'Laptop nổi bật' ?>
                </h2>

                <p class="product-count">
                    Tìm thấy <?= count($products) ?> sản phẩm
                    <?= $brand !== '' ? ' - Thương hiệu: ' . e($brand) : '' ?>
                </p>
            </div>

        </div>

        <div class="product-grid">

            <?php if (count($products) > 0): ?>

                <?php foreach ($products as $product): ?>

                    <div class="product-card">

                        <a href="product_detail.php?id=<?= (int)($product['Id'] ?? 0) ?>">

                        <div class="product-image">
                            <?php
                            $imagePath = getProductImage($product['image'] ?? '');
                            ?>
                            <style>
                                .product-image {
                                    width: 100%;
                                    height: 190px;
                                    object-fit: contain;
                                    display: block;
                                    margin: 0 auto;
                                }
                            </style>

                            <?php if ($imagePath !== ''): ?>
                            <img
                            src="<?= e($imagePath) ?>"
                            alt="<?= e($product['name']) ?>"
                            class="product-image"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >
                            <img 
                            src="<?= htmlspecialchars($product['image']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="product-image"
                            >
                            <span class="no-image" style="display:none;">💻</span>
                            <?php else: ?>
                                <span class="no-image">💻</span>
                                <?php endif; ?>
                        </div>


                            <h3 class="product-name">
                                <?= e($product['name']) ?>
                            </h3>

                        </a>

                        <p class="product-brand">
                            Thương hiệu: <?= e($product['brand']) ?>
                        </p>

                        <p class="product-price">
                            <?= number_format((float)$product['price'], 0, ',', '.') ?>đ
                        </p>

                        <p class="product-stock">
                            Còn hàng: <?= (int)$product['quantity'] ?>
                        </p>

                        <a
                        href="product_detail.php?id=<?= (int)$product['Id'] ?>"
                        class="detail-button">
                            Xem chi tiết
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="empty-message">
                    <h3>Không tìm thấy sản phẩm!</h3>
                    <p>Vui lòng thử từ khóa hoặc thương hiệu khác.</p>
                    <br>
                    <a href="index.php" class="detail-button">
                        Quay lại trang chủ
                    </a>
                </div>

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

</body>
</html>