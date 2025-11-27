<?php
require_once 'config.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Lấy thông tin giỏ hàng
$stmt = $conn->prepare("SELECT gh.*, sp.ten_san_pham, sp.gia, sp.don_vi, sp.so_luong as ton_kho, sp.hinh_anh
                       FROM gio_hang gh
                       JOIN san_pham sp ON gh.san_pham_id = sp.id
                       WHERE gh.nguoi_dung_id = ?
                       ORDER BY gh.ngay_them DESC");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['gia'] * $item['so_luong'];
}

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <style>
        /* ... (Toàn bộ CSS của bạn giữ nguyên ở đây) ... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            font-size: 1.8em;
        }

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

        .btn-light {
            background: white;
            color: #667eea;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-icon {
            font-size: 3em;
            color: white;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .product-stock {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .quantity-control input {
            width: 70px;
            padding: 8px;
            text-align: center;
            border: 2px solid #e1e8ed;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 600;
        }

        .quantity-control button {
            width: 35px;
            height: 35px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            transition: all 0.3s;
        }

        .quantity-control button:hover {
            background: #5568d3;
            transform: scale(1.1);
        }

        .quantity-control button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: scale(1);
        }

        .cart-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .summary-row.total {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
            padding-top: 15px;
            border-top: 2px solid #e1e8ed;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart h2 {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .toast {
            position: fixed;
            top: 100px;
            right: 30px;
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 10000;
            min-width: 300px;
            animation: slideInRight 0.4s ease-out;
        }

        .toast.show {
            display: block;
        }

        .toast.success {
            border-left: 5px solid #28a745;
        }

        .toast.error {
            border-left: 5px solid #dc3545;
        }

        .toast-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .toast-icon {
            font-size: 24px;
        }

        .toast-title {
            font-weight: 600;
            font-size: 1.1em;
            color: #2c3e50;
        }

        .toast-message {
            color: #555;
            font-size: 0.95em;
        }

        .toast-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
        }

        .toast-close:hover {
            color: #333;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast.hide {
            animation: slideOutRight 0.4s ease-out;
        }

        @media (max-width: 768px) {
            table {
                font-size: 0.9em;
            }

            .product-image-wrapper {
                width: 60px;
                height: 60px;
            }

            .product-icon {
                font-size: 2em;
            }

            .toast {
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <div id="toast" class="toast">
        <button class="toast-close" onclick="hideToast()">&times;</button>
        <div class="toast-header">
            <span class="toast-icon" id="toastIcon">✓</span>
            <span class="toast-title" id="toastTitle">Thông báo</span>
        </div>
        <div class="toast-message" id="toastMessage"></div>
    </div>

    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>🛒 Giỏ Hàng</h1>
            </div>
            <a href="index.php" class="btn btn-light">← Tiếp tục mua sắm</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (count($cart_items) > 0): ?>
            <div class="card">
                <h2 style="margin-bottom: 20px;">Sản phẩm trong giỏ</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr id="cart-row-<?php echo $item['id']; ?>">
                                <td>
                                    <div class="product-info">
                                        <div class="product-image-wrapper">
                                            <?php if ($item['hinh_anh'] && file_exists('uploads/' . $item['hinh_anh'])): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($item['hinh_anh']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['ten_san_pham']); ?>"
                                                    class="product-image">
                                            <?php else: ?>
                                                <div class="product-icon">🥤</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-details">
                                            <div class="product-name">
                                                <?php echo htmlspecialchars($item['ten_san_pham']); ?>
                                            </div>
                                            <div class="product-stock">
                                                Còn lại: <?php echo $item['ton_kho']; ?> <?php echo $item['don_vi']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?php echo formatPrice($item['gia']); ?></strong></td>
                                <td>
                                    <div class="quantity-control">
                                        <button type="button"
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, -1, <?php echo $item['so_luong']; ?>, <?php echo $item['ton_kho']; ?>, '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>')">
                                            -
                                        </button>

                                        <input type="number"
                                            id="qty-<?php echo $item['id']; ?>"
                                            value="<?php echo $item['so_luong']; ?>"
                                            min="1"
                                            max="<?php echo $item['ton_kho']; ?>"

                                            data-current-qty="<?php echo $item['so_luong']; ?>"

                                            onchange="updateQuantityManually(
                                                <?php echo $item['id']; ?>,
                                                <?php echo $item['ton_kho']; ?>,
                                                '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>',
                                                this
                                            )"

                                            style="width: 70px; text-align: center;">

                                        <button type="button"
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 1, <?php echo $item['so_luong']; ?>, <?php echo $item['ton_kho']; ?>, '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>')">
                                            +
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: #667eea; font-size: 1.1em;" id="subtotal-<?php echo $item['id']; ?>">
                                        <?php echo formatPrice($item['gia'] * $item['so_luong']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-danger"
                                        onclick="deleteFromCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['ten_san_pham'])); ?>')">
                                        🗑️ Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="cart-summary">
                    <h3 style="margin-bottom: 20px;">Tổng đơn hàng</h3>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span id="cart-subtotal"><?php echo formatPrice($total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span id="cart-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-success" style="width: 100%; text-align: center; margin-top: 20px; display: block;">
                        💳 Thanh toán
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="empty-cart">
                    <h2>🛒 Giỏ hàng trống</h2>
                    <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">
                        Mua sắm ngay
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Khởi tạo giá (giữ nguyên)
        const prices = <?php
                        $price_map = [];
                        foreach ($cart_items as $item) {
                            $price_map[$item['id']] = (float)$item['gia'];
                        }
                        echo json_encode($price_map);
                        ?>;

        // --- Các hàm showToast, hideToast, formatPrice (Giữ nguyên) ---
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toastIcon');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            toast.className = 'toast show ' + type;
            toastIcon.textContent = type === 'success' ? '✓' : '✕';
            toastTitle.textContent = type === 'success' ? 'Thành công' : 'Lỗi';
            toastMessage.textContent = message;
            setTimeout(() => {
                hideToast();
            }, 3000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('hide');
            setTimeout(() => {
                toast.classList.remove('show', 'hide');
            }, 400);
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
        }

        // --- HÀM MỚI: Xử lý khi gõ tay ---
        function updateQuantityManually(cartId, maxQty, productName, inputElement) {
            const newQty = parseInt(inputElement.value);
            // Lấy số lượng "tốt" cuối cùng từ thuộc tính data
            const originalQty = parseInt(inputElement.getAttribute('data-current-qty'));

            // 1. Kiểm tra tính hợp lệ
            if (isNaN(newQty) || newQty < 1) {
                showToast('Số lượng tối thiểu là 1', 'error');
                inputElement.value = originalQty; // Reset về giá trị cũ
                return;
            }

            if (newQty > maxQty) {
                showToast('Chỉ còn ' + maxQty + ' sản phẩm trong kho', 'error');
                inputElement.value = originalQty; // Reset về giá trị cũ
                return;
            }

            // 2. Gửi request (giống hệt hàm updateQuantity)
            sendUpdateRequest(cartId, newQty, originalQty, maxQty, productName, inputElement);
        }

        // --- HÀM CŨ (cho nút +/-): Sửa đổi một chút ---
        function updateQuantity(cartId, change, currentQty, maxQty, productName) {
            const newQty = currentQty + change;
            const qtyInput = document.getElementById('qty-' + cartId);
            const originalQty = parseInt(qtyInput.value); // Lấy giá trị hiện tại

            if (newQty < 1) {
                showToast('Số lượng tối thiểu là 1', 'error');
                return;
            }

            if (newQty > maxQty) {
                showToast('Chỉ còn ' + maxQty + ' sản phẩm trong kho', 'error');
                return;
            }

            // Gửi request
            sendUpdateRequest(cartId, newQty, originalQty, maxQty, productName, qtyInput);
        }

        // --- HÀM GỬI FETCH (Tách riêng để tái sử dụng) ---
        function sendUpdateRequest(cartId, newQty, originalQty, maxQty, productName, qtyInput) {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('cart_id', cartId);
            formData.append('quantity', newQty);

            // Cập nhật UI tạm thời (cho người dùng thấy ngay)
            qtyInput.value = newQty;

            fetch('cart-action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(responseText => {
                    const response = responseText.trim();

                    if (response === 'Success') {
                        // 1. Cập nhật thành tiền
                        const subtotal = prices[cartId] * newQty;
                        document.getElementById('subtotal-' + cartId).textContent = formatPrice(subtotal);

                        // 2. Cập nhật tổng tiền
                        updateCartTotal();

                        showToast('Đã cập nhật số lượng "' + productName + '"', 'success');

                        // 3. (QUAN TRỌNG) Cập nhật lại "số lượng tốt"
                        qtyInput.setAttribute('data-current-qty', newQty);

                        // 4. (QUAN TRỌNG) Cập nhật lại tham số onclick cho các nút
                        const btnMinus = qtyInput.previousElementSibling;
                        const btnPlus = qtyInput.nextElementSibling;

                        const safeProductName = productName.replace(/'/g, "\\'");
                        btnMinus.setAttribute('onclick', `updateQuantity(${cartId}, -1, ${newQty}, ${maxQty}, '${safeProductName}')`);
                        btnPlus.setAttribute('onclick', `updateQuantity(${cartId}, 1, ${newQty}, ${maxQty}, '${safeProductName}')`);

                    } else {
                        // Nếu server trả về lỗi
                        qtyInput.value = originalQty; // Reset về giá trị cũ
                        showToast(response.replace('Error: ', ''), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    qtyInput.value = originalQty; // Reset nếu lỗi mạng
                    showToast('Có lỗi kết nối!', 'error');
                });
        }

        // --- Hàm deleteFromCart (Giữ nguyên như đã sửa) ---
        function deleteFromCart(cartId, productName) {
            if (!confirm('Bạn có chắc muốn xóa "' + productName + '" khỏi giỏ hàng?')) {
                return;
            }
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('cart_id', cartId);
            fetch('cart-action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(responseText => {
                    const response = responseText.trim();
                    if (response === 'Success') {
                        const row = document.getElementById('cart-row-' + cartId);
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.3s';
                        setTimeout(() => {
                            row.remove();
                            delete prices[cartId];
                            updateCartTotal();
                            if (Object.keys(prices).length === 0) {
                                window.location.reload();
                            }
                            showToast('Đã xóa "' + productName + '" khỏi giỏ hàng', 'success');
                        }, 300);
                    } else {
                        showToast(response.replace('Error: ', ''), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Có lỗi kết nối!', 'error');
                });
        }

        // --- Hàm updateCartTotal (Giữ nguyên như đã sửa) ---
        function updateCartTotal() {
            let total = 0;
            for (const cartId in prices) {
                if (Object.prototype.hasOwnProperty.call(prices, cartId)) {
                    const qtyInput = document.getElementById('qty-' + cartId);
                    if (qtyInput) {
                        const qty = parseInt(qtyInput.value);
                        total += prices[cartId] * qty;
                    }
                }
            }
            document.getElementById('cart-subtotal').textContent = formatPrice(total);
            document.getElementById('cart-total').textContent = formatPrice(total);
        }
    </script>
</body>

</html>