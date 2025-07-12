<?php if($action == 'products'): ?>
<div class="container mt-4">
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3">
            <div class="category-filter">
                <h5><i class="fas fa-filter me-2"></i>Lọc sản phẩm</h5>
                <hr>
                
                <!-- Categories -->
                <h6>Danh mục</h6>
                <div class="list-group list-group-flush">
                    <a href="<?php echo BASE_URL; ?>products" 
                       class="list-group-item list-group-item-action <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                        Tất cả sản phẩm
                    </a>
                    <?php foreach($categories as $category): ?>
                    <a href="<?php echo BASE_URL; ?>products?category=<?php echo $category['id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo ($_GET['category'] ?? '') == $category['id'] ? 'active' : ''; ?>">
                        <?php echo $category['name']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Price Range -->
                <hr>
                <h6>Khoảng giá</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="price1">
                    <label class="form-check-label" for="price1">Dưới 5 triệu</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="price2">
                    <label class="form-check-label" for="price2">5 - 10 triệu</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="price3">
                    <label class="form-check-label" for="price3">10 - 20 triệu</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="price4">
                    <label class="form-check-label" for="price4">Trên 20 triệu</label>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Search Results Header -->
            <?php if(isset($_GET['search'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-search me-2"></i>
                Kết quả tìm kiếm cho: <strong>"<?php echo htmlspecialchars($_GET['search']); ?>"</strong>
                (<?php echo count($products); ?> sản phẩm)
            </div>
            <?php endif; ?>
            
            <!-- Sort Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Sản phẩm</h4>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-2"></i>Sắp xếp
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Mới nhất</a></li>
                        <li><a class="dropdown-item" href="#">Giá tăng dần</a></li>
                        <li><a class="dropdown-item" href="#">Giá giảm dần</a></li>
                        <li><a class="dropdown-item" href="#">Tên A-Z</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="row">
                <?php if(empty($products)): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <h5>Không tìm thấy sản phẩm nào</h5>
                        <p>Vui lòng thử lại với từ khóa khác hoặc chọn danh mục khác.</p>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card product-card">
                            <img src="https://via.placeholder.com/300x250/3498db/ffffff?text=<?php echo urlencode($product['name']); ?>" 
                                 class="product-image" alt="<?php echo $product['name']; ?>">
                            <div class="product-info">
                                <h5 class="product-title"><?php echo $product['name']; ?></h5>
                                <p class="text-muted small"><?php echo substr($product['description'], 0, 80); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <?php if($product['sale_price']): ?>
                                            <span class="product-price"><?php echo number_format($product['sale_price']); ?>đ</span>
                                            <br><small class="product-old-price"><?php echo number_format($product['price']); ?>đ</small>
                                        <?php else: ?>
                                            <span class="product-price"><?php echo number_format($product['price']); ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-success">Còn hàng</span>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo BASE_URL; ?>product_detail/<?php echo $product['id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>Xem chi tiết
                                    </a>
                                    <?php if(isLoggedIn()): ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>add-to-cart">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-cart w-100">
                                            <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>login" class="btn btn-cart">
                                        <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để mua
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Trước</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php endif; ?>