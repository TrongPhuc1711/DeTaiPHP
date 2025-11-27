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
        
        nav a:hover { background: #667eea; color: white; transform: translateY(-2px); }
        
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
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #000; }
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
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            margin: 100px auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1em;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover { color: #000; }
    </style>
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
    
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm Danh Mục';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').style.display = 'block';
        }
        
        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Sửa Danh Mục';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('ten_danh_muc').value = category.ten_danh_muc;
            document.getElementById('mo_ta').value = category.mo_ta || '';
            document.getElementById('categoryModal').style.display = 'block';
        }
        
        function deleteCategory(id) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>