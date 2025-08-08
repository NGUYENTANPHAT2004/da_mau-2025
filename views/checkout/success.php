<!-- views/checkout/success.php -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="text-success mb-3">Đặt hàng thành công!</h1>
                <p class="lead text-muted">
                    Cảm ơn bạn đã tin tướng và mua sắm tại <strong><?php echo SITE_NAME; ?></strong>
                </p>
            </div>

            <!-- Order Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-receipt me-2"></i>Thông tin đơn hàng
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Mã đơn hàng:</h6>
                            <p class="fw-bold text-primary fs-5">#<?php echo $order['order_number']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Ngày đặt:</h6>
                            <p class="fw-bold"><?php echo formatDate($order['created_at']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Tổng tiền:</h6>
                            <p class="fw-bold text-success fs-4"><?php echo formatPrice($order['total_amount']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Trạng thái:</h6>
                            <p><?php echo getStatusBadge($order['status']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Phương thức thanh toán:</h6>
                            <p class="fw-bold">
                                <?php 
                                $payment_methods = [
                                    'cod' => 'Thanh toán khi nhận hàng',
                                    'vnpay' => 'VNPay',
                                    'momo' => 'MoMo',
                                    'bank_transfer' => 'Chuyển khoản ngân hàng'
                                ];
                                echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Tình trạng thanh toán:</h6>
                            <p>
                                <?php 
                                $payment_status_badges = [
                                    'pending' => '<span class="badge bg-warning">Chờ thanh toán</span>',
                                    'paid' => '<span class="badge bg-success">Đã thanh toán</span>',
                                    'failed' => '<span class="badge bg-danger">Thanh toán thất bại</span>',
                                    'refunded' => '<span class="badge bg-info">Đã hoàn tiền</span>'
                                ];
                                echo $payment_status_badges[$order['payment_status']] ?? '<span class="badge bg-secondary">' . $order['payment_status'] . '</span>';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>Sản phẩm đã đặt
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                                 alt="<?php echo $item['name']; ?>" 
                                                 class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                                <?php if(!empty($item['product_sku'])): ?>
                                                <small class="text-muted">SKU: <?php echo $item['product_sku']; ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-primary"><?php echo $item['quantity']; ?></span>
                                    </td>
                                    <td class="text-end align-middle">
                                        <?php echo formatPrice($item['unit_price']); ?>
                                    </td>
                                    <td class="text-end align-middle fw-bold">
                                        <?php echo formatPrice($item['total_price']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Summary -->
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tạm tính:</span>
                                        <span><?php echo formatPrice($order['subtotal']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Phí vận chuyển:</span>
                                        <span class="text-success">
                                            <?php echo $order['shipping_amount'] > 0 ? formatPrice($order['shipping_amount']) : 'Miễn phí'; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Thuế (VAT):</span>
                                        <span><?php echo formatPrice($order['tax_amount']); ?></span>
                                    </div>
                                    <?php if($order['discount_amount'] > 0): ?>
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Giảm giá:</span>
                                        <span>-<?php echo formatPrice($order['discount_amount']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold fs-5">
                                        <span>Tổng cộng:</span>
                                        <span class="text-primary"><?php echo formatPrice($order['total_amount']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-truck me-2"></i>Thông tin giao hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Người nhận:</h6>
                            <p class="fw-bold"><?php echo $order['shipping_name'] ?? $order['billing_name']; ?></p>
                            
                            <h6 class="text-muted">Số điện thoại:</h6>
                            <p><?php echo $order['shipping_phone'] ?? $order['billing_phone']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Địa chỉ giao hàng:</h6>
                            <p>
                                <?php echo $order['shipping_address'] ?? $order['billing_address']; ?><br>
                                <?php echo $order['shipping_city'] ?? $order['billing_city']; ?>
                                <?php if(!empty($order['shipping_state'] ?? $order['billing_state'])): ?>
                                , <?php echo $order['shipping_state'] ?? $order['billing_state']; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if(!empty($order['notes'])): ?>
                    <div class="mt-3">
                        <h6 class="text-muted">Ghi chú:</h6>
                        <p class="text-muted fst-italic"><?php echo $order['notes']; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Bước tiếp theo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="step-number me-3">
                                    <span class="badge bg-primary rounded-pill">1</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Xác nhận đơn hàng</h6>
                                    <small class="text-muted">Chúng tôi sẽ liên hệ để xác nhận đơn hàng trong vòng 2-4 giờ</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="step-number me-3">
                                    <span class="badge bg-warning rounded-pill">2</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Chuẩn bị hàng</h6>
                                    <small class="text-muted">Đơn hàng sẽ được chuẩn bị và đóng gói cẩn thận</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="step-number me-3">
                                    <span class="badge bg-info rounded-pill">3</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Giao hàng</h6>
                                    <small class="text-muted">Hàng sẽ được giao trong vòng 1-3 ngày làm việc</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="step-number me-3">
                                    <span class="badge bg-success rounded-pill">4</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Hoàn thành</h6>
                                    <small class="text-muted">Nhận hàng và thanh toán (nếu chọn COD)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>orders" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-list me-2"></i>Xem đơn hàng của tôi
                </a>
                <a href="<?php echo BASE_URL; ?>products" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                </a>
            </div>

            <!-- Contact Info -->
            <div class="alert alert-light mt-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="alert-heading mb-2">
                            <i class="fas fa-headset me-2"></i>Cần hỗ trợ?
                        </h6>
                        <p class="mb-0">
                            Liên hệ với chúng tôi qua hotline <strong>1900 5588</strong> 
                            hoặc email <strong>support@fpolyshop.com</strong> để được hỗ trợ nhanh nhất.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-primary">
                            <i class="fas fa-phone me-2"></i>Liên hệ ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: bounceIn 1s ease-in-out;
}

@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.step-number .badge {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

.img-thumbnail {
    border: 1px solid #dee2e6;
}
</style>

<script>
// Auto redirect to order list after 30 seconds (optional)
setTimeout(function() {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
    alertDiv.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>
        <strong>Thông báo:</strong> Trang sẽ tự động chuyển đến danh sách đơn hàng sau <span id="countdown">10</span> giây.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container .col-lg-8').appendChild(alertDiv);
    
    let countdown = 10;
    const countdownInterval = setInterval(function() {
        countdown--;
        document.getElementById('countdown').textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            window.location.href = '<?php echo BASE_URL; ?>orders';
        }
    }, 1000);
}, 20000); // Show notification after 20 seconds
</script>