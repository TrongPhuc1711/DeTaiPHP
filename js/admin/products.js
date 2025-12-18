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