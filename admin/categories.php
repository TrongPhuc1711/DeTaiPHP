<?php
require_once '../config.php';
requireAdmin();
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Xử lý thêm/sửa/xóa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        try {
            $stmt = $conn->prepare("INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES (:ten, :mo_ta)");
            $stmt->execute([
                ':ten' => sanitize($_POST['ten_danh_muc']),
                ':mo_ta' => sanitize($_POST['mo_ta'])
            ]);
            $message = 'Thêm danh mục thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action == 'edit') {
        try {
            $stmt = $conn->prepare("UPDATE danh_muc SET ten_danh_muc=:ten, mo_ta=:mo_ta WHERE id=:id");
            $stmt->execute([
                ':id' => $_POST['id'],
                ':ten' => sanitize($_POST['ten_danh_muc']),
                ':mo_ta' => sanitize($_POST['mo_ta'])
            ]);
            $message = 'Cập nhật danh mục thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action == 'delete') {
        try {
            $stmt = $conn->prepare("DELETE FROM danh_muc WHERE id=:id");
            $stmt->execute([':id' => $_POST['id']]);
            $message = 'Xóa danh mục thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Không thể xóa danh mục (có thể đang có sản phẩm)!';
            $messageType = 'danger';
        }
    }
}

// Lấy danh sách danh mục
$categories = $conn->query("SELECT dm.*, COUNT(sp.id) as product_count 
                           FROM danh_muc dm 
                           LEFT JOIN san_pham sp ON dm.id = sp.danh_muc_id 
                           GROUP BY dm.id 
                           ORDER BY dm.ten_danh_muc")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục</title>
    <link rel="stylesheet" href="../css/admin/categories.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>📁 Quản Lý Danh Mục</h1>
        </div>
    </header>
    
    <div class="container">
        <nav>
            <ul>
                <li><a href="index.php">🏠 Dashboard</a></li>
                <li><a href="products.php">📦 Sản phẩm</a></li>
                <li><a href="categories.php">📁 Danh mục</a></li>
                <li><a href="orders.php">🛒 Đơn hàng</a></li>
                <li><a href="../index.php">🏪 Cửa hàng</a></li>
            </ul>
        </nav>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Danh Sách Danh Mục</h2>
                <button class="btn btn-primary" onclick="openAddModal()">➕ Thêm danh mục</button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả</th>
                        <th>Số sản phẩm</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($cat['ten_danh_muc']); ?></strong></td>
                        <td><?php echo htmlspecialchars($cat['mo_ta'] ?? 'N/A'); ?></td>
                        <td><?php echo $cat['product_count']; ?> sản phẩm</td>
                        <td><?php echo date('d/m/Y', strtotime($cat['ngay_tao'])); ?></td>
                        <td>
                            <button class="btn btn-warning" onclick='editCategory(<?php echo json_encode($cat); ?>)'>✏️</button>
                            <button class="btn btn-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Thêm/Sửa -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Thêm Danh Mục</h2>
            <form method="POST" id="categoryForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="categoryId">
                
                <div class="form-group">
                    <label>Tên danh mục *</label>
                    <input type="text" name="ten_danh_muc" id="ten_danh_muc" required>
                </div>
                
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="mo_ta" id="mo_ta"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">💾 Lưu</button>
                    <button type="button" class="btn" style="background: #6c757d; color: white;" onclick="closeModal()">❌ Hủy</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../js/admin/categories.js"></script>
</body>
</html>