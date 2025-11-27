<?php
require_once '../config.php';
requireAdmin();
$db = new Database();
$conn = $db->getConnection();

// Tạo thư mục uploads nếu chưa có
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Xử lý thêm/sửa/xóa
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        try {
            $hinh_anh = '';
            
            // Xử lý upload file
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
                $file_tmp = $_FILES['hinh_anh']['tmp_name'];
                $file_name = $_FILES['hinh_anh']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png'];
                
                if (in_array($file_ext, $allowed_ext)) {
                    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                    $destination = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $hinh_anh = $new_file_name;
                    }
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO san_pham (ten_san_pham, danh_muc_id, gia, so_luong, don_vi, mo_ta, hinh_anh) 
                                   VALUES (:ten, :danh_muc, :gia, :so_luong, :don_vi, :mo_ta, :hinh_anh)");
            $stmt->execute([
                ':ten' => sanitize($_POST['ten_san_pham']),
                ':danh_muc' => $_POST['danh_muc_id'],
                ':gia' => $_POST['gia'],
                ':so_luong' => $_POST['so_luong'],
                ':don_vi' => sanitize($_POST['don_vi']),
                ':mo_ta' => sanitize($_POST['mo_ta']),
                ':hinh_anh' => $hinh_anh
            ]);
            $message = 'Thêm sản phẩm thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action == 'edit') {
        try {
            // Lấy thông tin sản phẩm cũ
            $stmt = $conn->prepare("SELECT hinh_anh FROM san_pham WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $old_product = $stmt->fetch();
            
            $hinh_anh = $old_product['hinh_anh'];
            
            // Xử lý upload file mới
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
                $file_tmp = $_FILES['hinh_anh']['tmp_name'];
                $file_name = $_FILES['hinh_anh']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png'];
                
                if (in_array($file_ext, $allowed_ext)) {
                    // Xóa file cũ
                    if ($hinh_anh && file_exists($upload_dir . $hinh_anh)) {
                        unlink($upload_dir . $hinh_anh);
                    }
                    
                    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                    $destination = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $destination)) {
                        $hinh_anh = $new_file_name;
                    }
                }
            }
            
            $stmt = $conn->prepare("UPDATE san_pham SET ten_san_pham=:ten, danh_muc_id=:danh_muc, 
                                   gia=:gia, so_luong=:so_luong, don_vi=:don_vi, mo_ta=:mo_ta, hinh_anh=:hinh_anh 
                                   WHERE id=:id");
            $stmt->execute([
                ':id' => $_POST['id'],
                ':ten' => sanitize($_POST['ten_san_pham']),
                ':danh_muc' => $_POST['danh_muc_id'],
                ':gia' => $_POST['gia'],
                ':so_luong' => $_POST['so_luong'],
                ':don_vi' => sanitize($_POST['don_vi']),
                ':mo_ta' => sanitize($_POST['mo_ta']),
                ':hinh_anh' => $hinh_anh
            ]);
            $message = 'Cập nhật sản phẩm thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action == 'delete') {
        try {
            // Lấy thông tin hình ảnh trước khi xóa
            $stmt = $conn->prepare("SELECT hinh_anh FROM san_pham WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $product = $stmt->fetch();
            
            // Xóa file hình ảnh
            if ($product && $product['hinh_anh'] && file_exists($upload_dir . $product['hinh_anh'])) {
                unlink($upload_dir . $product['hinh_anh']);
            }
            
            $stmt = $conn->prepare("DELETE FROM san_pham WHERE id=:id");
            $stmt->execute([':id' => $_POST['id']]);
            $message = 'Xóa sản phẩm thành công!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Không thể xóa sản phẩm (có thể đang có trong đơn hàng)!';
            $messageType = 'danger';
        }
    }
}

// Tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT sp.*, dm.ten_danh_muc FROM san_pham sp 
        LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND sp.ten_san_pham LIKE :search";
    $params[':search'] = "%$search%";
}

if ($category_filter) {
    $sql .= " AND sp.danh_muc_id = :category";
    $params[':category'] = $category_filter;
}

