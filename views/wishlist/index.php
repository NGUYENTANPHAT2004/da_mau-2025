<!-- views/wishlist/index.php -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-heart text-danger me-2"></i>Danh sách yêu thích</h2>
                <?php if(!empty($wishlist_items)): ?>
                    <button class="btn btn-outline-danger" onclick="clearWishlist()">
                        <i class="fas fa-trash me-2"></i>Xóa tất cả
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(empty($wishlist_items)): ?>
        <div class="empty-wishlist text-center py-5">
            <div class="empty-icon mb-4">
                <i class="fas fa-heart-broken text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">Danh sách yêu thích trống</h4>
            <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
            <a href="products" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Khám phá sản phẩm
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($wishlist_items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" id="wishlist-item-<?php echo $item['product_id']; ?>">
                    <div class="card wishlist-card h-100">
                        <div class="card-image-container">
                            <img src="<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo $item['name']; ?>">
                            <div class="card-actions">
                                <button class="btn btn-sm btn-light wishlist-btn active" 
                                        onclick="toggleWishlist(<?php echo $item['product_id']; ?>)"
                                        title="Xóa khỏi yêu thích">
                                    <i class="fas fa-heart text-danger"></i>
                                </button>
                                <a href="product-detail/<?php echo $item['product_id']; ?>" 
                                   class="btn btn-sm btn-light" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <?php if($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                <div class="discount-badge">
                                    -<?php echo round((($item['price'] - $item['sale_price']) / $item['price']) * 100); ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <div class="product-category">
                                <small class="text-muted"><?php echo $item['category_name']; ?></small>
                            </div>
                            <h6 class="card-title"><?php echo $item['name']; ?></h6>
                            
                            <!-- Rating -->
                            <?php if($item['rating'] > 0): ?>
                                <div class="product-rating mb-2">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= round($item['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">(<?php echo $item['review_count']; ?> đánh giá)</small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-price mb-3">
                                <?php if($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                    <span class="current-price text-danger fw-bold">
                                        <?php echo number_format($item['sale_price']); ?> VNĐ
                                    </span>
                                    <br>
                                    <small class="original-price text-muted text-decoration-line-through">
                                        <?php echo number_format($item['price']); ?> VNĐ
                                    </small>
                                <?php else: ?>
                                    <span class="current-price text-primary fw-bold">
                                        <?php echo number_format($item['price']); ?> VNĐ
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent border-0">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm" 
                                        onclick="moveToCart(<?php echo $item['product_id']; ?>)">
                                    <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Recommended Products -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Có thể bạn cũng thích</h4>
                <div class="recommended-products">
                    <!-- Add recommended products slider here -->
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Enhanced Product Card Component -->
<style>
.wishlist-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.card-image-container {
    position: relative;
    overflow: hidden;
}

.card-image-container img {
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.wishlist-card:hover .card-image-container img {
    transform: scale(1.05);
}

.card-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s ease;
}

.wishlist-card:hover .card-actions {
    opacity: 1;
    transform: translateX(0);
}

.card-actions .btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.9);
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: bold;
}

.empty-wishlist .empty-icon i {
    font-size: 5rem;
}

.product-rating .stars {
    display: inline-block;
    margin-right: 0.5rem;
}

.product-rating .stars i {
    font-size: 0.875rem;
}

.current-price {
    font-size: 1.125rem;
}

.wishlist-btn.active i {
    color: #e74c3c !important;
}

/* Animations */
@keyframes heartbeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.wishlist-btn.active:hover i {
    animation: heartbeat 0.6s ease-in-out;
}
</style>

<!-- Enhanced Header Component -->
<style>
/* Enhanced Navigation */
.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.navbar-brand {
    background: linear-gradient(45deg, #fff, #f8f9fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    font-size: 1.8rem;
}

.navbar-nav .nav-link {
    position: relative;
    overflow: hidden;
}

.navbar-nav .nav-link::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: white;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.navbar-nav .nav-link:hover::before,
.navbar-nav .nav-link.active::before {
    width: 80%;
}

/* Enhanced Search */
.search-container {
    position: relative;
}

.search-box {
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 25px;
    color: white;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.search-box:focus {
    background: white;
    color: #333;
    border-color: #667eea;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.search-box::placeholder {
    color: rgba(255,255,255,0.7);
}

/* Cart & Wishlist Badges */
.badge-counter {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(45deg, #ff6b6b, #ee5a52);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Enhanced Product Cards */
.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    border: none;
    position: relative;
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.product-card:hover::before {
    opacity: 1;
}

.product-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* Enhanced Buttons */
.btn-primary {
    background: linear-gradient(45deg, #667eea, #764ba2);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

/* Loading States */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Notification Toast */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
    margin-bottom: 10px;
    backdrop-filter: blur(10px);
}

.toast.success {
    border-left: 4px solid #27ae60;
}

.toast.error {
    border-left: 4px solid #e74c3c;
}

.toast.info {
    border-left: 4px solid #3498db;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .card {
        background: #2c3e50;
        color: #ecf0f1;
    }
    
    .text-muted {
        color: #95a5a6 !important;
    }
    
    .bg-light {
        background: #34495e !important;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1.5rem;
    }
    
    .product-card {
        margin-bottom: 1rem;
    }
    
    .btn-primary {
        padding: 10px 20px;
        font-size: 0.875rem;
    }
    
    .card-actions {
        position: static;
        flex-direction: row;
        justify-content: center;
        opacity: 1;
        transform: none;
        margin-top: 10px;
    }
}
</style>

<!-- JavaScript for Enhanced Interactions -->
<script>
// Enhanced Wishlist Functions
function toggleWishlist(productId) {
    showLoading();
    
    $.ajax({
        url: 'wishlist/toggle',
        type: 'POST',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            
            if(response.success) {
                if(response.action === 'removed') {
                    // Remove item from wishlist page
                    $('#wishlist-item-' + productId).fadeOut(300, function() {
                        $(this).remove();
                        // Check if wishlist is empty
                        if($('.wishlist-card').length === 0) {
                            location.reload();
                        }
                    });
                }
                
                updateWishlistCounter(response.wishlist_count);
                showToast(response.message, 'success');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

function moveToCart(productId) {
    showLoading();
    
    $.ajax({
        url: 'wishlist/move-to-cart',
        type: 'POST',
        data: { product_id: productId },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            
            if(response.success) {
                $('#wishlist-item-' + productId).fadeOut(300, function() {
                    $(this).remove();
                    if($('.wishlist-card').length === 0) {
                        location.reload();
                    }
                });
                
                updateWishlistCounter(response.wishlist_count);
                updateCartCounter();
                showToast(response.message, 'success');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

function clearWishlist() {
    if(confirm('Bạn có chắc muốn xóa toàn bộ danh sách yêu thích?')) {
        showLoading();
        
        $.ajax({
            url: 'wishlist/clear',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if(response.success) {
                    showToast(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                hideLoading();
                showToast('Có lỗi xảy ra!', 'error');
            }
        });
    }
}

// Utility Functions
function showLoading() {
    if(!$('.loading-overlay').length) {
        $('body').append('<div class="loading-overlay"><div class="spinner"></div></div>');
    }
}

function hideLoading() {
    $('.loading-overlay').remove();
}

function showToast(message, type = 'info') {
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast ${type}" role="alert">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
        </div>
    `;
    
    if(!$('.toast-container').length) {
        $('body').append('<div class="toast-container"></div>');
    }
    
    $('.toast-container').append(toastHtml);
    
    const toast = new bootstrap.Toast(document.getElementById(toastId));
    toast.show();
    
    setTimeout(() => {
        $('#' + toastId).remove();
    }, 5000);
}

function updateWishlistCounter(count) {
    $('.wishlist-count').text(count);
    if(count > 0) {
        $('.wishlist-badge').show();
    } else {
        $('.wishlist-badge').hide();
    }
}

function updateCartCounter() {
    $.ajax({
        url: 'cart/get-count',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('.cart-count').text(response.count);
            if(response.count > 0) {
                $('.cart-badge').show();
            } else {
                $('.cart-badge').hide();
            }
        }
    });
}

// Smooth Scrolling
$('a[href^="#"]').on('click', function(e) {
    e.preventDefault();
    const target = $($(this).attr('href'));
    if(target.length) {
        $('html, body').animate({
            scrollTop: target.offset().top - 100
        }, 500);
    }
});

// Parallax Effect
$(window).scroll(function() {
    const scroll = $(window).scrollTop();
    $('.hero-section').css('transform', `translateY(${scroll * 0.5}px)`);
});

// Image Lazy Loading
if('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
</script>