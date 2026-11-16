<?php
require_once __DIR__.'/home-catalog.php';
$discount = (float)$product['old_price'] > (float)$product['price'] ? round((1 - (float)$product['price'] / (float)$product['old_price']) * 100) : 0;
$productLink = product_details_url($product);
$productCategory = category_from_product($product);
?>
<article class="product-card">
    <div class="card-badges"><?php if ($discount > 0): ?><span class="discount-badge">Giảm <?= $discount ?>%</span><?php else: ?><span class="new-badge">Mới về</span><?php endif; ?><span class="installment-badge">Chính hãng</span></div>
    <a class="product-image" href="<?= e($productLink) ?>"><img src="<?= e(product_image_url($product)) ?>" alt="<?= e($product['name']) ?>" loading="lazy" width="280" height="220"></a>
    <div class="card-content"><div class="spec-chips"><?php foreach (product_chips($product) as $chip): ?><span><?= e($chip) ?></span><?php endforeach; ?></div>
        <a class="product-name" href="<?= e($productLink) ?>"><?= e($product['name']) ?></a>
        <div class="card-price"><strong><?= money($product['price']) ?></strong><?php if ($discount > 0): ?><del><?= money($product['old_price']) ?></del><?php endif; ?></div>
        <div class="product-note"><?= icon($productCategory === 'monitor' ? 'shield' : 'cpu') ?><span><?= $productCategory === 'monitor' ? 'Tấm nền '.e($product['panel']).' · Hình ảnh sắc nét' : e($product['gpu'] ?: $product['usage_type']) ?></span></div>
        <div class="card-bottom"><?php if ((int)$product['review_count'] > 0): ?><span class="rating"><?= icon('star') ?> <b><?= e($product['rating']) ?></b><small>(<?= (int)$product['review_count'] ?>)</small></span><?php else: ?><span class="new-product-label"><?= icon('box') ?> Mới về</span><?php endif; ?><div class="card-actions"><button type="button" class="favorite-button" data-favorite="<?= (int)$product['id'] ?>" data-name="<?= e($product['name']) ?>" data-image="<?= e(product_image_url($product)) ?>" data-href="<?= e($productLink) ?>" data-price="<?= e(money($product['price'])) ?>" aria-label="Yêu thích <?= e($product['name']) ?>" aria-pressed="false"><?= icon('heart') ?></button><button type="button" class="quick-add" data-add-cart="<?= (int)$product['id'] ?>" aria-label="Thêm <?= e($product['name']) ?> vào giỏ hàng" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>><?= icon('cart') ?></button></div></div>
    </div>
</article>
