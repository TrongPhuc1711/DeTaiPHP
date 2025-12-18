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
    $confirm_password = $_POST['xac_nhan_mat_khau'];
    $fullname = sanitize($_POST['ho_ten']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['so_dien_thoai']);
    
    // Validate
    if ($password !== $confirm_password) {
        $message = 'Mật khẩu xác nhận không khớp!';
        $messageType = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Mật khẩu phải có ít nhất 6 ký tự!';
        $messageType = 'danger';
    } else {
        // Kiểm tra username đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $message = 'Tên đăng nhập đã tồn tại!';
            $messageType = 'danger';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email, so_dien_thoai, vai_tro) 
                                       VALUES (?, ?, ?, ?, ?, 'customer')");
                $stmt->execute([$username, $hashed_password, $fullname, $email, $phone]);
                
                $message = 'Đăng ký thành công! Vui lòng đăng nhập.';
                $messageType = 'success';
                
                // Chuyển hướng sau 2 giây
                header("refresh:2;url=login.php");
            } catch (PDOException $e) {
                $message = 'Lỗi: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="./css/register.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>📝 Đăng Ký</h1>
            <p>Tạo tài khoản mới</p>
        </div>
        
        <div class="register-body">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>👤 Tên đăng nhập *</label>
                    <input type="text" name="ten_dang_nhap" required>
                </div>
                
                <div class="form-group">
                    <label>👨 Họ và tên *</label>
                    <input type="text" name="ho_ten" required>
                </div>
                
                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email">
                </div>
                
                <div class="form-group">
                    <label>📱 Số điện thoại</label>
                    <input type="text" name="so_dien_thoai">
                </div>
                
                <div class="form-group">
                    <label>🔒 Mật khẩu * (tối thiểu 6 ký tự)</label>
                    <input type="password" name="mat_khau" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>🔒 Xác nhận mật khẩu *</label>
                    <input type="password" name="xac_nhan_mat_khau" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Đăng ký</button>
            </form>
        </div>
        
        <div class="register-footer">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </div>
    </div>
</body>
</html>