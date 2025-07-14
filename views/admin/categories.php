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
    $('#categoryModalLabel').text('Thêm danh mục');
    $('#categoryForm')[0].reset();
    $('#category_id').val('');
    $('#currentImage').html('');
}

function editCategory(id) {
    $.ajax({
        url: 'get-category/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var cat = response.category;
                $('#categoryModalLabel').text('Sửa danh mục');
                $('#category_id').val(cat.id);
                $('#category_name').val(cat.name);
                $('#category_description').val(cat.description);
                if(cat.image) {
                    $('#currentImage').html('<img src="uploads/categories/' + cat.image + '" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover;">');
                } else {
                    $('#currentImage').html('');
                }
                $('#categoryModal').modal('show');
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Không thể tải thông tin danh mục!');
        }
    });
}

$('#categoryForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'save-category',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
                $('#categoryModal').modal('hide');
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
});

function deleteCategory(id) {
    if(confirm('Bạn có chắc muốn xóa danh mục này?')) {
        $.ajax({
            url: 'delete-category',
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Đã xóa danh mục thành công');
                    location.reload();
                } else {
                    alert('Không thể xóa danh mục');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    }
}
</script> 