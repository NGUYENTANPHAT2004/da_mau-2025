<!-- views/checkout/index.php -->
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Thông tin đặt hàng
                    </h4>
                </div>
                <div class="card-body">
                    <form id="checkoutForm" method="POST" action="<?php echo BASE_URL; ?>checkout/process">
                        <!-- Thông tin người nhận -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-user me-2"></i>Thông tin người nhận
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="billing_name" name="billing_name" 
                                       value="<?php echo $_SESSION['user_name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="billing_email" name="billing_email" 
                                       value="<?php echo $_SESSION['user_email'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="billing_phone" name="billing_phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_city" class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                <select class="form-select" id="billing_city" name="billing_city" required>
                                    <option value="">Chọn tỉnh/thành phố</option>
                                    <option value="Hà Nội">Hà Nội</option>
                                    <option value="TP.HCM">TP. Hồ Chí Minh</option>
                                    <option value="Đà Nẵng">Đà Nẵng</option>
                                    <option value="Hải Phòng">Hải Phòng</option>
                                    <option value="Cần Thơ">Cần Thơ</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="billing_address" class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="billing_address" name="billing_address" 
                                          rows="3" placeholder="Số nhà, tên đường, phường/xã..." required></textarea>
                            </div>
                        </div>

                        <!-- Checkbox giao hàng khác địa chỉ -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="different_shipping">
                            <label class="form-check-label" for="different_shipping">
                                Giao hàng đến địa chỉ khác
                            </label>
                        </div>

                        <!-- Thông tin giao hàng (ẩn mặc định) -->
                        <div id="shipping_info" class="d-none">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-truck me-2"></i>Thông tin giao hàng
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shipping_name" class="form-label">Họ và tên người nhận</label>
                                    <input type="text" class="form-control" id="shipping_name" name="shipping_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shipping_phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shipping_city" class="form-label">Tỉnh/Thành phố</label>
                                    <select class="form-select" id="shipping_city" name="shipping_city">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        <option value="Hà Nội">Hà Nội</option>
                                        <option value="TP.HCM">TP. Hồ Chí Minh</option>
                                        <option value="Đà Nẵng">Đà Nẵng</option>
                                        <option value="Hải Phòng">Hải Phòng</option>
                                        <option value="Cần Thơ">Cần Thơ</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="shipping_address" class="form-label">Địa chỉ cụ thể</label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                              rows="3" placeholder="Số nhà, tên đường, phường/xã..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-credit-card me-2"></i>Phương thức thanh toán
                                </h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check payment-method">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="cod" value="cod" checked>
                                            <label class="form-check-label" for="cod">
                                                <i class="fas fa-money-bill-wave text-success me-2"></i>
                                                Thanh toán khi nhận hàng (COD)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check payment-method">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="vnpay" value="vnpay">
                                            <label class="form-check-label" for="vnpay">
                                                <i class="fas fa-credit-card text-primary me-2"></i>
                                                Thanh toán VNPay
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check payment-method">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="bank_transfer" value="bank_transfer">
                                            <label class="form-check-label" for="bank_transfer">
                                                <i class="fas fa-university text-info me-2"></i>
                                                Chuyển khoản ngân hàng
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-comment me-2"></i>Ghi chú đơn hàng
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Ghi chú thêm về đơn hàng (tùy chọn)"></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>Tóm tắt đơn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Danh sách sản phẩm -->
                    <div class="order-items mb-3">
                        <?php foreach($cart_items as $item): ?>
                        <div class="d-flex align-items-center mb-3 p-2 border rounded">
                            <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                 alt="<?php echo $item['name']; ?>" class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?></small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mã giảm giá -->
                    <div class="coupon-section mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="coupon_code" name="coupon_code" 
                                   placeholder="Nhập mã giảm giá">
                            <button class="btn btn-outline-primary" type="button" id="apply_coupon">
                                Áp dụng
                            </button>
                        </div>
                        <div id="coupon_message" class="mt-2"></div>
                    </div>

                    <!-- Tính toán -->
                    <div class="order-summary">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span id="subtotal"><?php echo formatPrice($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span id="shipping_fee" class="text-success">
                                <?php echo $shipping_fee > 0 ? formatPrice($shipping_fee) : 'Miễn phí'; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Thuế (VAT 10%):</span>
                            <span id="tax_amount"><?php echo formatPrice($tax_amount); ?></span>
                        </div>
                        <div id="discount_row" class="d-flex justify-content-between mb-2 text-success d-none">
                            <span>Giảm giá:</span>
                            <span id="discount_amount">-0đ</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span id="final_total" class="text-primary"><?php echo formatPrice($final_total); ?></span>
                        </div>
                    </div>

                    <!-- Nút đặt hàng -->
                    <button type="submit" form="checkoutForm" class="btn btn-primary btn-lg w-100 mt-3" id="place_order">
                        <i class="fas fa-check-circle me-2"></i>Đặt hàng
                    </button>

                    <!-- Chính sách -->
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Bằng việc đặt hàng, bạn đồng ý với 
                            <a href="#" class="text-decoration-none">Điều khoản sử dụng</a> 
                            và <a href="#" class="text-decoration-none">Chính sách bảo mật</a> của chúng tôi.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-method {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-method:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.payment-method .form-check-input:checked ~ .form-check-label {
    color: #007bff;
    font-weight: 500;
}

.order-items .img-thumbnail {
    border: 1px solid #dee2e6;
}

.coupon-section .alert {
    margin-top: 10px;
    margin-bottom: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle shipping info
    document.getElementById('different_shipping').addEventListener('change', function() {
        const shippingInfo = document.getElementById('shipping_info');
        if (this.checked) {
            shippingInfo.classList.remove('d-none');
        } else {
            shippingInfo.classList.add('d-none');
        }
    });

    // Apply coupon
    document.getElementById('apply_coupon').addEventListener('click', function() {
        const couponCode = document.getElementById('coupon_code').value;
        const messageDiv = document.getElementById('coupon_message');
        
        if (!couponCode) {
            messageDiv.innerHTML = '<div class="alert alert-warning alert-sm">Vui lòng nhập mã giảm giá</div>';
            return;
        }

        // AJAX call
        fetch(BASE_URL + 'checkout/apply-coupon', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'coupon_code=' + encodeURIComponent(couponCode)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = '<div class="alert alert-success alert-sm">' + data.message + '</div>';
                // Update totals
                document.getElementById('discount_row').classList.remove('d-none');
                document.getElementById('discount_amount').textContent = '-' + formatPrice(data.discount);
                document.getElementById('final_total').textContent = formatPrice(data.new_total);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger alert-sm">' + data.message + '</div>';
            }
        })
        .catch(error => {
            messageDiv.innerHTML = '<div class="alert alert-danger alert-sm">Có lỗi xảy ra, vui lòng thử lại</div>';
        });
    });

    // Submit form
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('place_order');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';

        const formData = new FormData(this);
        
        // Add coupon code if applied
        const couponCode = document.getElementById('coupon_code').value;
        if (couponCode) {
            formData.append('coupon_code', couponCode);
        }

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showAlert(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            showAlert('Có lỗi xảy ra, vui lòng thử lại', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}
</script>