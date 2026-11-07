<?php
require_once __DIR__.'/includes/bootstrap.php';
require_once __DIR__.'/includes/catalog.php';
require_once __DIR__.'/includes/home-catalog.php';
require_once __DIR__.'/includes/cart.php';
$slug = is_string($_GET['slug'] ?? null) ? $_GET['slug'] : '';
$product = null;
$databaseError = false;
try { $product = product_by_slug($slug); } catch (PDOException $exception) { error_log($exception->getMessage()); $databaseError = true; }
$activePage = 'product';
if (!$product) {
    http_response_code($databaseError ? 503 : 404);

    $pageTitle = $databaseError ? 'Danh mục đang được chuẩn bị' : 'Không tìm thấy sản phẩm';
    require __DIR__.'/includes/header.php';
    echo '<main class="container"><div class="empty-state">'.icon('monitor').'<h1>'.e($pageTitle).'</h1><p>'.($databaseError ? 'Vui lòng khởi động MySQL và nhập cơ sở dữ liệu theo hướng dẫn README.' : 'Sản phẩm có thể đã được cập nhật. Khám phá những màn hình khác trong danh mục.').'</p><a class="button-primary" href="'.e(url('monitor.php')).'">Về danh mục màn hình</a></div></main>';
    require __DIR__.'/includes/footer.php';
    exit;
}
$pageTitle = $product['name'].' | CellphoneS';
$breadcrumbLabel = $product['brand'];
$discount = (float)$product['old_price'] > (float)$product['price'] ? round((1-(float)$product['price']/(float)$product['old_price'])*100) : 0;
$breadcrumbCategory = category_from_product($product);
$relatedProducts = array_slice(array_values(array_filter(products(['category' => $breadcrumbCategory]), fn($item) => (int)$item['id'] !== (int)$product['id'])), 0, 5);
if ($breadcrumbCategory !== 'monitor') $extraStyles = ['assets/css/home.css'];
require __DIR__.'/includes/header.php';
if ($breadcrumbCategory !== 'monitor') { require __DIR__.'/includes/computer-detail.php'; exit; }
?>
<main class="container detail-page">
    <div class="detail-heading"><h1><?= e($product['name']) ?> <span>(<?= e($product['resolution']) ?>/<?= e($product['panel']) ?>/<?= e($product['refresh_rate']) ?>Hz/<?= e($product['response_time']) ?>)</span></h1><div class="detail-meta"><span class="rating"><?= icon('star') ?> <b><?= e($product['rating']) ?></b> <small>(<?= (int)$product['review_count'] ?> đánh giá)</small></span><span class="meta-divider"></span><span>Thương hiệu: <a href="<?= e(url('monitor.php?brand='.rawurlencode($product['brand']))) ?>"><?= e($product['brand']) ?></a></span><a href="#specifications"><?= icon('sliders') ?> Thông số kỹ thuật</a></div></div>
    <div class="product-detail-grid"><div class="product-gallery"><div class="gallery-stage" id="gallery-image"><span class="gallery-brand"><?= e($product['brand']) ?><small>THIẾT KẾ TINH GỌN. HIỂN THỊ ẤN TƯỢNG.</small></span><img src="<?= e(product_image_url($product)) ?>" alt="<?= e($product['name']) ?>" width="650" height="510"><div class="gallery-specs"><div><b><?= e($product['screen_size']) ?>″</b><span>Kích thước</span></div><div><b><?= e($product['resolution']) ?></b><span>Độ phân giải</span></div><div><b><?= e($product['refresh_rate']) ?>Hz</b><span>Tần số quét</span></div></div></div>
        <div class="gallery-thumbnails"><span class="thumbnail active"><img src="<?= e(product_image_url($product)) ?>" alt="Hình sản phẩm đang xem" width="70" height="55"></span><a class="thumbnail" href="#highlights"><?= icon('bolt') ?><span>Điểm nổi bật</span></a><a class="thumbnail" href="#specifications"><?= icon('sliders') ?><span>Thông số</span></a><span class="gallery-caption">Hình ảnh sản phẩm</span></div>
        <div class="product-promise"><h3><?= icon('box') ?> Thông tin sản phẩm</h3><div><?= icon('monitor') ?><span>Màn hình <?= e($product['screen_size']) ?> inch, tấm nền <?= e($product['panel']) ?> với độ phân giải <?= e($product['resolution']) ?>.</span></div><div><?= icon('bolt') ?><span>Tần số quét <?= e($product['refresh_rate']) ?>Hz, phản hồi <?= e($product['response_time']) ?>.</span></div><div><?= icon('shield') ?><span>Lựa chọn cho nhu cầu <?= e(mb_strtolower($product['usage_type'], 'UTF-8')) ?>.</span></div></div>
    </div><aside class="purchase-panel"><div class="stock-label"><i></i><?= (int)$product['stock'] > 0 ? 'Sẵn hàng' : 'Tạm hết hàng' ?><span>SKU: MH-<?= str_pad((string)$product['id'],4,'0',STR_PAD_LEFT) ?></span></div><div class="purchase-price"><span>Giá sản phẩm</span><div><strong><?= money($product['price']) ?></strong><?php if ($discount): ?><del><?= money($product['old_price']) ?></del><b>-<?= $discount ?>%</b><?php endif; ?></div><p>Giá niêm yết cho 1 sản phẩm</p></div>
        <div class="variant-label"><strong>Cấu hình sản phẩm</strong><span><?= e($product['brand']) ?></span></div><div class="selected-variant"><?= icon('check') ?><div><strong><?= e($product['screen_size']) ?> inch · <?= e($product['resolution']) ?> · <?= e($product['panel']) ?></strong><span><?= e($product['refresh_rate']) ?>Hz / <?= e($product['response_time']) ?></span></div><b><?= money($product['price']) ?></b></div>
        <div class="offer-box"><h3><?= icon('gift') ?> Một màn hình. Nhiều trải nghiệm.</h3><ul><li><span>1</span>Không gian rộng hơn cho công việc và giải trí.</li><li><span>2</span>Hình ảnh rõ nét với tấm nền <?= e($product['panel']) ?>.</li><li><span>3</span>Chuyển động mượt mà ở <?= e($product['refresh_rate']) ?>Hz.</li></ul></div>
        <div class="purchase-quantity"><label for="product-quantity">Số lượng</label><div class="quantity-control"><button type="button" data-step="-1" data-target="product-quantity" aria-label="Giảm số lượng"><?= icon('minus') ?></button><input id="product-quantity" type="number" min="1" max="<?= min(99,max(1,(int)$product['stock'])) ?>" value="1" aria-label="Số lượng sản phẩm"><button type="button" data-step="1" data-target="product-quantity" aria-label="Tăng số lượng"><?= icon('plus') ?></button></div><span><?= (int)$product['stock'] ?> sản phẩm có sẵn</span></div>
        <button class="button-primary add-main" type="button" data-add-cart="<?= (int)$product['id'] ?>" data-quantity-input="product-quantity" <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>><?= icon('cart') ?><span><strong>THÊM VÀO GIỎ HÀNG</strong><small>Lưu lựa chọn yêu thích của bạn</small></span></button>
        <a class="button-outline view-cart-button" href="<?= e(url('cart.php')) ?>">Xem giỏ hàng <?= icon('arrow') ?></a><div class="cart-reassurance"><?= icon('shield') ?> Thoải mái chọn lựa, chưa cần thanh toán.</div>
        <div class="help-box"><?= icon('monitor') ?><p><strong>Cần thêm lựa chọn?</strong><a href="<?= e(url('monitor.php?brand='.rawurlencode($product['brand']))) ?>">Khám phá màn hình <?= e($product['brand']) ?> <?= icon('arrow') ?></a></p></div>
    </aside></div>
    <div class="detail-info-grid"><section class="description-panel" id="highlights"><h2>Đặc điểm nổi bật</h2><div class="highlight-box"><h3>Không gian mới cho mọi ý tưởng</h3><ul><li>Màn hình <?= e($product['screen_size']) ?> inch, độ phân giải <?= e($product['resolution']) ?> cho không gian hiển thị rộng rãi.</li><li>Tấm nền <?= e($product['panel']) ?> phù hợp với nhiều tác vụ hằng ngày.</li><li>Tần số quét <?= e($product['refresh_rate']) ?>Hz cho trải nghiệm hình ảnh mượt mà.</li></ul></div><h3><?= e($product['name']) ?></h3><p><?= nl2br(e($product['description'])) ?></p><div class="description-visual"><div><span class="eyebrow">THÊM KHÔNG GIAN CHO CẢM HỨNG</span><h3>Sắc nét từng chi tiết.<br>Mượt mà từng khoảnh khắc.</h3><p><?= e($product['resolution']) ?> · <?= e($product['panel']) ?> · <?= e($product['refresh_rate']) ?>Hz</p></div><img src="<?= e(product_image_url($product)) ?>" alt="Thiết kế <?= e($product['name']) ?>" loading="lazy" width="350" height="280"></div></section>
    <section class="specification-panel" id="specifications"><h2>Thông số kỹ thuật</h2><table><tbody><?php foreach (['Thương hiệu'=>$product['brand'],'Kích thước màn hình'=>$product['screen_size'].' inch','Tấm nền'=>$product['panel'],'Độ phân giải'=>$product['resolution'],'Tần số quét'=>$product['refresh_rate'].' Hz','Thời gian phản hồi'=>$product['response_time'],'Nhu cầu sử dụng'=>$product['usage_type'],'Mã sản phẩm'=>'MH-'.str_pad((string)$product['id'],4,'0',STR_PAD_LEFT)] as $label=>$value): ?><tr><th scope="row"><?= e($label) ?></th><td><?= e($value) ?></td></tr><?php endforeach; ?></tbody></table></section></div>
    <?php if ($relatedProducts): ?><section class="related-section"><div class="section-label"><h2>Có thể bạn cũng thích</h2><a class="text-link" href="<?= e(url('monitor.php')) ?>">Xem tất cả <?= icon('arrow') ?></a></div><div class="product-grid"><?php foreach($relatedProducts as $product) require __DIR__.'/includes/product-card.php'; ?></div></section><?php endif; ?>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
