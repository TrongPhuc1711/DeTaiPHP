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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-light { background: white; color: #667eea; }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success { background: #d4edda; color: #155724; border-color: #28a745; }
        
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .order-id {
            font-size: 1.2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-view {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .empty-message {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-message h2 {
            color: #7f8c8d;
            margin-bottom: 20px;
        }
    </style>
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