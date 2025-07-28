<!-- views/checkout/index.php -->
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-credit-card me-2"></i>Thanh toán</h2>
            
            <!-- Progress Steps -->
            <div class="checkout-progress mb-4">
                <div class="progress-step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Giỏ hàng</div>
                </div>
                <div class="progress-step active">
                    <div class="step-number">2</div>
                    <div class="step-label">Thông tin</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-label">Thanh toán</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">4</div>
                    <div class="step-label">Hoàn thành</div>
                </div>
            </div>
        </div>
    </div>

    <form id="checkoutForm" method="POST">
        <div class="row">
            <!-- Billing Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user me-2"></i>Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Họ và tên *</label>
                                    <input type="text" name="billing_name" class="form-control" 
                                           value="<?php echo $user_info['fullname'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="billing_email" class="form-control" 
                                           value="<?php echo $user_info['email'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Số điện thoại *</label>
                                    <input type="tel" name="billing_phone" class="form-control" 
                                           value="<?php echo $user_info['phone'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Thành phố *</label>
                                    <select name="billing_city" class="form-select" required>
                                        <option value="">Chọn thành phố</option>
                                        <option value="hanoi">Hà Nội</option>
                                        <option value="hcm">TP. Hồ Chí Minh</option>
                                        <option value="danang">Đà Nẵng</option>
                                        <option value="haiphong">Hải Phòng</option>
                                        <option value="cantho">Cần Thơ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Địa chỉ *</label>
                            <textarea name="billing_address" class="form-control" rows="3" 
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện" required><?php echo $user_info['address'] ?? ''; ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-shipping-fast me-2"></i>Thông tin giao hàng</h5>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sameAsBilling" checked>
                                <label class="form-check-label" for="sameAsBilling">
                                    Giống thông tin thanh toán
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="shippingInfo" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Họ tên người nhận</label>
                                    <input type="text" name="shipping_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="tel" name="shipping_phone" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Địa chỉ giao hàng</label>
                            <textarea name="shipping_address" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Thành phố</label>
                                    <select name="shipping_city" class="form-select">
                                        <option value="">Chọn thành phố</option>
                                        <option value="hanoi">Hà Nội</option>
                                        <option value="hcm">TP. Hồ Chí Minh</option>
                                        <option value="danang">Đà Nẵng</option>
                                        <option value="haiphong">Hải Phòng</option>
                                        <option value="cantho">Cần Thơ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Mã bưu điện</label>
                                    <input type="text" name="shipping_zipcode" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-credit-card me-2"></i>Phương thức thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-methods">
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="cod" value="cod" checked>
                                <label class="form-check-label" for="cod">
                                    <div class="payment-info">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <span>Thanh toán khi nhận hàng (COD)</span>
                                    </div>
                                    <small class="text-muted">Thanh toán bằng tiền mặt khi nhận hàng</small>
                                </label>
                            </div>
                            
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="vnpay" value="vnpay">
                                <label class="form-check-label" for="vnpay">
                                    <div class="payment-info">
                                        <i class="fas fa-credit-card text-primary"></i>
                                        <span>Thanh toán qua VNPay</span>
                                    </div>
                                    <small class="text-muted">Thanh toán qua thẻ ATM, Visa, MasterCard</small>
                                </label>
                            </div>
                            
                            <div class="form-check payment-option">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="bank_transfer" value="bank_transfer">
                                <label class="form-check-label" for="bank_transfer">
                                    <div class="payment-info">
                                        <i class="fas fa-university text-info"></i>
                                        <span>Chuyển khoản ngân hàng</span>
                                    </div>
                                    <small class="text-muted">Chuyển khoản trực tiếp vào tài khoản</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-sticky-note me-2"></i>Ghi chú đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn."></textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card order-summary">
                    <div class="card-header">
                        <h5><i class="fas fa-shopping-bag me-2"></i>Đơn hàng của bạn</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="order-items">
                            <?php foreach($cart_items as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                        <span class="quantity"><?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="item-details">
                                        <h6><?php echo $item['name']; ?></h6>
                                        <span class="price">
                                            <?php 
                                            $price = $item['sale_price'] ?? $item['price'];
                                            echo number_format($price * $item['quantity']); 
                                            ?> VNĐ
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Coupon Code -->
                        <div class="coupon-section mt-3">
                            <div class="input-group">
                                <input type="text" name="coupon_code" class="form-control" 
                                       placeholder="Mã giảm giá">
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="applyCoupon()">Áp dụng</button>
                            </div>
                            <div id="couponMessage" class="mt-2"></div>
                        </div>

                        <!-- Order Totals -->
                        <div class="order-totals mt-4">
                            <div class="total-line">
                                <span>Tạm tính:</span>
                                <span id="subtotal"><?php echo number_format($cart_total); ?> VNĐ</span>
                            </div>
                            <div class="total-line">
                                <span>Phí vận chuyển:</span>
                                <span id="shipping"><?php echo number_format($shipping_cost); ?> VNĐ</span>
                            </div>
                            <div class="total-line">
                                <span>Thuế VAT:</span>
                                <span id="tax"><?php echo number_format($tax_amount); ?> VNĐ</span>
                            </div>
                            <div class="total-line discount-line" id="discountLine" style="display: none;">
                                <span>Giảm giá:</span>
                                <span id="discount" class="text-success">-0 VNĐ</span>
                            </div>
                            <hr>
                            <div class="total-line final-total">
                                <span><strong>Tổng cộng:</strong></span>
                                <span id="finalTotal"><strong><?php echo number_format($final_total); ?> VNĐ</strong></span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" id="placeOrderBtn">
                            <i class="fas fa-lock me-2"></i>Đặt hàng
                        </button>

                        <!-- Security Notice -->
                        <div class="security-notice mt-3">
                            <i class="fas fa-shield-alt text-success"></i>
                            <small class="text-muted">Thông tin của bạn được bảo mật an toàn</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* Checkout Styles */
.checkout-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 2rem 0;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
}

