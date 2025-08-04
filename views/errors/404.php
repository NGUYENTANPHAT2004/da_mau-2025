<!-- views/errors/404.php -->
<div class="error-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="error-content">
                    <div class="error-code">404</div>
                    <h2>Trang không tìm thấy</h2>
                    <p class="text-muted">Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.</p>
                    <div class="error-actions">
                        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary me-3">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                        <a href="products" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Xem sản phẩm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.error-code {
    font-size: 8rem;
    font-weight: 900;
    background: linear-gradient(45deg, #fff, rgba(255,255,255,0.7));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.error-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.error-actions {
    margin-top: 2rem;
}
</style>
