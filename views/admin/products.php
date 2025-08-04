<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý sản phẩm</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openProductModal()">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </button>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" value="<?php echo $_GET['search'] ?? ''; ?>" placeholder="Tên sản phẩm...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($_GET['category'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo clean($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="in_stock" <?php echo ($_GET['status'] ?? '') == 'in_stock' ? 'selected' : ''; ?>>Còn hàng</option>
                                <option value="out_of_stock" <?php echo ($_GET['status'] ?? '') == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin/products" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Xóa lọc
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng sản phẩm -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="10%">Hình ảnh</th>
                                    <th width="25%">Tên sản phẩm</th>
                                    <th width="15%">Danh mục</th>
                                    <th width="15%">Giá</th>
                                    <th width="10%">Số lượng</th>
                                    <th width="10%">Trạng thái</th>
                                    <th width="10%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                                <p class="mb-0">Không có sản phẩm nào</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <?php if($product['image']): ?>
                                                    <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo $product['image']; ?>" 
                                                         alt="<?php echo clean($product['name']); ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="text-center bg-light rounded" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image text-muted fa-2x"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo clean($product['name']); ?></strong>
                                                    <?php if($product['description']): ?>
                                                        <br><small class="text-muted"><?php echo clean(truncateText($product['description'], 80)); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo clean($product['category_name']); ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                        <span class="text-danger fw-bold"><?php echo formatPrice($product['sale_price']); ?></span>
                                                        <br><small class="text-muted text-decoration-line-through"><?php echo formatPrice($product['price']); ?></small>
                                                        <br><small class="badge bg-danger">-<?php echo calculateDiscount($product['price'], $product['sale_price']); ?>%</small>
                                                    <?php else: ?>
                                                        <span class="fw-bold"><?php echo formatPrice($product['price']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?> fs-6">
                                                    <?php echo $product['quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $product['quantity'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="editProduct(<?php echo $product['id']; ?>)" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Xóa">
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

                    <!-- Phân trang -->
                    <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">
                                            <i class="fas fa-chevron-left"></i> Trước
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php 
                                $start = max(1, $current_page - 2);
                                $end = min($total_pages, $current_page + 2);
                                ?>
                                
                                <?php if($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">1</a>
                                    </li>
                                    <?php if($start > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($end < $total_pages): ?>
                                    <?php if($end < $total_pages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">
                                            Sau <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa sản phẩm -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="product_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="product_name" class="form-control" required>
                                        <div class="invalid-feedback">Vui lòng nhập tên sản phẩm.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                        <select name="category_id" id="product_category" class="form-select" required>
                                            <option value="">Chọn danh mục</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"><?php echo clean($category['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Vui lòng chọn danh mục.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Giá gốc <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="price" id="product_price" class="form-control" min="0" step="1000" required>
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                        <div class="invalid-feedback">Vui lòng nhập giá hợp lệ.</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Giá khuyến mãi</label>
                                        <div class="input-group">
                                            <input type="number" name="sale_price" id="product_sale_price" class="form-control" min="0" step="1000">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                        <div class="form-text">Để trống nếu không có khuyến mãi</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity" id="product_quantity" class="form-control" min="0" required>
                                        <div class="invalid-feedback">Vui lòng nhập số lượng hợp lệ.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mô tả sản phẩm</label>
                                <textarea name="description" id="product_description" class="form-control" rows="5" placeholder="Nhập mô tả chi tiết về sản phẩm..."></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh sản phẩm</label>
                                <input type="file" name="image" id="product_image" class="form-control" accept="image/*">
                                <div class="form-text">Chấp nhận: JPG, JPEG, PNG, GIF. Tối đa 2MB.</div>
                            </div>
                            
                            <div id="imagePreview" class="text-center">
                                <div class="bg-light rounded p-4">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">Chưa có hình ảnh</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Đóng
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitProductBtn">
                        <i class="fas fa-save me-1"></i>Lưu sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa sản phẩm này?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Thao tác này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProductBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteProductId = null;

function openProductModal() {
    $('#modalTitle').text('Thêm sản phẩm mới');
    $('#productForm')[0].reset();
    $('#product_id').val('');
    $('#imagePreview').html(`
        <div class="bg-light rounded p-4">
            <i class="fas fa-image fa-3x text-muted"></i>
            <p class="text-muted mb-0 mt-2">Chưa có hình ảnh</p>
        </div>
    `);
    $('#productModal').modal('show');
}

function editProduct(productId) {
    console.log('Sửa sản phẩm ID:', productId);
    
    const submitBtn = $('#submitProductBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang tải...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>admin/get-product?id=' + productId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const product = response.product;
                $('#modalTitle').text('Sửa sản phẩm');
                $('#product_id').val(product.id);
                $('#product_name').val(product.name);
                $('#product_category').val(product.category_id);
                $('#product_price').val(product.price);
                $('#product_sale_price').val(product.sale_price || '');
                $('#product_quantity').val(product.quantity);
                $('#product_description').val(product.description);
                
                if(product.image) {
                    $('#imagePreview').html(`
                        <div>
                            <img src="<?php echo BASE_URL; ?>uploads/products/${product.image}" 
                                 class="img-fluid rounded" 
                                 style="max-width: 100%; max-height: 200px; object-fit: cover;">
                            <p class="small text-muted mt-2">Hình ảnh hiện tại</p>
                        </div>
                    `);
                } else {
                    $('#imagePreview').html(`
                        <div class="bg-light rounded p-4">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p class="text-muted mb-0 mt-2">Chưa có hình ảnh</p>
                        </div>
                    `);
                }
                
                $('#productModal').modal('show');
            } else {
                showAlert(response.message || 'Không thể tải thông tin sản phẩm', 'error');
            }
        },
        error: function() {
            showAlert('Có lỗi xảy ra khi tải thông tin sản phẩm!', 'error');
        },
        complete: function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
}

function deleteProduct(productId) {
    deleteProductId = productId;
    $('#confirmDeleteModal').modal('show');
}

$('#confirmDeleteProductBtn').on('click', function() {
    if(!deleteProductId) return;
    
    const btn = $(this);
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xóa...');
    btn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>admin/delete-product',
        type: 'POST',
        data: {product_id: deleteProductId},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showAlert(response.message || 'Đã xóa sản phẩm thành công', 'success');
                $('#confirmDeleteModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(response.message || 'Không thể xóa sản phẩm', 'error');
            }
        },
        error: function() {
            showAlert('Có lỗi xảy ra khi xóa sản phẩm!', 'error');
        },
        complete: function() {
            btn.html(originalText);
            btn.prop('disabled', false);
            deleteProductId = null;
        }
    });
});

$('#productForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validate form
    let isValid = true;
    
    // Check required fields
    $(this).find('[required]').each(function() {
        if(!$(this).val()) {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
    
    // Validate prices
    const price = parseFloat($('#product_price').val());
    const salePrice = parseFloat($('#product_sale_price').val());
    
    if(price <= 0) {
        $('#product_price').addClass('is-invalid');
        isValid = false;
    }
    
    if(salePrice && salePrice >= price) {
        showAlert('Giá khuyến mãi phải nhỏ hơn giá gốc!', 'error');
        $('#product_sale_price').addClass('is-invalid');
        isValid = false;
    }
    
    if(!isValid) {
        showAlert('Vui lòng kiểm tra lại thông tin!', 'error');
        return;
    }
    
    const formData = new FormData(this);
    
    const submitBtn = $('#submitProductBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
    submitBtn.prop('disabled', true);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>admin/save-product',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                showAlert(response.message || 'Lưu sản phẩm thành công!', 'success');
                $('#productModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(response.message || 'Không thể lưu sản phẩm!', 'error');
            }
        },
        error: function() {
            showAlert('Có lỗi xảy ra khi lưu sản phẩm!', 'error');
        },
        complete: function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }
    });
});

