<?php
require_once __DIR__.'/icons.php';
require_once __DIR__.'/home-catalog.php';
$pageTitle = $pageTitle ?? 'Màn hình máy tính chính hãng | CellphoneS';
$activePage = $activePage ?? 'catalog';
$navigationCategories = require dirname(__DIR__).'/config/categories.php';
$searchAction = $searchAction ?? 'index.php';
$breadcrumbCategory = $breadcrumbCategory ?? 'monitor';
$breadcrumbCategoryMeta = shop_categories()[$breadcrumbCategory] ?? shop_categories()['monitor'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription ?? 'Khám phá màn hình, máy tính và laptop cho công việc, học tập và giải trí.') ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="app-url" content="<?= e(url()) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= e(url('assets/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/category-menu.css')) ?>">
    <?php foreach ($extraStyles ?? [] as $stylesheet): ?><link rel="stylesheet" href="<?= e(url($stylesheet)) ?>"><?php endforeach; ?>
    <script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
    <script src="<?= e(url('assets/js/category-menu.js')) ?>" defer></script>
    <?php foreach ($extraScripts ?? [] as $script): ?><script src="<?= e(url($script)) ?>" defer></script><?php endforeach; ?>
</head>
<body>
<div class="benefits-bar"><div class="container benefits-inner"><span><?= icon('shield') ?> Sản phẩm chính hãng <b>·</b> An tâm lựa chọn</span><span><?= icon('truck') ?> Nâng tầm góc máy <b>·</b> Trọn vẹn trải nghiệm</span><span><?= icon('gift') ?> Màn hình tốt <b>·</b> Giá thật hời</span></div></div>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= e(url('index.php')) ?>" aria-label="CellphoneS - Trang chủ">cellphone<span>S</span><i>®</i></a>
        <details class="category-menu" data-category-menu>
            <summary class="header-category" aria-label="Danh mục sản phẩm" aria-controls="category-navigation"><?= icon('grid') ?><span>Danh mục</span><?= icon('down') ?></summary>
            <nav class="category-dropdown" id="category-navigation" aria-label="Danh mục sản phẩm">
                <ul class="category-list">
                    <?php foreach ($navigationCategories as $category): ?>
                        <?php
                        $destination = trim($category['url']);
                        $categoryHref = $destination === '' ? null : ((preg_match('~^https?://~i', $destination) || str_starts_with($destination, '/')) ? $destination : url($destination));
                        ?>
                        <li>
                            <?php if ($categoryHref !== null): ?>
                                <a class="category-link" href="<?= e($categoryHref) ?>"><?= icon($category['icon']) ?><span class="category-name"><?= e($category['name']) ?></span><?= icon('chevron', 'category-chevron') ?></a>
                            <?php else: ?>
                                <span class="category-link category-pending" aria-disabled="true"><?= icon($category['icon']) ?><span class="category-name"><?= e($category['name']) ?></span><?= icon('chevron', 'category-chevron') ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </details>
        <form class="search-form" action="<?= e(url($searchAction)) ?>" role="search">
            <button type="submit" aria-label="Tìm kiếm"><?= icon('search') ?></button>
            <input type="search" name="q" value="<?= e(is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>" placeholder="Bạn cần tìm sản phẩm gì?" aria-label="Tìm sản phẩm">
            <kbd>⌕</kbd>
        </form>
        <a class="header-link desktop-link" href="<?= e(url('laptop.php?use=Văn phòng')) ?>"><?= icon('monitor') ?><span>Góc làm việc<br><strong>của bạn</strong></span></a>
        <button class="header-link favorite-header" type="button" data-show-favorites aria-label="Sản phẩm yêu thích"><?= icon('heart') ?><span>Sản phẩm<br><strong>yêu thích</strong></span></button>
        <a class="header-cart <?= $activePage === 'cart' ? 'current' : '' ?>" href="<?= e(url('cart.php')) ?>" aria-label="Giỏ hàng"><span class="cart-icon"><?= icon('bag') ?><b data-cart-count><?= cart_count() ?></b></span><span>Giỏ hàng</span></a>
    </div>
</header>
<div class="category-backdrop" data-category-backdrop hidden aria-hidden="true"></div>
<?php if ($activePage !== 'home'): ?>
<div class="breadcrumb-wrap"><nav class="container breadcrumbs" aria-label="Đường dẫn"><a href="<?= e(url('index.php')) ?>"><?= icon('home') ?> Trang chủ</a><?= icon('chevron') ?><?php if ($activePage === 'cart'): ?><span>Giỏ hàng</span><?php else: ?><a href="<?= e(category_destination($breadcrumbCategory)) ?>"<?= $activePage === 'catalog' ? ' aria-current="page"' : '' ?>><?= e($breadcrumbCategoryMeta['label']) ?></a><?php if ($activePage !== 'catalog'): ?><?= icon('chevron') ?><span><?= e($breadcrumbLabel ?? 'Chi tiết sản phẩm') ?></span><?php endif; ?><?php endif; ?></nav></div>
<?php endif; ?>
