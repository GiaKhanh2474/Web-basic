<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/catalog.php';
require_once __DIR__ . '/includes/cart.php';

$filterKeys = ['q', 'brand', 'use', 'cpu', 'ram', 'storage', 'size', 'price', 'sort'];
$filters = [];
foreach ($filterKeys as $key) {
    if (isset($_GET[$key]) && is_string($_GET[$key]) && $_GET[$key] !== '') {
        $filters[$key] = trim($_GET[$key]);
    }
}

$catalogError = false;
$heroProduct = null;
try {
    $allProducts = products(array_merge($filters, ['category' => 'laptop']));
    $allBrands = brands('laptop');
    $heroProduct = product_by_slug('laptop-asus-vivobook-15-demo') ?? ($allProducts[0] ?? null);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $allProducts = [];
    $allBrands = ['Acer', 'ASUS', 'Dell', 'HP', 'Lenovo'];
    $catalogError = true;
    http_response_code(503);
}

function laptop_filter_link(array $changes): string
{
    global $filters;
    $query = array_filter(array_merge($filters, $changes), static fn ($value) => $value !== '');
    return url('laptop.php' . ($query ? '?' . http_build_query($query) : '') . '#catalog');
}

$pageTitle = 'Laptop chính hãng | Học tập, làm việc và giải trí - CellphoneS';
$pageDescription = 'Khám phá các mẫu laptop cho học tập, làm việc và giải trí. Lọc theo thương hiệu, cấu hình và mức giá.';
$searchAction = 'laptop.php';
$breadcrumbCategory = 'laptop';
require __DIR__ . '/includes/header.php';
?>
<main class="container catalog-page">
    <div class="page-heading">
        <div>
            <h1>Laptop <span>Đồng hành cùng bạn trong học tập, công việc và giải trí.</span></h1>
        </div>
        <a href="#catalog" class="text-link">Khám phá ngay <?= icon('arrow') ?></a>
    </div>

    <section class="hero-layout" aria-label="Laptop nổi bật">
        <a class="hero-main" href="<?= e($heroProduct ? url('product.php?slug=' . rawurlencode($heroProduct['slug'])) : '#catalog') ?>">
            <div class="hero-copy">
                <span class="hero-kicker"><i></i> MỞ RA KHÔNG GIAN SÁNG TẠO</span>
                <h2>Chạm ý tưởng mới.<br><em>Bứt phá mỗi ngày.</em></h2>
                <p><?= e($heroProduct['name'] ?? 'Tìm chiếc laptop phù hợp với bạn') ?></p>
                <div class="hero-specs">
                    <span><?= e($heroProduct['cpu'] ?? 'Intel Core / AMD Ryzen') ?></span>
                    <b>·</b>
                    <span><?= e($heroProduct['ram'] ?? '8GB – 16GB RAM') ?></span>
                    <b>·</b>
                    <span><?= e($heroProduct['storage'] ?? 'SSD') ?></span>
                </div>
                <div class="hero-price">
                    <small>Giá sản phẩm</small>
                    <strong><?= $heroProduct ? money($heroProduct['price']) : 'Khám phá ngay' ?></strong>
                </div>
                <span class="hero-cta">Khám phá sản phẩm <?= icon('arrow') ?></span>
            </div>
            <div class="hero-art">
                <span class="hero-ring ring-one"></span>
                <span class="hero-ring ring-two"></span>
                <span class="hero-brand"><?= e($heroProduct['brand'] ?? 'LAPTOP') ?></span>
                <img src="<?= e($heroProduct ? product_image_url($heroProduct) : url('assets/images/home/laptop-asus.jpg')) ?>"
                     alt="<?= e($heroProduct['name'] ?? 'Laptop nổi bật') ?>" width="500" height="400" fetchpriority="high">
                <span class="hero-tag"><b><?= e($heroProduct['screen_size'] ?? '15.6') ?>″</b> Màn hình rộng rãi</span>
            </div>
            <div class="hero-bottom">
                <span class="hero-dots"><i class="active"></i><i></i><i></i></span>
                <span>LINH HOẠT MỌI NƠI · SẴN SÀNG MỌI Ý TƯỞNG</span>
            </div>
            </a>
        <div class="side-promos">
            <a class="promo promo-gaming" href="<?= e(url('laptop.php?use=Gaming#catalog')) ?>">
                <div>
                    <span>GEAR UP. GAME ON.</span>
                    <h3>Đắm chìm<br>trong thế giới game</h3>
                    <p>Laptop Gaming <?= icon('arrow') ?></p>
                </div>
                <div class="promo-icon"><?= icon('game') ?></div>
                <span class="promo-watermark">RTX</span>
            </a>
            <a class="promo promo-work" href="<?= e(url('laptop.php?use=Văn phòng#catalog')) ?>">
                <div>
                    <span>WORK & STUDY SMART</span>
                    <h3>Học tập hiệu quả.<br>Làm việc linh hoạt.</h3>
                    <p>Laptop văn phòng <?= icon('arrow') ?></p>
                </div>
                <div class="promo-icon" style="color:#658194"><?= icon('laptop') ?></div>
            </a>
        </div>
    </section>

    <section class="discovery-section" aria-labelledby="brand-heading">
        <div class="section-label">
            <h2>Thương hiệu nổi bật</h2>
            <span>Lựa chọn chiếc laptop từ thương hiệu bạn yêu thích</span>
        </div>
        <div class="brand-list">
            <a href="<?= e(laptop_filter_link(['brand' => ''])) ?>" class="brand-pill brand-all <?= empty($filters['brand']) ? 'active' : '' ?>">Tất cả</a>
            <?php foreach ($allBrands as $brand): ?>
                <a href="<?= e(laptop_filter_link(['brand' => $brand])) ?>"
                   class="brand-pill brand-<?= e(strtolower($brand)) ?> <?= ($filters['brand'] ?? '') === $brand ? 'active' : '' ?>"><?= e($brand) ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="needs-section" aria-labelledby="needs-heading">
        <h2>Bạn cần laptop cho?</h2>
        <div class="needs-list">
            <?php foreach ([['Văn phòng', 'briefcase', 'Học tập & văn phòng'], ['Gaming', 'game', 'Chơi game'], ['Đồ họa', 'palette', 'Thiết kế đồ họa'], ['Lập trình', 'code', 'Lập trình']] as [$value, $symbol, $label]): ?>
                <a class="need-chip <?= ($filters['use'] ?? '') === $value ? 'active' : '' ?>"
                   href="<?= e(laptop_filter_link(['use' => ($filters['use'] ?? '') === $value ? '' : $value])) ?>"><?= icon($symbol) ?><?= e($label) ?></a>
            <?php endforeach; ?>
            <a class="need-chip <?= ($filters['ram'] ?? '') === '16GB' ? 'active' : '' ?>"
               href="<?= e(laptop_filter_link(['ram' => ($filters['ram'] ?? '') === '16GB' ? '' : '16GB'])) ?>"><?= icon('cpu') ?>Laptop RAM 16GB</a>
        </div>
    </section>
    <section class="catalog-section" id="catalog" aria-labelledby="catalog-heading">
        <div class="catalog-heading">
            <h2 id="catalog-heading"><?= !empty($filters['q']) ? 'Kết quả tìm kiếm' : 'Chọn laptop của bạn' ?> <span><?= count($allProducts) ?> sản phẩm</span></h2>
            <span class="catalog-note"><span></span> Sẵn sàng cho hành trình mới</span>
        </div>

        <form class="filter-form" method="get" action="<?= e(url('laptop.php#catalog')) ?>">
            <div class="filters-row">
                <span class="filter-label"><?= icon('sliders') ?> Bộ lọc</span>
                <?php foreach (['q', 'brand', 'use', 'sort'] as $key): ?>
                    <?php if (!empty($filters[$key])): ?><input type="hidden" name="<?= e($key) ?>" value="<?= e($filters[$key]) ?>"><?php endif; ?>
                <?php endforeach; ?>
                <?php
                $selects = [
                    'price' => ['Khoảng giá', 'under15' => 'Dưới 15 triệu', '15to25' => 'Từ 15 – 25 triệu', 'over25' => 'Trên 25 triệu'],
                    'cpu' => ['Vi xử lý (CPU)', 'Core i3' => 'Intel Core i3', 'Core i5' => 'Intel Core i5', 'Core i7' => 'Intel Core i7', 'Core Ultra' => 'Intel Core Ultra', 'Ryzen 5' => 'AMD Ryzen 5'],
                    'ram' => ['RAM', '8GB' => '8GB', '16GB' => '16GB', '32GB' => '32GB'],
                    'storage' => ['Ổ cứng', '256GB' => '256GB', '512GB' => '512GB', '1TB' => '1TB'],
                    'size' => ['Kích thước màn hình', '14' => '14 inch', '15.6' => '15.6 inch', '16' => '16 inch'],
                ];
                foreach ($selects as $key => $options):
                ?>
                    <label class="filter-select">
                        <span class="sr-only"><?= e($options[0]) ?></span>
                        <select name="<?= e($key) ?>" data-auto-submit>
                            <option value=""><?= e($options[0]) ?></option>
                            <?php foreach ($options as $value => $label): if ($value === 0) continue; ?>
                                <option value="<?= e($value) ?>" <?= ($filters[$key] ?? '') === (string) $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= icon('down') ?>
                    </label>
                <?php endforeach; ?>
                <noscript><button type="submit" class="button-primary">Áp dụng</button></noscript>
                <?php if ($filters): ?><a class="clear-filters" href="<?= e(url('laptop.php#catalog')) ?>">Xóa bộ lọc <?= icon('close') ?></a><?php endif; ?>
            </div>
            </form>

        <div class="sort-row">
            <span>Sắp xếp theo:</span>
            <?php foreach (['popular' => 'Nổi bật', 'price_asc' => 'Giá thấp → cao', 'price_desc' => 'Giá cao → thấp', 'newest' => 'Mới nhất'] as $sort => $label): ?>
                <a class="sort-button <?= ($filters['sort'] ?? 'popular') === $sort ? 'active' : '' ?>" href="<?= e(laptop_filter_link(['sort' => $sort])) ?>">
                    <?= $sort === 'popular' ? icon('bolt') : '' ?><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($filters['q'])): ?>
            <p class="search-summary">Sản phẩm phù hợp với “<strong><?= e($filters['q']) ?></strong>”</p>
        <?php endif; ?>

        <?php if ($catalogError): ?>
            <div class="empty-state">
                <?= icon('box') ?>
                <h3>Danh mục đang được chuẩn bị</h3>
                <p>Chưa kết nối được cơ sở dữ liệu. Bạn hãy khởi động MySQL trong XAMPP và kiểm tra cấu hình trong <code>config/database.php</code>.</p>
                <a class="button-primary" href="<?= e(url('laptop.php')) ?>">Thử lại</a>
            </div>
        <?php elseif (!$allProducts): ?>
            <div class="empty-state">
                <?= icon('search') ?>
                <h3>Chưa tìm thấy laptop phù hợp</h3>
                <p>Thử từ khóa khác hoặc bỏ bớt bộ lọc. Danh mục laptop hiện lấy dữ liệu có <code>category = 'laptop'</code> trong bảng <code>products</code>.</p>
                <a class="button-primary" href="<?= e(url('laptop.php#catalog')) ?>">Xem tất cả laptop</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($allProducts as $product): require __DIR__ . '/includes/product-card.php'; endforeach; ?>
            </div>
            <div class="catalog-end"><span></span>Bạn đã xem tất cả <?= count($allProducts) ?> laptop<span></span></div>
        <?php endif; ?>
    </section>

    <section class="editorial-banner">
        <div class="editorial-symbol"><?= icon('laptop') ?></div>
        <div>
            <span class="eyebrow">CHIẾC LAPTOP PHÙ HỢP. KHỞI ĐẦU MỚI.</span>
            <h2>Sẵn sàng cho mọi kế hoạch của bạn.</h2>
            <p>Từ lớp học, văn phòng đến những phút giây giải trí, chọn người bạn đồng hành phù hợp.</p>
        </div>
        <a href="<?= e(url('laptop.php?use=Văn phòng#catalog')) ?>">Khám phá laptop văn phòng <?= icon('arrow') ?></a>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>