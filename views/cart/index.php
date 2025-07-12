<div class="container mt-4">
    <h2>Giỏ hàng của bạn</h2>
    
    <?php if(empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Giỏ hàng trống</h4>
            <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
            <a href="products" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Sản phẩm trong giỏ hàng (<?php echo count($cart_items); ?> sản phẩm)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Tổng</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" 
                                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div class="ml-3">
                                                        <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                        <small class="text-muted">Mã: #<?php echo $item['product_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                    <span class="text-danger"><?php echo number_format($item['sale_price']); ?> VNĐ</span>
                                                    <br><small class="text-muted text-decoration-line-through"><?php echo number_format($item['price']); ?> VNĐ</small>
                                                <?php else: ?>
                                                    <span><?php echo number_format($item['price']); ?> VNĐ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="input-group" style="width: 120px;">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                                onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                                    </div>
                                                    <input type="number" class="form-control form-control-sm text-center" 
                                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                                           onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                                onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php 
                                                    $price = $item['sale_price'] && $item['sale_price'] < $item['price'] ? $item['sale_price'] : $item['price'];
                                                    echo number_format($price * $item['quantity']); 
                                                    ?> VNĐ
                                                </strong>
                                            </td>
                                            <td>
                                                <button class="btn btn-danger btn-sm" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-right">
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="fas fa-trash"></i> Xóa toàn bộ giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Tổng đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($cart_total); ?> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span>Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-primary h5"><?php echo number_format($cart_total); ?> VNĐ</strong>
                        </div>
                        
                        <button class="btn btn-success btn-lg btn-block" onclick="proceedToCheckout()">
                            <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="products" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(productId, quantity) {
    if(quantity < 1) {
        if(confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            removeItem(productId);
        }
        return;
    }
    
    $.ajax({
        url: 'update-cart',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                // Cập nhật tổng tiền hiển thị
                $('.h5.text-primary').text(response.cart_total + ' VNĐ');
                // Reload trang để cập nhật toàn bộ
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
}

function removeItem(productId) {
    if(confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        $.ajax({
            url: 'remove-from-cart',
            type: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Cập nhật số lượng trong giỏ hàng
                    $('.cart-count').text(response.cart_count);
                    // Reload trang
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    }
}

function clearCart() {
    if(confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
        $.ajax({
            url: 'clear-cart',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Cập nhật số lượng trong giỏ hàng
                    $('.cart-count').text('0');
                    // Reload trang
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    }
}

function proceedToCheckout() {
    window.location.href = 'checkout';
}
</script>