// Image preview
$('#product_image').on('change', function() {
    const file = this.files[0];
    if(file) {
        // Validate file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if(!allowedTypes.includes(file.type)) {
            showAlert('Chỉ chấp nhận file hình ảnh (JPG, PNG, GIF)', 'error');
            $(this).val('');
            return;
        }
        
        if(file.size > 2 * 1024 * 1024) {
            showAlert('Kích thước file không được vượt quá 2MB', 'error');
            $(this).val('');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').html(`
                <div>
                    <img src="${e.target.result}" 
                         class="img-fluid rounded" 
                         style="max-width: 100%; max-height: 200px; object-fit: cover;">
                    <p class="small text-muted mt-2">Xem trước hình ảnh mới</p>
                </div>
            `);
        };
        reader.readAsDataURL(file);
    }
});

// Form validation
$('#productForm input, #productForm select, #productForm textarea').on('input change', function() {
    $(this).removeClass('is-invalid');
    if($(this).is('[required]') && $(this).val()) {
        $(this).addClass('is-valid');
    }
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
    
    $('.alert').remove();
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
}

$('#productModal').on('hidden.bs.modal', function() {
    $('#productForm')[0].reset();
    $('#product_id').val('');
    $('#modalTitle').text('Thêm sản phẩm mới');
    $('#productForm .is-valid, #productForm .is-invalid').removeClass('is-valid is-invalid');
});
</script>