<?php
require_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

// Lấy danh mục
$categories = $conn->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

// Lọc sản phẩm
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT sp.*, dm.ten_danh_muc FROM san_pham sp 
        LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
        WHERE sp.so_luong > 0";
$params = [];

if ($category_filter) {
    $sql .= " AND sp.danh_muc_id = :category";
    $params[':category'] = $category_filter;
}

if ($search) {
    $sql .= " AND sp.ten_san_pham LIKE :search";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY sp.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$cartCount = getCartCount();

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng nước uống - Giao hàng tận nơi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        /* Top Bar */
        .top-bar {
            background: #2c3e50;
            color: white;
            padding: 10px 0;
            font-size: 0.9em;
        }
        
        .top-bar-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .top-bar-left {
            display: flex;
            gap: 20px;
        }
        
        .top-bar-left span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #2c3e50;
        }
        
        .logo-icon {
            font-size: 3em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo-text h1 {
            font-size: 1.8em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo-text p {
            font-size: 0.9em;
            color: #7f8c8d;
        }
        
        .header-search {
            flex: 1;
            max-width: 500px;
            position: relative;
        }
        
        .search-form {
            display: flex;
            gap: 5px;
        }
        
        .search-form input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 25px;
            font-size: 1em;
            transition: all 0.3s;
        }
        
        .search-form input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .cart-btn {
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* Banner */
        .banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .banner-content {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .banner h2 {
            font-size: 3em;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .banner p {
            font-size: 1.3em;
            margin-bottom: 30px;
            opacity: 0.95;
        }
        
        .banner-features {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1em;
        }
        
        .feature-item span {
            font-size: 1.5em;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Category Filter */
        .category-filter {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .category-filter h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.5em;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .category-item {
            padding: 15px 20px;
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .category-item:hover, .category-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        /* Products Section */
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .products-header h3 {
            font-size: 2em;
            color: #2c3e50;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
            overflow: hidden;
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff4757;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.3em;
            font-weight: 600;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-category {
            color: #667eea;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .product-name {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 12px;
            color: #2c3e50;
            min-height: 50px;
        }
        
        .product-price {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .product-stock {
            font-size: 0.9em;
            color: #27ae60;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-add-cart {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-add-cart:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 60px 20px 20px;
            margin-top: 80px;
        }
        
        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .footer-section p, .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
            transition: all 0.3s;
        }
        
        .footer-section a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: #667eea;
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 30px;
            text-align: center;
            color: #bdc3c7;
        }
        
        /* Toast */
        .toast {
            position: fixed;
            top: 100px;
            right: 30px;
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            display: none;
            z-index: 10000;
            min-width: 300px;
            animation: slideInRight 0.4s ease-out;
            border-left: 5px solid #28a745;
        }
        
        .toast.show { display: block; }
        .toast.success { border-left-color: #28a745; }
        .toast.error { border-left-color: #dc3545; }
        
        .toast-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .toast-icon { font-size: 24px; }
        
        .toast-title {
            font-weight: 600;
            font-size: 1.1em;
            color: #2c3e50;
        }
        
        .toast-message {
            color: #555;
            font-size: 0.95em;
        }
        
        .toast-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }
        
        .toast-close:hover { color: #333; }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast.hide {
            animation: slideOutRight 0.4s ease-out;
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .empty-message {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
        }
        
        .empty-message h2 {
            color: #7f8c8d;
            margin-bottom: 20px;
            font-size: 2em;
        }
        
        @media (max-width: 768px) {
            .banner h2 { font-size: 2em; }
            .banner p { font-size: 1em; }
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            .header-search { width: 100%; max-width: none; }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="top-bar-left">
                <span>📞 Hotline: 0377.819.348</span>
                <span>📧 Email: trongphuc171104@gmail.com</span>
            </div>
            <div>
                <?php if (isLoggedIn()): ?>
                    <span>Xin chào, <strong><?php echo $_SESSION['ho_ten']; ?></strong></span>
                <?php else: ?>
                    <span>Miễn phí giao hàng đơn từ 100.000đ</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">🥤</div>
                <div class="logo-text">
                    <h1>DrinkShop</h1>
                    <p>Nước uống chất lượng</p>
                </div>
            </a>
            
            <div class="header-search">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">🔍 Tìm</button>
                </form>
            </div>
            
            <div class="header-actions">
                <?php if (isLoggedIn()): ?>
                    <a href="cart.php" class="cart-btn">
                        🛒 Giỏ hàng
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline">⚙️ Quản lý</a>
                    <?php endif; ?>
                    <a href="my-orders.php" class="btn btn-outline">📦 Đơn hàng</a>
                    <a href="logout.php" class="btn btn-outline">Đăng xuất</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Đăng nhập</a>
                    <a href="register.php" class="btn btn-outline">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Banner -->
    <?php if (!isset($_GET['search']) && !isset($_GET['category'])): ?>
    <div class="banner">
        <div class="banner-content">
            <h2>🎉 Nước Uống Chất Lượng - Giao Hàng Nhanh Chóng</h2>
            <p>Đa dạng sản phẩm - Giá cả hợp lý - Uy tín hàng đầu</p>
            <div class="banner-features">
                <div class="feature-item">
                    <span>🚚</span>
                    <span>Giao hàng tận nơi</span>
                </div>
                <div class="feature-item">
                    <span>💰</span>
                    <span>Giá tốt nhất</span>
                </div>
                <div class="feature-item">
                    <span>✓</span>
                    <span>Chất lượng đảm bảo</span>
                </div>
                <div class="feature-item">
                    <span>⚡</span>
                    <span>Giao hàng nhanh</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <!-- Category Filter -->
        <div class="category-filter">
            <h3>📁 Danh Mục Sản Phẩm</h3>
            <div class="category-grid">
                <a href="index.php" class="category-item <?php echo !$category_filter ? 'active' : ''; ?>">
                    Tất cả
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo $cat['id']; ?>" 
                   class="category-item <?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Products -->
        <div class="products-header">
            <h3>🔥 Sản Phẩm Nổi Bật</h3>
            <?php if ($search || $category_filter): ?>
            <a href="index.php" class="btn btn-outline">✕ Xóa bộ lọc</a>
            <?php endif; ?>
        </div>
        
        <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['hinh_anh']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['hinh_anh']); ?>" 
                             alt="<?php echo htmlspecialchars($product['ten_san_pham']); ?>">
                    <?php else: ?>
                        🥤
                    <?php endif; ?>
                    <?php if ($product['so_luong'] < 20): ?>
                    <span class="product-badge">Sắp hết</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <div class="product-category">📁 <?php echo htmlspecialchars($product['ten_danh_muc'] ?? 'Chưa phân loại'); ?></div>
                    <div class="product-name"><?php echo htmlspecialchars($product['ten_san_pham']); ?></div>
                    <div class="product-price"><?php echo formatPrice($product['gia']); ?></div>
                    <div class="product-stock">✓ Còn lại: <?php echo $product['so_luong']; ?> <?php echo $product['don_vi']; ?></div>
                    
                    <?php if (isLoggedIn()): ?>
                    <button type="button" class="btn-add-cart" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['ten_san_pham']); ?>')">
                        🛒 Thêm vào giỏ
                    </button>
                    <?php else: ?>
                    <a href="login.php" class="btn-add-cart" style="text-align: center; display: block; text-decoration: none;">
                        Đăng nhập để mua
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-message">
            <h2>Không tìm thấy sản phẩm</h2>
            <p>Vui lòng thử lại với từ khóa khác</p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Xem tất cả sản phẩm</a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Về Chúng Tôi</h3>
                <p>DrinkShop - Cung cấp các loại nước uống chất lượng cao với giá cả hợp lý. Cam kết sản phẩm chính hãng, giao hàng nhanh chóng.</p>
                <div class="social-links">
                    <a href="#" title="Facebook">📘</a>
                    <a href="#" title="Instagram">📷</a>
                    <a href="#" title="Zalo">💬</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Liên Hệ</h3>
                <p>📍 Địa chỉ: 123 Đường ABC, Quận 8, TP.HCM</p>
                <p>📞 Hotline: 0377.819.348</p>
                <p>📧 Email: trongphuc171104@gmail.com</p>
                <p>⏰ Giờ làm việc: 8:00 - 22:00 (Hàng ngày)</p>
            </div>
            
            <div class="footer-section">
                <h3>Chính Sách</h3>
                <a href="#">Chính sách giao hàng</a>
                <a href="#">Chính sách đổi trả</a>
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Điều khoản sử dụng</a>
            </div>
            
            <div class="footer-section">
                <h3>Hỗ Trợ</h3>
                <a href="#">Hướng dẫn đặt hàng</a>
                <a href="#">Phương thức thanh toán</a>
                <a href="#">Câu hỏi thường gặp</a>
                <a href="#">Liên hệ hỗ trợ</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 DrinkShop. All rights reserved. Designed with ❤️</p>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-header">
            <span class="toast-icon" id="toastIcon">✓</span>
            <span class="toast-title" id="toastTitle">Thành công!</span>
        </div>
        <div class="toast-message" id="toastMessage"></div>
    </div>
    
    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const title = document.getElementById('toastTitle');
            const messageEl = document.getElementById('toastMessage');
            
            toast.classList.remove('success', 'error', 'show', 'hide');
            
            if (type === 'success') {
                toast.classList.add('success');
                icon.textContent = '✓';
                title.textContent = 'Thành công!';
            } else {
                toast.classList.add('error');
                icon.textContent = '✕';
                title.textContent = 'Lỗi!';
            }
            
            messageEl.textContent = message;
            toast.classList.add('show');
            
            setTimeout(() => {
                closeToast();
            }, 3000);
        }
        
        function closeToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('hide');
            setTimeout(() => {
                toast.classList.remove('show', 'hide');
            }, 400);
        }
        
        function updateCartCount(count) {
            const cartCountEl = document.querySelector('.cart-count');
            if (cartCountEl) {
                cartCountEl.textContent = count;
            } else if (count > 0) {
                const cartBtn = document.querySelector('.cart-btn');
                if (cartBtn) {
                    const badge = document.createElement('span');
                    badge.className = 'cart-count';
                    badge.textContent = count;
                    cartBtn.appendChild(badge);
                }
            }
        }
        
        function addToCart(productId, productName) {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Đang thêm...';
            btn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            
            fetch('cart-action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                if (data.success) {
                    showToast(data.message, 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            });
        }
    </script>
</body>
</html>