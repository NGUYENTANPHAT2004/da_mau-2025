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
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($categories)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Không có danh mục nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><?php echo $category['name']; ?></td>
                                            <td><?php echo $category['description']; ?></td>
                                            <td>
                                                <?php if($category['image']): ?>
                                                    <img src="uploads/categories/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <span class="text-muted">Không có</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Hoạt động</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="editCategory(<?php echo $category['id']; ?>)"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)"><i class="fas fa-trash"></i></button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="categoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Thêm danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="category_id">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Tên danh mục *</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="category_image" class="form-label">Hình ảnh</label>
                        <input type="file" class="form-control" id="category_image" name="image" accept="image/*">
                        <div id="currentImage" class="mt-2"></div>
                        <!-- Thêm trường ẩn để lưu trữ hình ảnh hiện có -->
                        <input type="hidden" name="existing_image" id="existing_image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCategoryModal() {
    // Thêm log để debug
    console.log('Mở modal thêm danh mục');
    $('#categoryModalLabel').text('Thêm danh mục');
    $('#categoryForm')[0].reset();
    $('#category_id').val('');
    $('#currentImage').html('');
    $('#existing_image').val('');
}

function editCategory(id) {
    $.ajax({
        url: '<?php echo BASE_URL; ?>get-category/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var cat = response.category;
                $('#categoryModalLabel').text('Sửa danh mục');
                $('#category_id').val(cat.id);
                $('#category_name').val(cat.name);
                $('#category_description').val(cat.description);
                $('#existing_image').val(cat.image || '');
                
                if(cat.image) {
                    $('#currentImage').html('<div><img src="<?php echo BASE_URL; ?>uploads/categories/' + cat.image + '" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;"><p class="small text-muted mt-1">Hình ảnh hiện tại</p></div>');
                } else {
                    $('#currentImage').html('<p class="text-muted">Không có hình ảnh</p>');
                }
                $('#categoryModal').modal('show');
            } else {
                alert(response.message || 'Không thể tải thông tin danh mục!');
            }
        },
        error: function() {
            alert('Không thể tải thông tin danh mục! Vui lòng thử lại sau.');
        }
    });
}

$('#categoryForm').on('submit', function(e) {
    e.preventDefault();
    console.log('Form submitted'); // Thêm log để debug
    var formData = new FormData(this);
    
    // Log dữ liệu form để kiểm tra
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Hiển thị chỉ báo đang tải
    var submitBtn = $(this).find('button[type="submit"]');
    var originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>save-category',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response); // Thêm log để debug
            if(response.success) {
                alert(response.message || 'Lưu danh mục thành công!');
                $('#categoryModal').modal('hide');
                location.reload();
            } else {
                alert(response.message || 'Không thể lưu danh mục!');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', status, error); // Thêm log chi tiết lỗi
            console.log('Response Text:', xhr.responseText);
            var errorMsg = 'Có lỗi xảy ra khi lưu danh mục!';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            alert(errorMsg);
        },
        complete: function() {
            // Khôi phục trạng thái nút
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
});

function deleteCategory(id) {
    if(confirm('Bạn có chắc muốn xóa danh mục này? Việc này có thể ảnh hưởng đến các sản phẩm thuộc danh mục.')) {
        $.ajax({
            url: '<?php echo BASE_URL; ?>delete-category',
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Đã xóa danh mục thành công');
                    location.reload();
                } else {
                    alert(response.message || 'Không thể xóa danh mục');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi xóa danh mục!');
            }
        });
    }
}
</script>