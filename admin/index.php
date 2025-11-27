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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        header h1 { text-align: center; font-size: 2.5em; }
        
        nav {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        nav ul { 
            list-style: none; 
            display: flex; 
            justify-content: center; 
            gap: 10px; 
            flex-wrap: wrap; 
        }
        
        nav a {
            text-decoration: none;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            display: block;
        }
        
        nav a:hover { 
            background: #667eea; 
            color: white; 
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 1em;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover { background: #f8f9fa; }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
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
        
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
    </style>
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