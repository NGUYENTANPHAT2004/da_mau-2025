<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Quản lý đơn hàng</h2>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tìm kiếm</label>
                                <input type="text" name="search" class="form-control" value="<?php echo $_GET['search'] ?? ''; ?>" placeholder="Mã đơn hàng, tên khách hàng...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                    <option value="processing" <?php echo ($_GET['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                    <option value="shipped" <?php echo ($_GET['status'] ?? '') == 'shipped' ? 'selected' : ''; ?>>Đã gửi hàng</option>
                                    <option value="delivered" <?php echo ($_GET['status'] ?? '') == 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                    <option value="cancelled" <?php echo ($_GET['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Từ ngày</label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo $_GET['from_date'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin-orders" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Xóa bộ lọc
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng đơn hàng -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Mã đơn hàng</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Không có đơn hàng nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $order['id']; ?></strong>
                                                <br><small class="text-muted"><?php echo $order['payment_method']; ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $order['customer_name']; ?></strong>
                                                <br><small class="text-muted"><?php echo $order['customer_email']; ?></small>
                                                <br><small class="text-muted"><?php echo $order['customer_phone']; ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td>
                                                <strong><?php echo number_format($order['total_amount']); ?> VNĐ</strong>
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm status-select" 
                                                        onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)"
                                                        style="width: auto;">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Đã gửi hàng</option>
                                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="viewOrderDetail(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-eye"></i> Chi tiết
                                                    </button>
                                                    <button class="btn btn-sm btn-success" onclick="printInvoice(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-print"></i> In
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&from_date=<?php echo $_GET['from_date'] ?? ''; ?>">Trước</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&from_date=<?php echo $_GET['from_date'] ?? ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&from_date=<?php echo $_GET['from_date'] ?? ''; ?>">Sau</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết đơn hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng #<span id="orderId"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Nội dung sẽ được load bằng AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" onclick="printInvoiceFromModal()">
                    <i class="fas fa-print"></i> In hóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateOrderStatus(orderId, status) {
    if(confirm('Bạn có chắc muốn cập nhật trạng thái đơn hàng này?')) {
        $.ajax({
            url: '<?php echo BASE_URL; ?>update-order-status',
            type: 'POST',
            data: {
                order_id: orderId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Đã cập nhật trạng thái thành công');
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    } else {
        location.reload();
    }
}

function viewOrderDetail(orderId) {
    $('#orderId').text(orderId);
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>get-order-detail/' + orderId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var order = response.order;
                var items = response.items;
                
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thông tin khách hàng</h6>
                            <p><strong>Tên:</strong> ${order.customer_name}</p>
                            <p><strong>Email:</strong> ${order.customer_email}</p>
                            <p><strong>SĐT:</strong> ${order.customer_phone}</p>
                            <p><strong>Địa chỉ:</strong> ${order.shipping_address}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin đơn hàng</h6>
                            <p><strong>Ngày đặt:</strong> ${new Date(order.created_at).toLocaleDateString('vi-VN')}</p>
                            <p><strong>Phương thức thanh toán:</strong> ${order.payment_method}</p>
                            <p><strong>Trạng thái:</strong> <span class="badge badge-${getStatusBadge(order.status)}">${getStatusText(order.status)}</span></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Sản phẩm đã đặt</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                items.forEach(function(item) {
                    html += `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${item.image}" alt="${item.name}" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div class="ml-2">
                                        <strong>${item.name}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>${new Intl.NumberFormat('vi-VN').format(item.price)} VNĐ</td>
                            <td>${item.quantity}</td>
                            <td>${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)} VNĐ</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-right">
                        <h5>Tổng cộng: ${new Intl.NumberFormat('vi-VN').format(order.total_amount)} VNĐ</h5>
                    </div>
                `;
                
                $('#orderDetailContent').html(html);
                $('#orderDetailModal').modal('show');
            } else {
                alert('Không thể tải thông tin đơn hàng');
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
}

function printInvoice(orderId) {
    window.open('<?php echo BASE_URL; ?>print-invoice/' + orderId, '_blank');
}

function printInvoiceFromModal() {
    var orderId = $('#orderId').text();
    printInvoice(orderId);
}

function getStatusBadge(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'pending': return 'Chờ xử lý';
        case 'processing': return 'Đang xử lý';
        case 'shipped': return 'Đã gửi hàng';
        case 'delivered': return 'Đã giao hàng';
        case 'cancelled': return 'Đã hủy';
        default: return 'Không xác định';
    }
}
</script>
