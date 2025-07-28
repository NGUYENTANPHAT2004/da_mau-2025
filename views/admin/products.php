<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quản lý sản phẩm</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
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
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tìm kiếm</label>
                                <input type="text" name="search" class="form-control" value="<?php echo $_GET['search'] ?? ''; ?>" placeholder="Tên sản phẩm...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Danh mục</label>
                                <select name="category" class="form-control">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($_GET['category'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="active" <?php echo ($_GET['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Còn hàng</option>
                                    <option value="out_of_stock" <?php echo ($_GET['status'] ?? '') == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin-products" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Xóa bộ lọc
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
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Không có sản phẩm nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" 
                                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <strong><?php echo $product['name']; ?></strong>
                                                <br><small class="text-muted"><?php echo substr($product['description'], 0, 100); ?>...</small>
                                            </td>
                                            <td><?php echo $product['category_name']; ?></td>
                                            <td>
                                                <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                    <span class="text-danger"><?php echo number_format($product['sale_price']); ?> VNĐ</span>
                                                    <br><small class="text-muted text-decoration-line-through"><?php echo number_format($product['price']); ?> VNĐ</small>
                                                <?php else: ?>
                                                    <span><?php echo number_format($product['price']); ?> VNĐ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $product['quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $product['quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $product['quantity'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="editProduct(<?php echo $product['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
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
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Trước</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Sau</a>
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
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm sản phẩm mới</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="product_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên sản phẩm *</label>
                                <input type="text" name="name" id="product_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Danh mục *</label>
                                <select name="category_id" id="product_category" class="form-control" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Giá gốc *</label>
                                <input type="number" name="price" id="product_price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Giá khuyến mãi</label>
                                <input type="number" name="sale_price" id="product_sale_price" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Số lượng *</label>
                                <input type="number" name="quantity" id="product_quantity" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hình ảnh</label>
                                <input type="file" name="image" id="product_image" class="form-control-file" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" id="product_description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProduct(productId) {
    // Load thông tin sản phẩm
    $.ajax({
        url: '<?php echo BASE_URL; ?>get-product/' + productId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var product = response.product;
                $('#product_id').val(product.id);
                $('#product_name').val(product.name);
                $('#product_category').val(product.category_id);
                $('#product_price').val(product.price);
                $('#product_sale_price').val(product.sale_price);
                $('#product_quantity').val(product.quantity);
                $('#product_description').val(product.description);
                
                $('#modalTitle').text('Sửa sản phẩm');
                $('#addProductModal').modal('show');
            } else {
                alert('Không thể tải thông tin sản phẩm');
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
}

function deleteProduct(productId) {
    if(confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        $.ajax({
            url: '<?php echo BASE_URL; ?>delete-product',
            type: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Đã xóa sản phẩm thành công');
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    }
}

$('#productForm').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>save-product',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
                $('#addProductModal').modal('hide');
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

$('#addProductModal').on('hidden.bs.modal', function() {
    $('#productForm')[0].reset();
    $('#product_id').val('');
    $('#modalTitle').text('Thêm sản phẩm mới');
});
</script>
