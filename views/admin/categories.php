<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý danh mục</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCategoryModal()">
                    <i class="fas fa-plus"></i> Thêm danh mục
                </button>
            </div>
        </div>
    </div>

    <!-- Bảng danh mục -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên danh mục</th>
                                    <th>Mô tả</th>
                                    <th>Hình ảnh</th>
                                    <th>Số sản phẩm</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($categories)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Không có danh mục nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><strong><?php echo clean($category['name']); ?></strong></td>
                                            <td><?php echo clean(truncateText($category['description'], 100)); ?></td>
                                            <td>
                                                <?php if($category['image']): ?>
                                                    <img src="<?php echo BASE_URL; ?>uploads/categories/<?php echo $category['image']; ?>" 
                                                         alt="<?php echo clean($category['name']); ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="text-center" style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $category['product_count'] ?? 0; ?> sản phẩm</span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="editCategory(<?php echo $category['id']; ?>)" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Danh mục -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="categoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Thêm danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="category_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="category_name" name="name" required>
                                <div class="invalid-feedback">
                                    Vui lòng nhập tên danh mục.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" id="category_image" name="image" accept="image/*">
                                <div class="form-text">Chấp nhận: JPG, JPEG, PNG, GIF. Tối đa 2MB.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="category_description" name="description" rows="4" placeholder="Nhập mô tả cho danh mục..."></textarea>
                    </div>
                    
                    <div id="currentImage" class="mb-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Đóng
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i>Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa danh mục này?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Lưu ý: Không thể xóa danh mục đang có sản phẩm.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

function openCategoryModal() {
    console.log('Mở modal thêm danh mục');
    $('#categoryModalLabel').text('Thêm danh mục');
    $('#categoryForm')[0].reset();
    $('#category_id').val('');
    $('#currentImage').html('');
    $('#categoryModal').modal('show');
}

function editCategory(id) {
    console.log('Sửa danh mục ID:', id);
    
    // Hiển thị loading
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang tải...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>admin/get-category?id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            if(response.success) {
                const cat = response.category;
                $('#categoryModalLabel').text('Sửa danh mục');
                $('#category_id').val(cat.id);
                $('#category_name').val(cat.name);
                $('#category_description').val(cat.description);
                
                if(cat.image) {
                    $('#currentImage').html(`
                        <div class="current-image-preview">
                            <label class="form-label">Hình ảnh hiện tại:</label>
                            <div>
                                <img src="<?php echo BASE_URL; ?>uploads/categories/${cat.image}" 
                                     class="img-thumbnail" 
                                     style="width:150px;height:150px;object-fit:cover;">
                                <p class="small text-muted mt-1">Chọn file mới để thay đổi hình ảnh</p>
                            </div>
                        </div>
                    `);
                } else {
                    $('#currentImage').html('<p class="text-muted">Chưa có hình ảnh</p>');
                }
                
                $('#categoryModal').modal('show');
            } else {
                showAlert(response.message || 'Không thể tải thông tin danh mục!', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', status, error);
            showAlert('Không thể tải thông tin danh mục! Vui lòng thử lại sau.', 'error');
        },
        complete: function() {
            // Khôi phục trạng thái nút
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
}

$('#categoryForm').on('submit', function(e) {
    e.preventDefault();
    console.log('Form submitted');
    
    const formData = new FormData(this);
    
    // Validate form
    if(!formData.get('name').trim()) {
        showAlert('Vui lòng nhập tên danh mục!', 'error');
        $('#category_name').focus();
        return;
    }
    
    // Log dữ liệu form để kiểm tra
    for (const pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Hiển thị chỉ báo đang tải
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>admin/save-category',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            if(response.success) {
                showAlert(response.message || 'Lưu danh mục thành công!', 'success');
                $('#categoryModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(response.message || 'Không thể lưu danh mục!', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', status, error);
            console.log('Response Text:', xhr.responseText);
            let errorMsg = 'Có lỗi xảy ra khi lưu danh mục!';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showAlert(errorMsg, 'error');
        },
        complete: function() {
            // Khôi phục trạng thái nút
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
});

function deleteCategory(id) {
    deleteId = id;
    $('#confirmModal').modal('show');
}

$('#confirmDeleteBtn').on('click', function() {
    if(!deleteId) return;
    
    const btn = $(this);
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xóa...');
    btn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>admin/delete-category',
        type: 'POST',
        data: {id: deleteId},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showAlert(response.message || 'Đã xóa danh mục thành công', 'success');
                $('#confirmModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(response.message || 'Không thể xóa danh mục', 'error');
            }
        },
        error: function() {
            showAlert('Có lỗi xảy ra khi xóa danh mục!', 'error');
        },
        complete: function() {
            btn.html(originalText);
            btn.prop('disabled', false);
            deleteId = null;
        }
    });
});

// Alert function
function showAlert(message, type = 'info') {
    const alertClass = type === 'error' ? 'danger' : type;
    const iconClass = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    const alertHtml = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Insert new alert at top of container
    $('.container-fluid').prepend(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

// Form validation
$('#category_name').on('input', function() {
    const value = $(this).val().trim();
    if(value) {
        $(this).removeClass('is-invalid').addClass('is-valid');
    } else {
        $(this).removeClass('is-valid').addClass('is-invalid');
    }
});

// Image preview
$('#category_image').on('change', function() {
    const file = this.files[0];
    if(file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if(!allowedTypes.includes(file.type)) {
            showAlert('Chỉ chấp nhận file hình ảnh (JPG, PNG, GIF)', 'error');
            $(this).val('');
            return;
        }
        
        // Validate file size (2MB)
        if(file.size > 2 * 1024 * 1024) {
            showAlert('Kích thước file không được vượt quá 2MB', 'error');
            $(this).val('');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#currentImage').html(`
                <div class="new-image-preview">
                    <label class="form-label">Xem trước hình ảnh mới:</label>
                    <div>
                        <img src="${e.target.result}" 
                             class="img-thumbnail" 
                             style="width:150px;height:150px;object-fit:cover;">
                    </div>
                </div>
            `);
        };
        reader.readAsDataURL(file);
    }
});
</script>