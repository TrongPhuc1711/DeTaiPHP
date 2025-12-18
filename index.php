<?php
require_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

// Lấy danh mục
$categories = $conn->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();


// --- XỬ LÝ PHÂN TRANG & LỌC SẢN PHẨM ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4; // Số sản phẩm mỗi trang
$offset = ($page - 1) * $limit;

$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$sql_base = " FROM san_pham sp LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id WHERE sp.so_luong > 0";
$params = [];

if ($category_filter) {
    $sql_base .= " AND sp.danh_muc_id = :category";
    $params[':category'] = $category_filter;
}

if ($search) {
    $sql_base .= " AND sp.ten_san_pham LIKE :search";
    $params[':search'] = "%$search%";
}

// 2. Đếm tổng số sản phẩm (để tính số trang)
$sql_count = "SELECT COUNT(*) " . $sql_base;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_products = $stmt_count->fetchColumn();
$total_pages = ceil($total_products / $limit);

// 3. Lấy dữ liệu sản phẩm cho trang hiện tại
$sql_data = "SELECT sp.*, dm.ten_danh_muc " . $sql_base . " ORDER BY sp.id DESC LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql_data);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
// Lưu ý: Limit và Offset phải bind theo kiểu INT
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
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
    <link rel="stylesheet" href="./css/index.css">
</head>

<body>

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

    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">🥤</div>
                <div class="logo-text">
                    <h1>Thi thu nam - Ca 4 - Nguyen Hoang Trong Phuc</h1>
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

                <a href="labs.php" class="lab-btn" style="text-decoration: none;">
                    📚 Lab thực hành
                </a>
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

    <?php if (!isset($_GET['search']) && !isset($_GET['category'])): ?>
        <div class="banner">
            <div class="banner-content">
                <h2>Thi thu nam- ca 4 - Nguyen Hoang Trong Phuc</h2>
                <p>Đa dạng sản phẩm - Giá cả hợp lý - Uy tín hàng đầu</p>
                <div class="banner-features">
                    <div class="feature-item"><span>🚚</span><span>Giao hàng tận nơi</span></div>
                    <div class="feature-item"><span>💰</span><span>Giá tốt nhất</span></div>
                    <div class="feature-item"><span>✓</span><span>Chất lượng đảm bảo</span></div>
                    <div class="feature-item"><span>⚡</span><span>Giao hàng nhanh</span></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="category-filter">
            <h3>📁 Danh Mục Sản Phẩm</h3>
            <div class="category-grid">
                <a href="index.php" class="category-item <?php echo !$category_filter ? 'active' : ''; ?>">Tất cả</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?php echo $cat['id']; ?>"
                        class="category-item <?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

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
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Tạo chuỗi query string để giữ lại bộ lọc khi chuyển trang
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $link = "?" . ($queryString ? $queryString . "&" : "") . "page=";
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="<?php echo $link . ($page - 1); ?>" class="page-link">«</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?php echo $link . $i; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo $link . ($page + 1); ?>" class="page-link">»</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>


        <?php else: ?>
            <div class="empty-message">
                <h2>Không tìm thấy sản phẩm</h2>
                <p>Vui lòng thử lại với từ khóa khác</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Xem tất cả sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>

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

    <div id="toast" class="toast">
        <button class="toast-close" onclick="closeToast()">&times;</button>
        <div class="toast-header">
            <span class="toast-icon" id="toastIcon">✓</span>
            <span class="toast-title" id="toastTitle">Thành công!</span>
        </div>
        <div class="toast-message" id="toastMessage"></div>
    </div>

    <script src="js/admin/index.js"></script>
</body>

</html>