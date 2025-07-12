<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Lịch sử đơn hàng</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if(empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Chưa có đơn hàng nào</h4>
                            <p class="text-muted">Bạn chưa đặt đơn hàng nào.</p>
                            <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary">Mua sắm ngay</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $order['id']; ?></strong>
                                                <br><small class="text-muted"><?php echo $order['payment_method']; ?></small>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td><strong><?php echo number_format($order['total_amount']); ?> VNĐ</strong></td>
                                            <td>
                                                <span class="badge badge-<?php echo getStatusBadge($order['status']); ?>">
                                                    <?php echo getStatusText($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>order-detail/<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 