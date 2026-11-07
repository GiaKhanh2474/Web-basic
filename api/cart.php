<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cart.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], 405);
}
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    respond(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 422);
}
$token = $data['csrf_token'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    respond(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], 403);
}
$action = $data['action'] ?? '';
$id = filter_var($data['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!in_array($action, ['add', 'update', 'remove'], true) || $id === false) {
    respond(['success' => false, 'message' => 'Sản phẩm hoặc thao tác không hợp lệ.'], 422);
}
$quantity = filter_var($data['quantity'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
if ($action !== 'remove' && $quantity === false) {
    respond(['success' => false, 'message' => 'Số lượng phải từ 1 đến 99.'], 422);
}
try {
    $product = product_by_id($id);
    if (!$product) {
        respond(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 422);
    }
    $_SESSION['cart'] ??= [];
    if ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
        $message = 'Đã xóa sản phẩm khỏi giỏ hàng.';
    } else {
        $target = $action === 'add' ? (int) ($_SESSION['cart'][$id] ?? 0) + $quantity : $quantity;
        if ($target > min(99, (int) $product['stock'])) {
            respond(['success' => false, 'message' => 'Số lượng vượt quá tồn kho hoặc giới hạn 99 sản phẩm.'], 422);
        }
        $_SESSION['cart'][$id] = $target;
        $message = $action === 'add' ? 'Đã thêm vào giỏ hàng (' . $product['name'] . ')' : 'Đã cập nhật giỏ hàng.';
    }
    $items = cart_items();
    respond(['success' => true, 'message' => $message, 'count' => cart_count(), 'total' => (int) array_sum(array_column($items, 'line_total')), 'items' => $items]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    respond(['success' => false, 'message' => 'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.'], 503);
}
