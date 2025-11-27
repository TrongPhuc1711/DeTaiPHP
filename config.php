<?php
session_start();

// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_NAME', 'quan_ly_ban_nuoc');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Lỗi kết nối: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm kiểm tra admin
function isAdmin() {
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] == 'admin';
}

// Hàm yêu cầu đăng nhập
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Hàm yêu cầu quyền admin
function requireAdmin() {
    if (!isAdmin()) {
        redirect('index.php');
    }
}

// Hàm lấy thông tin user hiện tại
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Hàm đếm số lượng giỏ hàng
function getCartCount() {
    if (!isLoggedIn()) return 0;
    
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT SUM(so_luong) as total FROM gio_hang WHERE nguoi_dung_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

// Hàm helper
function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

function getStatusText($status) {
    $statuses = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'dang_giao' => 'Đang giao',
        'hoan_thanh' => 'Hoàn thành',
        'huy' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}

function getStatusClass($status) {
    $classes = [
        'cho_xac_nhan' => 'warning',
        'dang_giao' => 'info',
        'hoan_thanh' => 'success',
        'huy' => 'danger'
    ];
    return $classes[$status] ?? 'secondary';
}
?>