function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const title = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    
    toast.classList.remove('success', 'error', 'show', 'hide');
    
    if (type === 'success') {
        toast.classList.add('success');
        icon.textContent = '✓';
        title.textContent = 'Thành công!';
    } else {
        toast.classList.add('error');
        icon.textContent = '✕';
        title.textContent = 'Lỗi!';
    }
    
    messageEl.textContent = message;
    toast.classList.add('show');
    
    setTimeout(() => {
        closeToast();
    }, 3000);
}

function closeToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('hide');
    setTimeout(() => {
        toast.classList.remove('show', 'hide');
    }, 400);
}

function updateCartCount(count) {
    const cartCountEl = document.querySelector('.cart-count');
    if (cartCountEl) {
        cartCountEl.textContent = count;
    } else if (count > 0) {
        const cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) {
            const badge = document.createElement('span');
            badge.className = 'cart-count';
            badge.textContent = count;
            cartBtn.appendChild(badge);
        }
    }
}

function addToCart(productId, productName) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Đang thêm...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    
    fetch('cart-action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if (data.success) {
            showToast(data.message, 'success');
            updateCartCount(data.cart_count);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    });
}