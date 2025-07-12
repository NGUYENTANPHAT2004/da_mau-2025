<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5><i class="fas fa-shopping-bag me-2"></i><?php echo SITE_NAME; ?></h5>
                <p>Website bán hàng trực tuyến hàng đầu Việt Nam. Chuyên cung cấp các sản phẩm công nghệ chính hãng với giá tốt nhất.</p>
                <div class="social-links">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-youtube fa-2x"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 mb-4">
                <h6>Danh mục</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50">Điện thoại</a></li>
                    <li><a href="#" class="text-white-50">Laptop</a></li>
                    <li><a href="#" class="text-white-50">Tablet</a></li>
                    <li><a href="#" class="text-white-50">Phụ kiện</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 mb-4">
                <h6>Hỗ trợ khách hàng</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50">Hotline: 1900 1234</a></li>
                    <li><a href="#" class="text-white-50">Email: support@fpolyshop.com</a></li>
                    <li><a href="#" class="text-white-50">Chính sách đổi trả</a></li>
                    <li><a href="#" class="text-white-50">Hướng dẫn mua hàng</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 mb-4">
                <h6>Đăng ký nhận tin</h6>
                <p class="text-white-50">Nhận thông tin về sản phẩm mới và khuyến mãi</p>
                <div class="input-group">
                    <input type="email" class="form-control" placeholder="Email của bạn">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <hr class="border-white-50">
        <div class="row">
            <div class="col-md-6">
                <p class="text-white-50 mb-0">© 2024 <?php echo SITE_NAME; ?>. Thiết kế bởi sinh viên FPoly.</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-white-50 mb-0">
                    <a href="#" class="text-white-50 me-3">Điều khoản</a>
                    <a href="#" class="text-white-50">Bảo mật</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script>
// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add to cart animation
function addToCartAnimation(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<div class="spinner"></div>';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Đã thêm';
        button.classList.remove('btn-cart');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-cart');
            button.disabled = false;
        }, 2000);
    }, 1000);
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Search suggestion
function setupSearchSuggestion() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Implement search suggestion logic here
        });
    }
}

// Initialize components
document.addEventListener('DOMContentLoaded', function() {
    setupSearchSuggestion();
    
    // Animate elements on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    });
    
    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
});

// Alert auto-hide
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

</body>
</html>