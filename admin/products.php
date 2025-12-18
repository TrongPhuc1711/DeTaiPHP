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
    <link rel="stylesheet" href="../css/admin/products.css">
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
    
    <!-- Thêm/Sửa -->
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
    
    <script src="../js/admin/products.js"></script>
</body>
</html>