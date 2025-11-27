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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .login-header p {
            opacity: 0.9;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .demo-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .demo-info strong {
            color: #856404;
        }
    </style>
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