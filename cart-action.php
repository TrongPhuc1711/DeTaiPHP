<?php
require_once 'config.php';
requireLogin();

// Xóa bất kỳ output buffer nào
ob_start();

$db = new Database();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];


$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';


if ($action == 'add') {

    
    $product_id = $_POST['product_id'] ?? null;
    
    if (!$product_id) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        // 1. Kiểm tra sản phẩm còn hàng
        $stmt = $conn->prepare("SELECT ten_san_pham, so_luong FROM san_pham WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product || $product['so_luong'] <= 0) {
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hàng!'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 2. Kiểm tra xem đã có trong giỏ chưa
        $stmt = $conn->prepare("SELECT id, so_luong FROM gio_hang WHERE nguoi_dung_id = ? AND san_pham_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $new_quantity = $existing['so_luong'] + 1;
            
            // 3. Kiểm tra số lượng
            if ($new_quantity > $product['so_luong']) {
                $message = 'Không thể thêm! Chỉ còn ' . $product['so_luong'] . ' sản phẩm trong kho.';
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 4. Cập nhật số lượng
            $stmt = $conn->prepare("UPDATE gio_hang SET so_luong = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing['id']]);
            $message = 'Đã thêm "' . $product['ten_san_pham'] . '" vào giỏ hàng! (Số lượng: ' . $new_quantity . ')';
            
        } else {
            // 5. Thêm mới
            $stmt = $conn->prepare("INSERT INTO gio_hang (nguoi_dung_id, san_pham_id, so_luong) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product_id]);
            $message = 'Đã thêm "' . $product['ten_san_pham'] . '" vào giỏ hàng!';
        }
        
        // 6. Lấy tổng số lượng giỏ hàng
        $cart_count = getCartCount();
        
        // 7. Luôn trả về JSON thành công
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'cart_count' => $cart_count
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}


if ($action == 'update') {
    $cart_id = $_POST['cart_id'] ?? null;
    $quantity = max(1, intval($_POST['quantity'] ?? 0));
    
    if (!$cart_id || $quantity <= 0) {
        echo 'Error: Dữ liệu không hợp lệ.';
        exit;
    }

    try {
        // Lấy thông tin sản phẩm
        $stmt = $conn->prepare("SELECT sp.ten_san_pham, sp.so_luong 
                                FROM gio_hang gh 
                                JOIN san_pham sp ON gh.san_pham_id = sp.id 
                                WHERE gh.id = ? AND gh.nguoi_dung_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            echo 'Error: Không tìm thấy sản phẩm trong giỏ hàng.';
            exit;
        }

        if ($quantity > $item['so_luong']) {
            echo 'Error: Số lượng vượt quá tồn kho! Chỉ còn ' . $item['so_luong'] . ' sản phẩm.';
            exit;
        }
        
        // Cập nhật
        $stmt = $conn->prepare("UPDATE gio_hang SET so_luong = ? WHERE id = ? AND nguoi_dung_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
        
        echo 'Success';
        exit;
        
    } catch (PDOException $e) {
        echo "Error: Lỗi CSDL - Vui lòng thử lại.";
        exit;
    }
}


if ($action == 'delete') {
    $cart_id = $_POST['cart_id'] ?? null;

    if (!$cart_id) {
        echo 'Error: Thiếu ID mục giỏ hàng.';
        exit;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM gio_hang WHERE id = ? AND nguoi_dung_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo 'Success';
        } else {
            echo 'Error: Không tìm thấy mục để xóa.';
        }
        exit;

    } catch (PDOException $e) {
        echo 'Error: Lỗi CSDL - Vui lòng thử lại.';
        exit;
    }
}

// Mặc định
redirect('index.php');
?>