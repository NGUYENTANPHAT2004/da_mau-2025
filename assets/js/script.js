class FPolyShop {
    constructor() {
        this.cart = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupCartFunctionality();
        this.setupSearch();
    }

    setupEventListeners() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                this.fadeOut(alert);
            });
        }, 5000);
    }

    setupFormValidation() {
        // Real-time form validation
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    this.validateField(input);
                }
            });
        });

        // Password confirmation
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('confirm_password');
        
        if (passwordField && confirmField) {
            confirmField.addEventListener('input', () => {
                if (passwordField.value !== confirmField.value) {
                    confirmField.setCustomValidity('Mật khẩu không khớp');
                    confirmField.classList.add('is-invalid');
                } else {
                    confirmField.setCustomValidity('');
                    confirmField.classList.remove('is-invalid');
                }
            });
        }
    }

    setupCartFunctionality() {
        // Add to cart buttons
        document.querySelectorAll('.btn-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.addToCart(button);
            });
        });

        // Quantity update buttons
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.updateQuantity(button);
            });
        });
    }

    setupSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(searchInput.value);
                }, 300);
            });
        }
    }

    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let message = '';

        if (!value && field.required) {
            isValid = false;
            message = 'Trường này là bắt buộc';
        } else if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Email không hợp lệ';
            }
        } else if (type === 'tel' && value) {
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(value.replace(/\s+/g, ''))) {
                isValid = false;
                message = 'Số điện thoại không hợp lệ';
            }
        }

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            this.showFieldError(field, message);
        }

        return isValid;
    }

    showFieldError(field, message) {
        let errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    addToCart(button) {
        const productId = button.dataset.productId || 
                         button.closest('form').querySelector('input[name="product_id"]').value;
        
        // Animation
        button.innerHTML = '<div class="loading"></div>';
        button.disabled = true;

        // Simulate API call
        setTimeout(() => {
            this.updateCartBadge();
            this.showNotification('Đã thêm sản phẩm vào giỏ hàng!', 'success');
            
            button.innerHTML = '<i class="fas fa-check me-2"></i>Đã thêm';
            button.classList.remove('btn-cart');
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ';
                button.classList.remove('btn-success');
                button.classList.add('btn-cart');
                button.disabled = false;
            }, 2000);
        }, 1000);
    }

    updateQuantity(button) {
        const action = button.dataset.action;
        const input = button.parentNode.querySelector('input[type="number"]');
        let quantity = parseInt(input.value);

        if (action === 'increase') {
            quantity++;
        } else if (action === 'decrease' && quantity > 1) {
            quantity--;
        }

        input.value = quantity;
        this.updateCartTotal();
    }

    updateCartBadge() {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            let count = parseInt(badge.textContent) + 1;
            badge.textContent = count;
            badge.classList.add('pulse');
            setTimeout(() => badge.classList.remove('pulse'), 500);
        }
    }

    updateCartTotal() {
        // Calculate and update cart total
        let total = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const price = parseFloat(item.dataset.price);
            const quantity = parseInt(item.querySelector('input[type="number"]').value);
            total += price * quantity;
        });

        const totalElement = document.querySelector('.cart-total');
        if (totalElement) {
            totalElement.textContent = this.formatPrice(total);
        }
    }

    performSearch(keyword) {
        if (keyword.length < 2) return;

        // Show loading state
        const searchButton = document.querySelector('.search-box').nextElementSibling;
        searchButton.innerHTML = '<div class="loading"></div>';

        // Simulate search delay
        setTimeout(() => {
            searchButton.innerHTML = '<i class="fas fa-search"></i>';
            // Here you would typically make an AJAX request
            console.log('Searching for:', keyword);
        }, 500);
    }

    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification alert alert-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            this.fadeOut(notification);
        }, 3000);
    }

    fadeOut(element) {
        element.style.transition = 'opacity 0.5s ease';
        element.style.opacity = '0';
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }, 500);
    }

    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new FPolyShop();
});

// Utility functions
window.FPolyUtils = {
    // Format number with thousand separators
    formatNumber: (num) => {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },

    // Validate Vietnamese phone number
    isValidPhone: (phone) => {
        const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
        return phoneRegex.test(phone);
    },

    // Get URL parameter
    getUrlParameter: (name) => {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    // Set cookie
    setCookie: (name, value, days) => {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    },

    // Get cookie
    getCookie: (name) => {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
};