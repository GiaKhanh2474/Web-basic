<?php
// Chỉ cấu hình liên kết điều hướng; không tạo website hay danh mục sản phẩm mới.
// URL trống: chỉ hiển thị biểu tượng và tên, chưa chuyển trang.
// Khi các website khác sẵn sàng, thay '' bằng '/laptop/', '/pc/'
// hoặc URL đầy đủ, ví dụ 'https://example.com/laptop/'.
return [
    ['name' => 'Màn hình', 'category' => 'monitor', 'icon' => 'monitor', 'url' => 'monitor.php'],
    ['name' => 'PC', 'category' => 'pc', 'icon' => 'tower', 'url' => 'pc.php'],
    ['name' => 'Laptop', 'category' => 'laptop', 'icon' => 'laptop', 'url' => ''],
];
