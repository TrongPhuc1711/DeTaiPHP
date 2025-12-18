<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $username = sanitize($_POST['ten_dang_nhap']);
    $password = $_POST['mat_khau'];
    
    $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE ten_dang_nhap = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['mat_khau'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ho_ten'] = $user['ho_ten'];
        $_SESSION['vai_tro'] = $user['vai_tro'];
        
        if ($user['vai_tro'] == 'admin') {
            redirect('admin/index.php');
        } else {
            redirect('index.php');
        }
    } else {
        $message = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🥤 Đăng Nhập</h1>
            <p>Hệ thống quản lý bán nước</p>
        </div>
        
        <div class="login-body">
            <?php if ($message): ?>
            <div class="alert">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="demo-info">
                <strong>Tài khoản demo:</strong><br>
                Admin: <strong>admin</strong> / <strong>123456</strong><br>
                Khách: <strong>customer1</strong> / <strong>123456</strong>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>👤 Tên đăng nhập</label>
                    <input type="text" name="ten_dang_nhap" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>🔒 Mật khẩu</label>
                    <input type="password" name="mat_khau" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Đăng nhập</button>
            </form>
        </div>
        
        <div class="login-footer">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
</body>
</html>