<?php
require_once '../config.php';
requireAdmin();
$db = new Database();
$conn = $db->getConnection();

// Thống kê tổng quan
$stats = [
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM san_pham")->fetch()['count'],
    'total_categories' => $conn->query("SELECT COUNT(*) as count FROM danh_muc")->fetch()['count'],
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM don_hang")->fetch()['count'],
    'total_revenue' => $conn->query("SELECT SUM(tong_tien) as total FROM don_hang WHERE trang_thai = 'hoan_thanh'")->fetch()['total'] ?? 0,
    'pending_orders' => $conn->query("SELECT COUNT(*) as count FROM don_hang WHERE trang_thai = 'cho_xac_nhan'")->fetch()['count'],
    'low_stock' => $conn->query("SELECT COUNT(*) as count FROM san_pham WHERE so_luong < 20")->fetch()['count']
];

// Đơn hàng gần đây
$recent_orders = $conn->query("SELECT * FROM don_hang ORDER BY ngay_tao DESC LIMIT 5")->fetchAll();

// Sản phẩm bán chạy
$top_products = $conn->query("
    SELECT sp.ten_san_pham, SUM(ct.so_luong) as total_sold, SUM(ct.thanh_tien) as revenue
    FROM chi_tiet_don_hang ct
    JOIN san_pham sp ON ct.san_pham_id = sp.id
    GROUP BY sp.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin/index.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🎛️ Admin Dashboard</h1>
        </div>
    </header>
    
    <div class="container">
        <nav>
            <ul>
                <li><a href="index.php">🏠 Dashboard</a></li>
                <li><a href="products.php">📦 Sản phẩm</a></li>
                <li><a href="categories.php">📁 Danh mục</a></li>
                <li><a href="orders.php">🛒 Đơn hàng</a></li>
                <li><a href="../index.php">🏪 Về cửa hàng</a></li>
                <li><a href="../logout.php">🚪 Đăng xuất</a></li>
            </ul>
        </nav>
        
        <!-- Thống kê tổng quan -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Sản phẩm</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-value"><?php echo $stats['total_categories']; ?></div>
                <div class="stat-label">Danh mục</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Đơn hàng</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value"><?php echo number_format($stats['total_revenue']/1000000, 1); ?>M</div>
                <div class="stat-label">Doanh thu</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Chờ xác nhận</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?php echo $stats['low_stock']; ?></div>
                <div class="stat-label">Sắp hết hàng</div>
            </div>
        </div>
        
        <!-- Đơn hàng gần đây -->
        <div class="card">
            <h2>📋 Đơn Hàng Gần Đây</h2>
            <?php if (count($recent_orders) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['ten_khach_hang']); ?></td>
                        <td><strong><?php echo formatPrice($order['tong_tien']); ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo getStatusClass($order['trang_thai']); ?>">
                                <?php echo getStatusText($order['trang_thai']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['ngay_tao'])); ?></td>
                        <td>
                            <a href="orders.php" class="btn btn-primary">Xem</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #7f8c8d;">Chưa có đơn hàng nào</p>
            <?php endif; ?>
        </div>
        
        <!-- Sản phẩm bán chạy -->
        <div class="card">
            <h2>🔥 Sản Phẩm Bán Chạy</h2>
            <?php if (count($top_products) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đã bán</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_products as $product): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($product['ten_san_pham']); ?></strong></td>
                        <td><?php echo $product['total_sold']; ?> sản phẩm</td>
                        <td><strong style="color: #667eea;"><?php echo formatPrice($product['revenue']); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #7f8c8d;">Chưa có dữ liệu bán hàng</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>