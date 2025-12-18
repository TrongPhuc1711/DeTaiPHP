<?php
require_once 'config.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$stmt = $conn->prepare("SELECT * FROM don_hang WHERE nguoi_dung_id = ? ORDER BY ngay_tao DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi</title>
    <link rel="stylesheet" href="./css/my-orders.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📦 Đơn Hàng Của Tôi</h1>
            <a href="index.php" class="btn btn-light">← Về trang chủ</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-id">Đơn hàng #<?php echo $order['id']; ?></div>
                    <span class="badge badge-<?php echo getStatusClass($order['trang_thai']); ?>">
                        <?php echo getStatusText($order['trang_thai']); ?>
                    </span>
                </div>
                
                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label">👤 Người nhận</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['ten_khach_hang']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📱 Số điện thoại</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['so_dien_thoai']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">💰 Tổng tiền</span>
                        <span class="info-value" style="color: #667eea; font-weight: bold;">
                            <?php echo formatPrice($order['tong_tien']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📅 Ngày đặt</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['ngay_tao'])); ?></span>
                    </div>
                </div>
                
                <?php if ($order['dia_chi']): ?>
                <div class="info-item" style="margin-bottom: 15px;">
                    <span class="info-label">📍 Địa chỉ giao hàng</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['dia_chi']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($order['ghi_chu']): ?>
                <div class="info-item" style="margin-bottom: 15px;">
                    <span class="info-label">📝 Ghi chú</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['ghi_chu']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="order-actions">
                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn-view">
                        Xem chi tiết
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="card">
            <div class="empty-message">
                <h2>📦 Chưa có đơn hàng nào</h2>
                <p>Hãy bắt đầu mua sắm để tạo đơn hàng đầu tiên</p>
                <a href="index.php" class="btn" style="background: #667eea; color: white; margin-top: 20px;">
                    Mua sắm ngay
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>