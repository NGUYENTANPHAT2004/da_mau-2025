<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="product-image">
                <?php 
                $imagePath = $product['image'] ? BASE_URL . 'uploads/products/' . $product['image'] : 'https://via.placeholder.com/500x400/e74c3c/ffffff?text=' . urlencode($product['name']);
                ?>
                <img src="<?php echo $imagePath; ?>" class="img-fluid rounded" alt="<?php echo clean($product['name']); ?>" style="width: 100%; height: 400px; object-fit: cover;">
            </div>
        </div>
        <div class="col-md-6">
            <div class="product-info">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>products">Sản phẩm</a></li>
                        <li class="breadcrumb-item active"><?php echo clean($product['name']); ?></li>
                    </ol>
                </nav>

                <h2><?php echo clean($product['name']); ?></h2>
                
                <div class="price-section mb-3">
                    <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="old-price text-muted text-decoration-line-through h5">
                            <?php echo formatPrice($product['price']); ?>
                        </span>
                        <span class="current-price text-danger h3 ms-3">
                            <?php echo formatPrice($product['sale_price']); ?>
                        </span>
                        <span class="discount-badge badge bg-danger ms-2">
                            -<?php echo calculateDiscount($product['price'], $product['sale_price']); ?>%
                        </span>
                    <?php else: ?>
                        <span class="current-price text-primary h3">
                            <?php echo formatPrice($product['price']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-description mb-3">
                    <p><?php echo clean($product['description']); ?></p>
                </div>

                <div class="product-meta mb-4">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>Danh mục:</strong> <?php echo clean($product['category_name']); ?></p>
                        </div>
                        <div class="col-6">
                            <p><strong>Tình trạng:</strong> 
                                <?php if($product['quantity'] > 0): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['quantity']; ?>)</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Hết hàng</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if($product['quantity'] > 0): ?>
                    <?php if(isLoggedIn()): ?>
                        <div class="add-to-cart-section mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Số lượng:</label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">-</button>
                                        <input type="number" id="quantity" class="form-control text-center" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">+</button>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="addProductToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <a href="<?php echo BASE_URL; ?>login">Đăng nhập</a> để mua sản phẩm này.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Sản phẩm hiện tại đã hết hàng
                    </div>
                <?php endif; ?>

                <div class="product-actions">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                            <i class="fas fa-heart"></i> Yêu thích
                        </button>
                        <button class="btn btn-outline-secondary" onclick="shareProduct()">
                            <i class="fas fa-share"></i> Chia sẻ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description">
                        <i class="fas fa-info-circle me-2"></i>Mô tả
                    </button>
                    <button class="nav-link" id="nav-reviews-tab" data-bs-toggle="tab" data-bs-target="#nav-reviews">
                        <i class="fas fa-star me-2"></i>Đánh giá (<?php echo count($reviews); ?>)
                    </button>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-description">
                    <div class="card">
                        <div class="card-body">
                            <h5>Thông tin chi tiết</h5>
                            <p><?php echo nl2br(clean($product['description'])); ?></p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-reviews">
                    <div class="card">
                        <div class="card-body">
                            <h5>Đánh giá sản phẩm</h5>
                            
                            <?php if(isLoggedIn() && $can_review): ?>
                                <div class="mb-4">
                                    <h6>Viết đánh giá của bạn</h6>
                                    <form id="reviewForm">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Đánh giá của bạn:</label>
                                                    <select name="rating" class="form-select" required>
                                                        <option value="">Chọn đánh giá</option>
                                                        <option value="5">★★★★★ 5 sao - Tuyệt vời</option>
                                                        <option value="4">★★★★☆ 4 sao - Tốt</option>
                                                        <option value="3">★★★☆☆ 3 sao - Bình thường</option>
                                                        <option value="2">★★☆☆☆ 2 sao - Không tốt</option>
                                                        <option value="1">★☆☆☆☆ 1 sao - Rất tệ</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nhận xét:</label>
                                                    <textarea name="comment" class="form-control" rows="3" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success">Gửi đánh giá</button>
                                    </form>
                                </div>
                                <hr>
                            <?php elseif(!isLoggedIn()): ?>
                                <div class="alert alert-info mb-4">
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
                                                    <strong><?php echo clean($review['user_name']); ?></strong>
                                                    <div class="rating mb-1">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo timeAgo($review['created_at']); ?>
                                                </small>
                                            </div>
                                            <?php if($review['comment']): ?>
                                                <p class="mt-2 mb-0"><?php echo clean($review['comment']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if(!empty($related_products)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-4">Sản phẩm liên quan</h4>
            <div class="row">
                <?php foreach($related_products as $related): ?>
                    <?php if($related['id'] != $product['id']): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card product-card h-100">
                            <?php 
                            $relatedImagePath = $related['image'] ? BASE_URL . 'uploads/products/' . $related['image'] : 'https://via.placeholder.com/250x200/3498db/ffffff?text=' . urlencode($related['name']);
                            ?>
                            <img src="<?php echo $relatedImagePath; ?>" class="card-img-top" alt="<?php echo clean($related['name']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo truncateText($related['name'], 50); ?></h6>
                                <div class="price mb-2">
                                    <?php if($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                                        <span class="text-danger fw-bold"><?php echo formatPrice($related['sale_price']); ?></span>
                                        <br><small class="text-muted text-decoration-line-through"><?php echo formatPrice($related['price']); ?></small>
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo formatPrice($related['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-grid">
                                    <a href="<?php echo BASE_URL; ?>product-detail/<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeQuantity(delta) {
    const qtyInput = document.getElementById('quantity');
    const currentQty = parseInt(qtyInput.value);
    const newQty = currentQty + delta;
    const maxQty = parseInt(qtyInput.getAttribute('max'));
    
    if(newQty >= 1 && newQty <= maxQty) {
        qtyInput.value = newQty;
    }
}

function addProductToCart(productId) {
    const quantity = document.getElementById('quantity').value;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang thêm...';
    button.disabled = true;
    
    fetch(BASE_URL + 'add-to-cart', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showAlert(data.message, 'success');
            updateCartCount();
            
            // Reset form
            document.getElementById('quantity').value = 1;
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra!', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function toggleWishlist(productId) {
    // Wishlist functionality - can be implemented later
    showAlert('Chức năng yêu thích sẽ được cập nhật sớm!', 'info');
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo clean($product['name']); ?>',
            text: '<?php echo clean($product['description']); ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        showAlert('Đã copy link sản phẩm!', 'success');
    }
}

// Review form submission
document.addEventListener('DOMContentLoaded', function() {
    const reviewForm = document.getElementById('reviewForm');
    if(reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(BASE_URL + 'add-review', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showAlert(data.message, 'success');
                    this.reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Có lỗi xảy ra!', 'error');
            });
        });
    }
});
</script>