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


function updateQuantityManually(cartId, maxQty, productName, inputElement) {
    const newQty = parseInt(inputElement.value);
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

    sendUpdateRequest(cartId, newQty, originalQty, maxQty, productName, inputElement);
}


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

    sendUpdateRequest(cartId, newQty, originalQty, maxQty, productName, qtyInput);
}

function sendUpdateRequest(cartId, newQty, originalQty, maxQty, productName, qtyInput) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('cart_id', cartId);
    formData.append('quantity', newQty);

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

                
                qtyInput.setAttribute('data-current-qty', newQty);

                
                const btnMinus = qtyInput.previousElementSibling;
                const btnPlus = qtyInput.nextElementSibling;

                const safeProductName = productName.replace(/'/g, "\\'");
                btnMinus.setAttribute('onclick', `updateQuantity(${cartId}, -1, ${newQty}, ${maxQty}, '${safeProductName}')`);
                btnPlus.setAttribute('onclick', `updateQuantity(${cartId}, 1, ${newQty}, ${maxQty}, '${safeProductName}')`);

            } else {
                qtyInput.value = originalQty; 
                showToast(response.replace('Error: ', ''), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            qtyInput.value = originalQty; 
            showToast('Có lỗi kết nối!', 'error');
        });
}

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