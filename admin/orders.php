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
        
        nav ul { list-style: none; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
        
        nav a {
            text-decoration: none;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        nav a:hover { background: #667eea; color: white; }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success { background: #d4edda; color: #155724; border-color: #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-color: #dc3545; }
        
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-bar input, .search-bar select {
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
            flex: 1;
            min-width: 200px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .btn-success { background: #28a745; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
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
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-content {
            background: white;
            margin: 30px auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 800px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover { color: #000; }
        
        .product-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .product-item select, .product-item input {
            flex: 1;
        }
        
        #orderTotal {
            font-size: 1.5em;
            color: #667eea;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }
    </style>
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
    
    <!-- Modal Tạo đơn -->
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
        
        function openAddModal() {
            productRows = [];
            document.getElementById('productsList').innerHTML = '';
            document.getElementById('orderForm').reset();
            addProductRow();
            document.getElementById('orderModal').style.display = 'block';
        }
        
        function addProductRow() {
            const index = productRows.length;
            const row = document.createElement('div');
            row.className = 'product-item';
            row.innerHTML = `
                <select onchange="updateTotal()" data-index="${index}">
                    <option value="">-- Chọn sản phẩm --</option>
                    ${products.map(p => `<option value="${p.id}" data-price="${p.gia}">${p.ten_san_pham} - ${formatPrice(p.gia)} (Còn: ${p.so_luong})</option>`).join('')}
                </select>
                <input type="number" min="1" value="1" placeholder="Số lượng" onchange="updateTotal()" data-index="${index}">
                <button type="button" class="btn btn-danger" onclick="removeProductRow(this)">✕</button>
            `;
            document.getElementById('productsList').appendChild(row);
            productRows.push(row);
        }
        
        function removeProductRow(btn) {
            btn.parentElement.remove();
            updateTotal();
        }
        
        function updateTotal() {
            let total = 0;
            const rows = document.querySelectorAll('.product-item');
            const productsData = [];
            
            rows.forEach(row => {
                const select = row.querySelector('select');
                const input = row.querySelector('input');
                const selectedOption = select.options[select.selectedIndex];
                
                if (selectedOption.value) {
                    const price = parseFloat(selectedOption.dataset.price);
                    const quantity = parseInt(input.value) || 0;
                    total += price * quantity;
                    
                    productsData.push({
                        id: selectedOption.value,
                        price: price,
                        quantity: quantity
                    });
                }
            });
            
            document.getElementById('orderTotal').textContent = 'Tổng tiền: ' + formatPrice(total);
            document.getElementById('tongTien').value = total;
            document.getElementById('productsData').value = JSON.stringify(productsData);
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
        }
        
        function viewOrder(id) {
            window.location.href = 'order_detail.php?id=' + id;
        }
        
        function updateStatus(id, action) {
            const statuses = ['cho_xac_nhan', 'dang_giao', 'hoan_thanh'];
            // Logic cập nhật trạng thái kế tiếp
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="${id}">
                <input type="hidden" name="status" value="dang_giao">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteOrder(id) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) closeModal();
        }
    </script>
</body>
</html>