.progress-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 60%;
    width: 80%;
    height: 2px;
    background: #e9ecef;
}

.progress-step.active:not(:last-child)::after {
    background: var(--primary-color);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.progress-step.active .step-number {
    background: var(--primary-color);
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.progress-step.active .step-label {
    color: var(--primary-color);
    font-weight: 600;
}

.payment-option {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.payment-option:hover {
    border-color: var(--primary-color);
}

.payment-option input:checked + label {
    color: var(--primary-color);
}

.payment-info {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.payment-info i {
    font-size: 1.5rem;
    margin-right: 0.75rem;
    width: 30px;
}

.order-summary {
    position: sticky;
    top: 2rem;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #e9ecef;
}

.item-image {
    position: relative;
    margin-right: 1rem;
}

.item-image img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.quantity {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

.item-details h6 {
    margin: 0 0 0.25rem 0;
    font-size: 0.875rem;
}

.item-details .price {
    color: var(--primary-color);
    font-weight: 600;
}

.total-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.final-total {
    font-size: 1.125rem;
}

.discount-line {
    color: var(--success-color);
}

.security-notice {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}
</style>

<script>
$(document).ready(function() {
    // Toggle shipping info
    $('#sameAsBilling').change(function() {
        if(this.checked) {
            $('#shippingInfo').slideUp();
        } else {
            $('#shippingInfo').slideDown();
        }
    });

    // Checkout form submission
    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#placeOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...');
        
        $.ajax({
            url: 'checkout/process',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    if(response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert(response.message);
                    $('#placeOrderBtn').prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Đặt hàng');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra, vui lòng thử lại!');
                $('#placeOrderBtn').prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Đặt hàng');
            }
        });
    });
});

function applyCoupon() {
    const couponCode = $('input[name="coupon_code"]').val();
    if(!couponCode) {
        alert('Vui lòng nhập mã giảm giá');
        return;
    }

    $.ajax({
        url: 'checkout/apply-coupon',
        type: 'POST',
        data: { coupon_code: couponCode },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#discountLine').show();
                $('#discount').text('-' + new Intl.NumberFormat('vi-VN').format(response.discount) + ' VNĐ');
                $('#finalTotal strong').text(new Intl.NumberFormat('vi-VN').format(response.new_total) + ' VNĐ');
                $('#couponMessage').html('<small class="text-success"><i class="fas fa-check-circle"></i> ' + response.message + '</small>');
            } else {
                $('#couponMessage').html('<small class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + response.message + '</small>');
            }
        },
        error: function() {
            $('#couponMessage').html('<small class="text-danger">Có lỗi xảy ra!</small>');
        }
    });
}
</script>

<!-- views/checkout/success.php -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card success-card">
                <div class="card-body text-center py-5">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    
                    <h2 class="text-success mb-3">Đặt hàng thành công!</h2>
                    <p class="lead mb-4">
                        Cảm ơn bạn đã mua hàng. Đơn hàng <strong>#<?php echo $order['order_number']; ?></strong> 
                        đã được xác nhận và đang được xử lý.
                    </p>
                    
                    <div class="order-info bg-light p-4 rounded mb-4">
                        <div class="row text-start">
                            <div class="col-md-6">
                                <p><strong>Mã đơn hàng:</strong> #<?php echo $order['order_number']; ?></p>
                                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Tổng tiền:</strong> <span class="text-primary"><?php echo number_format($order['total_amount']); ?> VNĐ</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Phương thức thanh toán:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-warning">Chờ xử lý</span>
                                </p>
                                <p><strong>Email:</strong> <?php echo $order['billing_email']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="next-steps">
                        <h5 class="mb-3">Bước tiếp theo:</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="step-card">
                                    <i class="fas fa-envelope text-primary mb-2"></i>
                                    <h6>Kiểm tra email</h6>
                                    <small class="text-muted">Chúng tôi đã gửi xác nhận đơn hàng đến email của bạn</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="step-card">
                                    <i class="fas fa-box text-info mb-2"></i>
                                    <h6>Chuẩn bị hàng</h6>
                                    <small class="text-muted">Chúng tôi sẽ chuẩn bị và đóng gói đơn hàng của bạn</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="step-card">
                                    <i class="fas fa-shipping-fast text-success mb-2"></i>
                                    <h6>Giao hàng</h6>
                                    <small class="text-muted">Đơn hàng sẽ được giao trong 2-3 ngày làm việc</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons mt-4">
                        <a href="order-detail/<?php echo $order['id']; ?>" class="btn btn-outline-primary me-3">
                            <i class="fas fa-eye me-2"></i>Xem chi tiết đơn hàng
                        </a>
                        <a href="products" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-card {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.success-icon i {
    font-size: 4rem;
}

.step-card {
    text-align: center;
    padding: 1rem;
}

.step-card i {
    font-size: 2rem;
    display: block;
}
</style>