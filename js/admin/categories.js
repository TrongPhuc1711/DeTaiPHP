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