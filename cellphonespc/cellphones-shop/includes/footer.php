<section class="service-strip"><div class="container service-grid">
    <div><?= icon('shield') ?><span><strong>Chất lượng trong từng lựa chọn</strong><small>Thông số rõ ràng, dễ so sánh</small></span></div>
    <div><?= icon('monitor') ?><span><strong>Thiết bị cho mọi nhu cầu</strong><small>Làm việc, sáng tạo và giải trí</small></span></div>
    <div><?= icon('bag') ?><span><strong>Mua sắm thật đơn giản</strong><small>Lưu sản phẩm vào giỏ hàng</small></span></div>
    <div><?= icon('headphones') ?><span><strong>Trải nghiệm liền mạch</strong><small>Trên máy tính và điện thoại</small></span></div>
</div></section>
<footer class="site-footer"><div class="container footer-grid">
    <div class="footer-brand"><a class="logo" href="<?= e(url('index.php')) ?>">cellphone<span>S</span><i>®</i></a><p>Công nghệ cho cuộc sống.<br>Thiết bị cho mọi cảm hứng.</p><span class="footer-tag">YOUR NEXT SETUP STARTS HERE</span></div>
    <div><h3>Khám phá sản phẩm</h3><?php foreach (shop_categories() as $footerType => $footerCategory): ?><a href="<?= e(category_destination($footerType)) ?>"><?= e($footerCategory['label']) ?></a><?php endforeach; ?></div>
    <div><h3>Thương hiệu nổi bật</h3><a href="<?= e(url('laptop.php?brand=Xiaomi')) ?>">Xiaomi</a><a href="<?= e(url('laptop.php?brand=LG')) ?>">LG UltraGear</a><a href="<?= e(url('laptop.php?brand=Samsung')) ?>">Samsung</a></div>
    <div><h3>Góc mua sắm của bạn</h3><a href="<?= e(url('cart.php')) ?>">Giỏ hàng của tôi</a><button type="button" data-show-favorites>Sản phẩm yêu thích</button><a href="<?= e(url('laptop.php#catalog')) ?>">Tất cả màn hình</a></div>
</div><div class="container footer-bottom"><p>Bản dựng giao diện tham khảo CellphoneS · Dự án PHP & MySQL. Không phải website chính thức.</p><span>Được thiết kế cho góc máy của bạn.</span></div></footer>
<div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="true"></div>
<dialog id="favorites-dialog" class="favorites-dialog"><div class="dialog-heading"><div><span class="eyebrow">GÓC MUA SẮM CỦA BẠN</span><h2>Sản phẩm yêu thích</h2></div><button class="icon-button" type="button" data-close-dialog aria-label="Đóng"><?= icon('close') ?></button></div><div id="favorites-content"></div></dialog>
<button class="back-to-top" type="button" aria-label="Về đầu trang" title="Về đầu trang"><?= icon('arrow') ?></button>
</body>
</html>
