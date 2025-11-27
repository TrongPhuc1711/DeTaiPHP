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
        }
        
        .header h1 { font-size: 1.8em; text-align: center; }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success {
            width: 100%;
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .order-summary {
            position: sticky;
            top: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #e1e8ed;
            margin-top: 15px;
        }
        
        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
    </style>
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