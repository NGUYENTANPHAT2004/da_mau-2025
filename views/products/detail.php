<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="product-image">
                <img src="<?php echo $product['image']; ?>" class="img-fluid" alt="<?php echo $product['name']; ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="product-info">
                <h2><?php echo $product['name']; ?></h2>
                
                <div class="price-section mb-3">
                    <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="old-price text-muted text-decoration-line-through">
                            <?php echo number_format($product['price']); ?> VNĐ
                        </span>
                        <span class="current-price text-danger h4">
                            <?php echo number_format($product['sale_price']); ?> VNĐ
                        </span>
                        <span class="discount-badge badge badge-danger">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php else: ?>
                        <span class="current-price text-primary h4">
                            <?php echo number_format($product['price']); ?> VNĐ
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-description mb-3">
                    <p><?php echo $product['description']; ?></p>
                </div>

                <div class="product-meta mb-3">
                    <p><strong>Danh mục:</strong> <?php echo $product['category_name']; ?></p>
                    <p><strong>Tình trạng:</strong> 
                        <?php if($product['quantity'] > 0): ?>
                            <span class="text-success">Còn hàng (<?php echo $product['quantity']; ?>)</span>
                        <?php else: ?>
                            <span class="text-danger">Hết hàng</span>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if($product['quantity'] > 0): ?>
                    <div class="add-to-cart-section">
                        <form id="addToCartForm">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Số lượng:</label>
                                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Sản phẩm hiện tại đã hết hàng
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Đánh giá sản phẩm -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Đánh giá sản phẩm</h4>
                </div>
                <div class="card-body">
                    <?php if(isLoggedIn()): ?>
                        <form id="reviewForm" class="mb-4">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Đánh giá của bạn:</label>
                                        <select name="rating" class="form-control" required>
                                            <option value="">Chọn đánh giá</option>
                                            <option value="5">5 sao - Tuyệt vời</option>
                                            <option value="4">4 sao - Tốt</option>
                                            <option value="3">3 sao - Bình thường</option>
                                            <option value="2">2 sao - Không tốt</option>
                                            <option value="1">1 sao - Rất tệ</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nhận xét:</label>
                                        <textarea name="comment" class="form-control" rows="3" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Gửi đánh giá</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <a href="<?php echo BASE_URL; ?>login">Đăng nhập</a> để đánh giá sản phẩm này.
                        </div>
                    <?php endif; ?>

                    <div id="reviewsList">
                        <?php if(empty($reviews)): ?>
                            <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
                        <?php else: ?>
                            <?php foreach($reviews as $review): ?>
                                <div class="review-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo $review['user_name']; ?></strong>
                                            <div class="rating">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if($review['comment']): ?>
                                        <p class="mt-2 mb-0"><?php echo $review['comment']; ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Thêm vào giỏ hàng
    $('#addToCartForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>add-to-cart',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    // Cập nhật số lượng trong giỏ hàng
                    if(response.cart_count !== undefined) {
                        $('.cart-count').text(response.cart_count);
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    });

    // Gửi đánh giá
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>add-review',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    $('#reviewForm')[0].reset();
                    // Reload trang để hiển thị đánh giá mới
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
});
</script>
