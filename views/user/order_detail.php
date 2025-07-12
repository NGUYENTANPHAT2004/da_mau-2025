<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Chi tiết đơn hàng #<?php echo $order['id']; ?></h2>
                <a href="orders" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Sản phẩm đã đặt</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($order_items)): ?>
                        <p class="text-muted">Không có sản phẩm nào trong đơn hàng này.</p>
                    <?php else: ?>
                        <?php foreach($order_items as $item): ?>
                            <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" 
                                     class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                <div class="ml-3 flex-grow-1">
                                    <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                    <p class="text-muted mb-1">Số lượng: <?php echo $item['quantity']; ?></p>
                                    <p class="mb-0">
                                        <strong><?php echo number_format($item['price']); ?> VNĐ</strong>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <strong><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Trạng thái:</strong>
                        <span class="badge badge-<?php echo getStatusBadge($order['status']); ?> ml-2">
                            <?php echo getStatusText($order['status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Ngày đặt:</strong><br>
                        <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Phương thức thanh toán:</strong><br>
                        <span class="text-muted"><?php echo $order['payment_method']; ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Địa chỉ giao hàng:</strong><br>
                        <span class="text-muted"><?php echo $order['shipping_address']; ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($order['total_amount']); ?> VNĐ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-primary h5"><?php echo number_format($order['total_amount']); ?> VNĐ</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 