<div class="container mt-4">
    <h2>Giỏ hàng của bạn</h2>
    
    <?php if(empty($cart_items)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Giỏ hàng trống</h4>
            <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
            <a href="<?php echo BASE_URL; ?>products" class="btn btn-primary">Tiếp tục mua sắm</a>
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
                                        <tr id="cart-item-<?php echo $item['product_id']; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $item['image'] ? BASE_URL . 'uploads/products/' . $item['image'] : 'https://via.placeholder.com/60x60'; ?>" 
                                                         alt="<?php echo $item['name']; ?>" 
                                                         class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                        <small class="text-muted">Mã: #<?php echo $item['product_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                    <span class="text-danger fw-bold"><?php echo number_format($item['sale_price']); ?> VNĐ</span>
                                                    <br><small class="text-muted text-decoration-line-through"><?php echo number_format($item['price']); ?> VNĐ</small>
                                                <?php else: ?>
                                                    <span class="fw-bold"><?php echo number_format($item['price']); ?> VNĐ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="input-group" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                                    <input type="number" class="form-control form-control-sm text-center" 
                                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                                           onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)"
                                                           id="qty-<?php echo $item['product_id']; ?>">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <strong class="item-total" data-product="<?php echo $item['product_id']; ?>">
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
                        
                        <div class="text-end">
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
                            <span id="cart-subtotal"><?php echo number_format($cart_total); ?> VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-primary h5" id="cart-total"><?php echo number_format($cart_total); ?> VNĐ</strong>
                        </div>
                        
                        <button class="btn btn-success btn-lg w-100 mb-3" onclick="proceedToCheckout()">
                            <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                        </button>
                        
                        <div class="text-center">
                            <a href="<?php echo BASE_URL; ?>products" class="btn btn-outline-primary">
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
    quantity = parseInt(quantity);
    
    if(quantity < 1) {
        if(confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            removeItem(productId);
        } else {
            // Reset quantity to 1
            document.getElementById('qty-' + productId).value = 1;
        }
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch(BASE_URL + 'update-cart', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Update quantity input
            document.getElementById('qty-' + productId).value = quantity;
            
            // Update cart totals
            updateCartDisplay(data);
            updateCartCount();
            
            showAlert('Cập nhật thành công', 'success');
        } else {
            showAlert(data.message, 'error');
            // Reset quantity on error
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra!', 'error');
    });
}

function removeItem(productId) {
    if(!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch(BASE_URL + 'remove-from-cart', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Remove item row
            const itemRow = document.getElementById('cart-item-' + productId);
            if(itemRow) {
                itemRow.remove();
            }
            
            // Update cart totals
            updateCartDisplay(data);
            updateCartCount();
            
            // Check if cart is empty
            const remainingItems = document.querySelectorAll('[id^="cart-item-"]');
            if(remainingItems.length === 0) {
                location.reload();
            }
            
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra!', 'error');
    });
}

function clearCart() {
    if(!confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
        return;
    }
    
    fetch(BASE_URL + 'clear-cart', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            updateCartCount();
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra!', 'error');
    });
}

function updateCartDisplay(data) {
    if(data.cart_total !== undefined) {
        const subtotal = document.getElementById('cart-subtotal');
        const total = document.getElementById('cart-total');
        
        if(subtotal) subtotal.textContent = data.cart_total + ' VNĐ';
        if(total) total.textContent = data.cart_total + ' VNĐ';
    }
}

function proceedToCheckout() {
    window.location.href = BASE_URL + 'checkout';
}
</script>