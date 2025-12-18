<?php
require_once '../config.php';
requireAdmin();
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Xử lý thêm đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_order') {
        try {
            $conn->beginTransaction();
            
            // Thêm đơn hàng
            $stmt = $conn->prepare("INSERT INTO don_hang (ten_khach_hang, so_dien_thoai, dia_chi, tong_tien, ghi_chu) 
                                   VALUES (:ten, :sdt, :dia_chi, :tong_tien, :ghi_chu)");
            $stmt->execute([
                ':ten' => sanitize($_POST['ten_khach_hang']),
                ':sdt' => sanitize($_POST['so_dien_thoai']),
                ':dia_chi' => sanitize($_POST['dia_chi']),
                ':tong_tien' => $_POST['tong_tien'],
                ':ghi_chu' => sanitize($_POST['ghi_chu'])
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Thêm chi tiết đơn hàng
            $products = json_decode($_POST['products'], true);
            $stmt = $conn->prepare("INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia, thanh_tien) 
                                   VALUES (:order_id, :product_id, :quantity, :price, :total)");
            
            $updateStock = $conn->prepare("UPDATE san_pham SET so_luong = so_luong - :quantity WHERE id = :id");
            
            foreach ($products as $product) {
                $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $product['id'],
                    ':quantity' => $product['quantity'],
                    ':price' => $product['price'],
                    ':total' => $product['quantity'] * $product['price']
                ]);
                
                // Cập nhật tồn kho
                $updateStock->execute([
                    ':quantity' => $product['quantity'],
                    ':id' => $product['id']
                ]);
            }
            
            $conn->commit();
            $message = 'Thêm đơn hàng thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $conn->rollBack();
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action == 'update_status') {
        try {
            $stmt = $conn->prepare("UPDATE don_hang SET trang_thai = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $_POST['status'],
                ':id' => $_POST['order_id']
            ]);
            $message = 'Cập nhật trạng thái thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action == 'delete') {
        try {
            $stmt = $conn->prepare("DELETE FROM don_hang WHERE id = :id");
            $stmt->execute([':id' => $_POST['order_id']]);
            $message = 'Xóa đơn hàng thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM don_hang WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (ten_khach_hang LIKE :search OR so_dien_thoai LIKE :search OR id = :id)";
    $params[':search'] = "%$search%";
    $params[':id'] = is_numeric($search) ? $search : 0;
}

if ($status_filter) {
    $sql .= " AND trang_thai = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY ngay_tao DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Lấy danh sách sản phẩm cho form tạo đơn
$products = $conn->query("SELECT * FROM san_pham WHERE so_luong > 0 ORDER BY ten_san_pham")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="../css/admin/orders.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🛒 Quản Lý Đơn Hàng</h1>
        </div>
    </header>
    
    <div class="container">
        <nav>
            <ul>
                <li><a href="index.php">🏠 Trang chủ</a></li>
                <li><a href="products.php">📦 Sản phẩm</a></li>
                <li><a href="categories.php">📁 Danh mục</a></li>
                <li><a href="orders.php">🛒 Đơn hàng</a></li>
            </ul>
        </nav>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Danh Sách Đơn Hàng</h2>
                <button class="btn btn-primary" onclick="openAddModal()">➕ Tạo đơn hàng</button>
            </div>
            
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="🔍 Tìm theo tên, SĐT, mã đơn..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="cho_xac_nhan" <?php echo $status_filter == 'cho_xac_nhan' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="dang_giao" <?php echo $status_filter == 'dang_giao' ? 'selected' : ''; ?>>Đang giao</option>
                    <option value="hoan_thanh" <?php echo $status_filter == 'hoan_thanh' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="huy" <?php echo $status_filter == 'huy' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                <a href="orders.php" class="btn" style="background: #6c757d; color: white;">Reset</a>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['ten_khach_hang']); ?></td>
                        <td><?php echo htmlspecialchars($order['so_dien_thoai']); ?></td>
                        <td><strong><?php echo formatPrice($order['tong_tien']); ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo getStatusClass($order['trang_thai']); ?>">
                                <?php echo getStatusText($order['trang_thai']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['ngay_tao'])); ?></td>
                        <td>
                            <button class="btn btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">👁️</button>
                            
                            <button class="btn btn-warning" onclick="copyConfirmMsg('<?php echo $order['id']; ?>', '<?php echo formatPrice($order['tong_tien']); ?>')">📋 SMS</button>
                            
                            <?php if ($order['trang_thai'] != 'hoan_thanh' && $order['trang_thai'] != 'huy'): ?>
                            <button class="btn btn-success" onclick="updateStatus(<?php echo $order['id']; ?>, 'next')">✓</button>
                            <?php endif; ?>
                            <button class="btn btn-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Tạo Đơn Hàng Mới</h2>
            <form method="POST" id="orderForm">
                <input type="hidden" name="action" value="add_order">
                <input type="hidden" name="products" id="productsData">
                <input type="hidden" name="tong_tien" id="tongTien">
                
                <div class="form-group">
                    <label>Tên khách hàng *</label>
                    <input type="text" name="ten_khach_hang" required>
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input type="text" name="so_dien_thoai" required>
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="dia_chi">
                </div>
                
                <div class="form-group">
                    <label>Sản phẩm *</label>
                    <div id="productsList"></div>
                    <button type="button" class="btn btn-success" onclick="addProductRow()">➕ Thêm sản phẩm</button>
                </div>
                
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="ghi_chu"></textarea>
                </div>
                
                <div id="orderTotal">Tổng tiền: 0đ</div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">💾 Tạo đơn</button>
                    <button type="button" class="btn" style="background: #6c757d; color: white;" onclick="closeModal()">❌ Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const products = <?php echo json_encode($products); ?>;
        let productRows = [];
    </script>
    <script src="../js/admin/orders.js"></script>
</body>
</html>