$sql .= " ORDER BY sp.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh sách danh mục
$categories = $conn->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm</title>
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-danger { background: #dc3545; color: white; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
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
            overflow-y: auto;
        }
        
        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
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
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .badge-low { background: #f8d7da; color: #721c24; }
        .badge-medium { background: #fff3cd; color: #856404; }
        .badge-high { background: #d4edda; color: #155724; }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📦 Quản Lý Sản Phẩm</h1>
        </div>
    </header>
    
    <div class="container">
        <nav>
            <ul>
                <li><a href="index.php">🏠 Trang chủ</a></li>
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
                <h2>Danh Sách Sản Phẩm</h2>
                <button class="btn btn-primary" onclick="openAddModal()">➕ Thêm sản phẩm</button>
            </div>
            
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="🔍 Tìm kiếm sản phẩm..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <select name="category">
                    <option value="">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" 
                            <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                <a href="product.php" class="btn" style="background: #6c757d; color: white;">Reset</a>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['hinh_anh']): ?>
                                <img src="../uploads/<?php echo $product['hinh_anh']; ?>" 
                                     class="product-image" alt="<?php echo htmlspecialchars($product['ten_san_pham']); ?>">
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                            border-radius: 8px; display: flex; align-items: center; justify-content: center; 
                                            font-size: 2em;">🥤</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['ten_san_pham']); ?></td>
                        <td><?php echo htmlspecialchars($product['ten_danh_muc'] ?? 'N/A'); ?></td>
                        <td><?php echo formatPrice($product['gia']); ?></td>
                        <td>
                            <span class="badge <?php 
                                if ($product['so_luong'] < 20) echo 'badge-low';
                                elseif ($product['so_luong'] < 50) echo 'badge-medium';
                                else echo 'badge-high';
                            ?>">
                                <?php echo $product['so_luong']; ?> <?php echo htmlspecialchars($product['don_vi']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-warning" onclick='editProduct(<?php echo json_encode($product); ?>)'>✏️</button>
                            <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Thêm/Sửa -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Thêm Sản Phẩm</h2>
            <form method="POST" id="productForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-group">
                    <label>📸 Hình ảnh</label>
                    <input type="file" name="hinh_anh" id="hinh_anh" accept="image/png,image/jpeg,image/jpg" onchange="previewImage(this)">
                    <img id="imagePreview" class="image-preview">
                    <div id="currentImage"></div>
                </div>
                
                <div class="form-group">
                    <label>Tên sản phẩm *</label>
                    <input type="text" name="ten_san_pham" id="ten_san_pham" required>
                </div>
                
                <div class="form-group">
                    <label>Danh mục *</label>
                    <select name="danh_muc_id" id="danh_muc_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Giá *</label>
                    <input type="number" name="gia" id="gia" min="0" step="1000" required>
                </div>
                
                <div class="form-group">
                    <label>Số lượng *</label>
                    <input type="number" name="so_luong" id="so_luong" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Đơn vị *</label>
                    <input type="text" name="don_vi" id="don_vi" value="chai" required>
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
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm Sản Phẩm';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('currentImage').innerHTML = '';
            document.getElementById('productModal').style.display = 'block';
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Sửa Sản Phẩm';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('productId').value = product.id;
            document.getElementById('ten_san_pham').value = product.ten_san_pham;
            document.getElementById('danh_muc_id').value = product.danh_muc_id;
            document.getElementById('gia').value = product.gia;
            document.getElementById('so_luong').value = product.so_luong;
            document.getElementById('don_vi').value = product.don_vi;
            document.getElementById('mo_ta').value = product.mo_ta || '';
            
            const currentImage = document.getElementById('currentImage');
            if (product.hinh_anh) {
                currentImage.innerHTML = `
                    <div style="margin-top: 10px;">
                        <p style="font-weight: 500; margin-bottom: 5px;">Hình ảnh hiện tại:</p>
                        <img src="../uploads/${product.hinh_anh}" style="max-width: 200px; border-radius: 8px;">
                        <p style="font-size: 0.9em; color: #7f8c8d; margin-top: 5px;">Chọn file mới để thay đổi</p>
                    </div>
                `;
            } else {
                currentImage.innerHTML = '';
            }
            
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('productModal').style.display = 'block';
        }
        
        function deleteProduct(id) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
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
            document.getElementById('productModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>