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
                    <li><a href="<?php echo BASE_URL; ?>products?category=1" class="text-white-50">Điện thoại</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products?category=2" class="text-white-50">Laptop</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products?category=3" class="text-white-50">Tablet</a></li>
                    <li><a href="<?php echo BASE_URL; ?>products?category=4" class="text-white-50">Phụ kiện</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 mb-4">
                <h6>Hỗ trợ khách hàng</h6>
                <ul class="list-unstyled">
                    <li><a href="tel:19001234" class="text-white-50">Hotline: 1900 1234</a></li>
                    <li><a href="mailto:support@fpolyshop.com" class="text-white-50">Email: support@fpolyshop.com</a></li>
                    <li><a href="#" class="text-white-50">Chính sách đổi trả</a></li>
                    <li><a href="#" class="text-white-50">Hướng dẫn mua hàng</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 mb-4">
                <h6>Đăng ký nhận tin</h6>
                <p class="text-white-50">Nhận thông tin về sản phẩm mới và khuyến mãi</p>
                <div class="input-group">
                    <input type="email" class="form-control" placeholder="Email của bạn" id="newsletterEmail">
                    <button class="btn btn-primary" type="button" onclick="subscribeNewsletter()">
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
// Global variables
const BASE_URL = '<?php echo BASE_URL; ?>';

// Show alert messages
<?php $alert = getAlert(); if($alert): ?>
    showAlert('<?php echo $alert["message"]; ?>', '<?php echo $alert["type"]; ?>');
<?php endif; ?>

// Utility functions
function showAlert(message, type = 'info') {
    const alertClass = type === 'error' ? 'danger' : type;
    const alertHtml = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.container');
    if(mainContent) {
        mainContent.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if(alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    }
}

// Cart functions
function updateCartCount() {
    if(typeof BASE_URL === 'undefined') return;
    
    fetch(BASE_URL + 'get-cart-count')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const countElement = document.querySelector('.cart-count');
                if(countElement) {
                    countElement.textContent = data.count;
                    countElement.style.display = data.count > 0 ? 'inline' : 'none';
                }
            }
        })
        .catch(error => console.log('Error updating cart count:', error));
}

// Add to cart function
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch(BASE_URL + 'add-to-cart', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showAlert(data.message, 'success');
            updateCartCount();
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra!', 'error');
    });
}

// Form submission helper
function submitForm(formId, url, callback) {
    const form = document.getElementById(formId);
    if(!form) return;
    
    const formData = new FormData(form);
    
    fetch(BASE_URL + url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(callback) callback(data);
        else {
            if(data.success) {
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra!', 'error');
    });
}

// Newsletter subscription
function subscribeNewsletter() {
    const email = document.getElementById('newsletterEmail').value;
    if(!email || !isValidEmail(email)) {
        showAlert('Vui lòng nhập email hợp lệ', 'error');
        return;
    }
    
    showAlert('Cảm ơn bạn đã đăng ký nhận tin!', 'success');
    document.getElementById('newsletterEmail').value = '';
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if(target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Add validation to forms
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if(!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Update cart count on page load
    updateCartCount();
});

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if(!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if(field.value && !isValidEmail(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Loading state helper
function setLoading(element, loading = true) {
    if(loading) {
        element.disabled = true;
        element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    } else {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText || 'Submit';
    }
}

// Auto-hide alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if(!alert.classList.contains('alert-permanent')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                if(alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }
    });
}, 5000);

// Back to top button
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if(backToTop) {
        if(window.scrollY > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    }
});

// Image lazy loading fallback
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    images.forEach(img => {
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
    });
});
</script>

</body>
</html>