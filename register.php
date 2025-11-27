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
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .register-body {
            padding: 30px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
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
        
        .register-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
    </style>
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