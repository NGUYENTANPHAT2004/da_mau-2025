<?php if($action == 'home'): ?>
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4 fade-in">
                    Chào mừng đến với <?php echo SITE_NAME; ?>
                </h1>
                <p class="lead mb-4 fade-in">
                    Khám phá hàng ngàn sản phẩm công nghệ chính hãng với giá tốt nhất
                </p>
                <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary btn-lg fade-in">
                    <i class="fas fa-shopping-bag me-2"></i>Mua sắm ngay
                </a>
            </div>
            <div class="col-lg-6">
                <img src="https://via.placeholder.com/500x400/3498db/ffffff?text=FPoly+Shop" 
                     class="img-fluid rounded" alt="Hero Image">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<div class="container">
    <h2 class="text-center mb-5">Danh mục nổi bật</h2>
    <div class="row">
        <?php foreach($categories as $category): ?>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card product-card text-center">
                <div class="card-body">
                    <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $category['name']; ?></h5>
                    <p class="card-text"><?php echo $category['description']; ?></p>
                    <a href="<?php echo BASE_URL; ?>products?category=<?php echo $category['id']; ?>" 
                       class="btn btn-outline-primary">Xem sản phẩm</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Featured Products -->
<div class="container">
    <h2 class="text-center mb-5">Sản phẩm nổi bật</h2>
    <div class="row">
        <?php foreach($featured_products as $product): ?>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card product-card">
                <img src="https://via.placeholder.com/300x250/e74c3c/ffffff?text=<?php echo urlencode($product['name']); ?>" 
                     class="product-image" alt="<?php echo $product['name']; ?>">
                <div class="product-info">
                    <h5 class="product-title"><?php echo $product['name']; ?></h5>
                    <p class="text-muted small"><?php echo substr($product['description'], 0, 60); ?>...</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?php if($product['sale_price']): ?>
                                <span class="product-price"><?php echo number_format($product['sale_price']); ?>đ</span>
                                <br><small class="product-old-price"><?php echo number_format($product['price']); ?>đ</small>
                            <?php else: ?>
                                <span class="product-price"><?php echo number_format($product['price']); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="<?php echo BASE_URL; ?>product_detail/<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if(isLoggedIn()): ?>
                            <form style="display: inline-block;" action="<?php echo BASE_URL; ?>add-to-cart" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-cart">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>