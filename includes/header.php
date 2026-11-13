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
