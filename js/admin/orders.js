// --- HÀM COPY SMS MỚI ---
function copyConfirmMsg(orderId, amount) {
    const msg = `Dạ shop đã nhận được ${amount} từ bạn cho đơn hàng #${orderId} rồi ạ. Shop cảm ơn bạn nhiều nhé! Đơn hàng của bạn sẽ được đóng gói và gửi đi trong chiều nay ạ. 🥰`;
    
    navigator.clipboard.writeText(msg).then(function() {
        alert('Đã copy tin nhắn mẫu: \n\n' + msg);
    }, function(err) {
        console.error('Lỗi copy: ', err);
        alert('Copy thủ công:\n' + msg);
    });
}
// -----------------------

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