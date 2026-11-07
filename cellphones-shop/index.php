<?php
require_once __DIR__.'/includes/home-catalog.php';
require_once __DIR__.'/includes/cart.php';
$activePage = 'home';
$pageTitle = 'CellphoneS | Màn hình, máy tính & laptop cho góc máy của bạn';
$pageDescription = 'Khám phá màn hình, máy tính và laptop. Tìm thiết bị phù hợp cho công việc, học tập và giải trí.';
$searchAction = 'index.php';
$query = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$homeCategories = shop_categories();
$groupedProducts = array_fill_keys(array_keys($homeCategories), []);
$homeError = false;
try {
    foreach (products($query !== '' ? ['q' => $query] : []) as $item) {
        $type = category_from_product($item);
        if (array_key_exists($type, $groupedProducts)) $groupedProducts[$type][] = $item;
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $homeError = true;
    http_response_code(503);
}
$heroMonitor = $groupedProducts['monitor'][0] ?? null;
$heroComputer = $groupedProducts['pc'][0] ?? null;
$heroLaptop = $groupedProducts['laptop'][0] ?? null;
$extraStyles = ['assets/css/home.css'];
$extraScripts = ['assets/js/home.js'];
require __DIR__.'/includes/header.php';
?>
<main class="container home-page">
    <?php if ($query === ''): ?>
    <section class="home-welcome" aria-labelledby="home-title">
        <div class="home-welcome-copy">
            <span class="home-eyebrow"><span></span> CÔNG NGHỆ CHO CUỘC SỐNG</span>
            <h1 id="home-title">Một điểm đến.<br><em>Trọn bộ góc máy.</em></h1>
            <p>Từ màn hình đầu tiên đến góc máy mơ ước.<br>Khám phá thiết bị phù hợp với cách bạn làm việc và giải trí.</p>
            <a class="button-primary" href="#man-hinh">Khám phá sản phẩm <?= icon('arrow') ?></a>
        </div>
        <div class="home-welcome-art" aria-hidden="true">
            <span class="home-art-orbit orbit-one"></span><span class="home-art-orbit orbit-two"></span>
            <span class="home-art-word">YOUR NEXT SETUP</span>
            <img class="home-art-monitor" src="<?= e($heroMonitor ? product_image_url($heroMonitor) : url('assets/images/xiaomi-a27qi.webp')) ?>" alt="" width="340" height="300" fetchpriority="high">
            <img class="home-art-pc" src="<?= e($heroComputer ? product_image_url($heroComputer) : url('assets/images/home/pc-dell.jpg')) ?>" alt="" width="210" height="250">
            <img class="home-art-laptop" src="<?= e($heroLaptop ? product_image_url($heroLaptop) : url('assets/images/home/laptop-asus.jpg')) ?>" alt="" width="260" height="200">
            <span class="home-art-caption"><?= icon('check') ?> Làm việc. Sáng tạo. Giải trí.</span>
        </div>
    </section>
    <?php else: ?>
    <div class="home-search-heading"><div><span class="home-eyebrow">TÌM THIẾT BỊ CỦA BẠN</span><h1 id="home-title">Kết quả cho “<?= e($query) ?>”</h1><p><?= array_sum(array_map('count', $groupedProducts)) ?> sản phẩm trong các danh mục</p></div><a href="<?= e(url('index.php')) ?>" class="text-link">Xóa tìm kiếm <?= icon('close') ?></a></div>
    <?php endif; ?>

    <nav class="home-category-navigation" aria-label="Khám phá theo danh mục">
        <?php foreach ($homeCategories as $type => $category): ?>
        <a class="home-category-tile home-category-<?= e($type) ?>" href="<?= e(category_destination($type)) ?>">
            <span class="home-category-symbol"><?= icon($category['icon']) ?></span>
            <span class="home-category-text"><strong><?= e($category['label']) ?></strong><small><?= e(['monitor' => 'Sắc nét mọi góc nhìn', 'pc' => 'Hiệu năng cho mọi tác vụ', 'laptop' => 'Linh hoạt ở mọi nơi'][$type]) ?></small></span>
            <?= icon('arrow') ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($homeError): ?>
    <div class="empty-state"><?= icon('box') ?><h2>Chưa thể tải sản phẩm</h2><p>Vui lòng kiểm tra kết nối MySQL và chạy phần cập nhật database theo README.</p><a href="<?= e(url('index.php')) ?>" class="button-primary">Thử lại</a></div>
    <?php else: ?>
    <?php foreach ($homeCategories as $type => $category):
        $sectionProducts = $groupedProducts[$type];
        $destination = category_destination($type);
        $localSection = url('index.php#'.$category['anchor']);
    ?>
    <section class="home-section home-section-<?= e($type) ?>" id="<?= e($category['anchor']) ?>" data-product-section aria-labelledby="title-<?= e($type) ?>">
        <div class="home-section-heading">
            <div class="home-section-title"><span class="home-section-symbol"><?= icon($category['icon']) ?></span><div><div class="home-section-title-line"><h2 id="title-<?= e($type) ?>"><?= e($category['label']) ?></h2><span><?= count($sectionProducts) ?> sản phẩm</span></div><p><?= e(['monitor' => 'Thêm không gian, thêm cảm hứng.', 'pc' => 'Sẵn sàng cho công việc và những trận game.', 'laptop' => 'Mang cả thế giới công nghệ theo bạn.'][$type]) ?></p></div></div>
            <div class="home-section-actions">
                <?php if ($destination !== $localSection): ?><a class="home-view-all" href="<?= e($destination) ?>">Xem tất cả <?= icon('arrow') ?></a><?php endif; ?>
                <?php if ($sectionProducts): ?><div class="home-scroll-buttons"><button type="button" data-scroll-prev aria-controls="products-<?= e($type) ?>" aria-label="Xem sản phẩm <?= e($category['label']) ?> phía trước" disabled><?= icon('back') ?></button><button type="button" data-scroll-next aria-controls="products-<?= e($type) ?>" aria-label="Xem sản phẩm <?= e($category['label']) ?> tiếp theo" disabled><?= icon('arrow') ?></button></div><?php endif; ?>
            </div>
        </div>
        <?php if ($sectionProducts): ?>
        <div class="home-product-track" id="products-<?= e($type) ?>" tabindex="0" aria-label="Sản phẩm <?= e($category['label']) ?>; dùng phím mũi tên để cuộn">
            <?php foreach ($sectionProducts as $product) require __DIR__.'/includes/product-card.php'; ?>
        </div>
        <div class="home-section-bottom"><span><?= icon('arrow') ?> Lướt ngang để khám phá thêm</span><span data-scroll-position aria-live="polite" aria-atomic="true"><?= count($sectionProducts) ?> sản phẩm</span></div>
        <?php else: ?>
        <div class="home-empty-category"><?= icon($category['icon']) ?><p><?= $query !== '' ? 'Chưa có sản phẩm phù hợp với từ khóa này.' : 'Danh mục chưa có sản phẩm.' ?></p><?php if ($query !== ''): ?><a href="<?= e(url('index.php#'.$category['anchor'])) ?>">Xem các sản phẩm khác <?= icon('arrow') ?></a><?php endif; ?></div>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="home-closing-note"><span><?= icon('monitor') ?><?= icon('tower') ?><?= icon('laptop') ?></span><p>Góc máy của bạn.<strong> Theo cách của bạn.</strong></p><a href="#home-title" aria-label="Lên đầu trang"><?= icon('arrow') ?></a></div>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
