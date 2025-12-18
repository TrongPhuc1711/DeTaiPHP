<?php
require_once 'config.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy giỏ hàng
$stmt = $conn->prepare("SELECT gh.*, sp.ten_san_pham, sp.gia, sp.so_luong as ton_kho
                        FROM gio_hang gh
                        JOIN san_pham sp ON gh.san_pham_id = sp.id
                        WHERE gh.nguoi_dung_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) == 0) {
    redirect('cart.php');
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['gia'] * $item['so_luong'];
}

// Lấy thông tin user
$user = getCurrentUser();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Tạo đơn hàng
        $stmt = $conn->prepare("INSERT INTO don_hang (nguoi_dung_id, ten_khach_hang, so_dien_thoai, dia_chi, tong_tien, ghi_chu) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            sanitize($_POST['ten_khach_hang']),
            sanitize($_POST['so_dien_thoai']),
            sanitize($_POST['dia_chi']),
            $total,
            sanitize($_POST['ghi_chu'])
        ]);
        
        $order_id = $conn->lastInsertId();
        
        // Thêm chi tiết đơn hàng và cập nhật tồn kho
        $stmt = $conn->prepare("INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia, thanh_tien) 
                               VALUES (?, ?, ?, ?, ?)");
        $updateStock = $conn->prepare("UPDATE san_pham SET so_luong = so_luong - ? WHERE id = ?");
        
        foreach ($cart_items as $item) {
            // Kiểm tra tồn kho
            if ($item['ton_kho'] < $item['so_luong']) {
                throw new Exception("Sản phẩm {$item['ten_san_pham']} không đủ hàng!");
            }
            
            $stmt->execute([
                $order_id,
                $item['san_pham_id'],
                $item['so_luong'],
                $item['gia'],
                $item['gia'] * $item['so_luong']
            ]);
            
            $updateStock->execute([$item['so_luong'], $item['san_pham_id']]);
        }
        
        // Xóa giỏ hàng
        $stmt = $conn->prepare("DELETE FROM gio_hang WHERE nguoi_dung_id = ?");
        $stmt->execute([$user_id]);
        
        $conn->commit();
        
        $_SESSION['message'] = 'Đặt hàng thành công! Mã đơn hàng: #' . $order_id;
        $_SESSION['message_type'] = 'success';
        redirect('my-orders.php');
        
    } catch (Exception $e) {
        $conn->rollBack();
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="./css/checkout.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>💳 Thanh Toán</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2 style="margin-bottom: 20px;">Thông tin giao hàng</h2>
            
            <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Tên người nhận *</label>
                    <input type="text" name="ten_khach_hang" 
                           value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input type="text" name="so_dien_thoai" 
                           value="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ giao hàng *</label>
                    <textarea name="dia_chi" required><?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="ghi_chu" placeholder="Yêu cầu đặc biệt, thời gian giao hàng..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">
                    ✓ Đặt hàng
                </button>
            </form>
        </div>
        
        <div class="order-summary">
            <div class="card">
                <h3 style="margin-bottom: 20px;">Đơn hàng của bạn</h3>
                
                <?php foreach ($cart_items as $item): ?>
                <div class="summary-item">
                    <div>
                        <strong><?php echo htmlspecialchars($item['ten_san_pham']); ?></strong>
                        <div style="font-size: 0.9em; color: #7f8c8d;">
                            x<?php echo $item['so_luong']; ?>
                        </div>
                    </div>
                    <div><strong><?php echo formatPrice($item['gia'] * $item['so_luong']); ?></strong></div>
                </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e1e8ed;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Tạm tính:</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                </div>
                
                <div class="summary-total">
                    <span>Tổng cộng:</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>