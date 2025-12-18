<?php
require_once 'config.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy thông tin giỏ hàng
$stmt = $conn->prepare("SELECT gh.*, sp.ten_san_pham, sp.gia, sp.don_vi, sp.so_luong as ton_kho, sp.hinh_anh
                       FROM gio_hang gh
                       JOIN san_pham sp ON gh.san_pham_id = sp.id
                       WHERE gh.nguoi_dung_id = ?
                       ORDER BY gh.ngay_them DESC");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['gia'] * $item['so_luong'];
}

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="../../css/cart.css">
</head>

<body>
    <div id="toast" class="toast">
        <button class="toast-close" onclick="hideToast()">&times;</button>
        <div class="toast-header">
            <span class="toast-icon" id="toastIcon">✓</span>
            <span class="toast-title" id="toastTitle">Thông báo</span>
        </div>
        <div class="toast-message" id="toastMessage"></div>
    </div>

    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>🛒 Giỏ Hàng</h1>
            </div>
            <a href="index.php" class="btn btn-light">← Tiếp tục mua sắm</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (count($cart_items) > 0): ?>
            <div class="card">
                <h2 style="margin-bottom: 20px;">Sản phẩm trong giỏ</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr id="cart-row-<?php echo $item['id']; ?>">
                                <td>
                                    <div class="product-info">
                                        <div class="product-image-wrapper">
                                            <?php if ($item['hinh_anh'] && file_exists('uploads/' . $item['hinh_anh'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($item['hinh_anh']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['ten_san_pham']); ?>"
                                                    class="product-image">
                                            <?php else: ?>
                                                <div class="product-icon">🥤</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-details">
                                            <div class="product-name">
                                                <?php echo htmlspecialchars($item['ten_san_pham']); ?>
                                            </div>
                                            <div class="product-stock">
                                                Còn lại: <?php echo $item['ton_kho']; ?> <?php echo $item['don_vi']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?php echo formatPrice($item['gia']); ?></strong></td>
                                <td>
                                    <div class="quantity-control">
                                        <button type="button"
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, -1, <?php echo $item['so_luong']; ?>, <?php echo $item['ton_kho']; ?>, '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>')">
                                            -
                                        </button>

                                        <input type="number"
                                            id="qty-<?php echo $item['id']; ?>"
                                            value="<?php echo $item['so_luong']; ?>"
                                            min="1"
                                            max="<?php echo $item['ton_kho']; ?>"

                                            data-current-qty="<?php echo $item['so_luong']; ?>"

                                            onchange="updateQuantityManually(
                                                <?php echo $item['id']; ?>,
                                                <?php echo $item['ton_kho']; ?>,
                                                '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>',
                                                this
                                            )"

                                            style="width: 70px; text-align: center;">

                                        <button type="button"
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 1, <?php echo $item['so_luong']; ?>, <?php echo $item['ton_kho']; ?>, '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>')">
                                            +
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #667eea; font-size: 1.1em;" id="subtotal-<?php echo $item['id']; ?>">
                                        <?php echo formatPrice($item['gia'] * $item['so_luong']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-danger"
                                        onclick="deleteFromCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>')">
                                        🗑️ Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="cart-summary">
                    <h3 style="margin-bottom: 20px;">Tổng đơn hàng</h3>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span id="cart-subtotal"><?php echo formatPrice($total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span id="cart-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-success" style="width: 100%; text-align: center; margin-top: 20px; display: block;">
                        💳 Thanh toán
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="empty-cart">
                    <h2>🛒 Giỏ hàng trống</h2>
                    <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">
                        Mua sắm ngay
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Khởi tạo giá 
        const prices = <?php
                        $price_map = [];
                        foreach ($cart_items as $item) {
                            $price_map[$item['id']] = (float)$item['gia'];
                        }
                        echo json_encode($price_map);
                        ?>;
    </script>
    <script src="js/admin/carts.js"></script>

</body>

</html>