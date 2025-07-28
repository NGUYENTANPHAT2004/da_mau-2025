<?php
class CheckoutController {
    private $db;
    private $cart;
    private $order;
    private $payment;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cart = new Cart($this->db);
        $this->order = new Order($this->db);
        $this->payment = new Payment($this->db);
        $this->user = new User($this->db);
    }

    public function index() {
        if(!isLoggedIn()) {
            redirect('login');
            return;
        }

        $cart_items = $this->cart->getCartItems($_SESSION['user_id']);
        if(empty($cart_items)) {
            redirect('cart');
            return;
        }

        $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
        $user_info = $this->user->getUserById($_SESSION['user_id']);
        $shipping_cost = $this->calculateShipping($cart_total);
        $tax_amount = $this->calculateTax($cart_total);
        $final_total = $cart_total + $shipping_cost + $tax_amount;

        include 'views/layouts/header.php';
        include 'views/checkout/index.php';
        include 'views/layouts/footer.php';
    }

    public function process() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            // Validate dữ liệu
            $validation = $this->validateCheckoutData($_POST);
            if(!$validation['success']) {
                echo json_encode($validation);
                return;
            }

            // Lấy thông tin giỏ hàng
            $cart_items = $this->cart->getCartItems($_SESSION['user_id']);
            if(empty($cart_items)) {
                echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
                return;
            }

            // Tính toán
            $subtotal = $this->cart->getCartTotal($_SESSION['user_id']);
            $shipping_cost = $this->calculateShipping($subtotal);
            $tax_amount = $this->calculateTax($subtotal);
            $discount_amount = 0;

            // Áp dụng coupon nếu có
            if(!empty($_POST['coupon_code'])) {
                $coupon_discount = $this->applyCoupon($_POST['coupon_code'], $subtotal);
                if($coupon_discount > 0) {
                    $discount_amount = $coupon_discount;
                }
            }

            $total_amount = $subtotal + $shipping_cost + $tax_amount - $discount_amount;

            // Tạo đơn hàng
            $order_data = [
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $_SESSION['user_id'],
                'subtotal' => $subtotal,
                'shipping_amount' => $shipping_cost,
                'tax_amount' => $tax_amount,
                'discount_amount' => $discount_amount,
                'total_amount' => $total_amount,
                'coupon_code' => $_POST['coupon_code'] ?? null,
                'billing_name' => $_POST['billing_name'],
                'billing_email' => $_POST['billing_email'],
                'billing_phone' => $_POST['billing_phone'],
                'billing_address' => $_POST['billing_address'],
                'billing_city' => $_POST['billing_city'],
                'billing_state' => $_POST['billing_state'] ?? '',
                'billing_zipcode' => $_POST['billing_zipcode'] ?? '',
                'shipping_name' => $_POST['shipping_name'] ?? $_POST['billing_name'],
                'shipping_phone' => $_POST['shipping_phone'] ?? $_POST['billing_phone'],
                'shipping_address' => $_POST['shipping_address'] ?? $_POST['billing_address'],
                'shipping_city' => $_POST['shipping_city'] ?? $_POST['billing_city'],
                'shipping_state' => $_POST['shipping_state'] ?? $_POST['billing_state'],
                'shipping_zipcode' => $_POST['shipping_zipcode'] ?? $_POST['billing_zipcode'],
                'payment_method' => $_POST['payment_method'],
                'notes' => $_POST['notes'] ?? ''
            ];

            $order_id = $this->order->create($order_data);
            
            if($order_id) {
                // Thêm order items
                foreach($cart_items as $item) {
                    $this->order->addOrderItem($order_id, [
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'product_sku' => $item['sku'] ?? '',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['sale_price'] ?? $item['price'],
                        'total_price' => ($item['sale_price'] ?? $item['price']) * $item['quantity']
                    ]);
                }

                // Xóa giỏ hàng
                $this->cart->clearCart($_SESSION['user_id']);

                // Xử lý thanh toán
                if($_POST['payment_method'] === 'vnpay') {
                    $vnpay_url = $this->payment->createVNPayURL(
                        $order_id, 
                        $total_amount, 
                        "Thanh toán đơn hàng #{$order_data['order_number']}"
                    );
                    echo json_encode([
                        'success' => true, 
                        'redirect' => $vnpay_url,
                        'message' => 'Chuyển hướng đến VNPay...'
                    ]);
                } else {
                    // COD hoặc bank transfer
                    echo json_encode([
                        'success' => true, 
                        'redirect' => 'order-success/' . $order_id,
                        'message' => 'Đặt hàng thành công!'
                    ]);
                }

                // Gửi email xác nhận
                $this->sendOrderConfirmationEmail($order_id);

            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng']);
            }
        }
    }

    public function success($order_id) {
        if(!isLoggedIn()) {
            redirect('login');
            return;
        }

        $order = $this->order->getById($order_id);
        if(!$order || $order['user_id'] != $_SESSION['user_id']) {
            redirect('home');
            return;
        }

        $order_items = $this->order->getOrderItems($order_id);

        include 'views/layouts/header.php';
        include 'views/checkout/success.php';
        include 'views/layouts/footer.php';
    }

    public function applyCoupon() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST && isset($_POST['coupon_code'])) {
            $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
            $discount = $this->applyCoupon($_POST['coupon_code'], $cart_total);
            
            if($discount > 0) {
                echo json_encode([
                    'success' => true, 
                    'discount' => $discount,
                    'message' => 'Áp dụng mã giảm giá thành công!',
                    'new_total' => $cart_total - $discount
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn']);
            }
        }
    }

    private function validateCheckoutData($data) {
        $errors = [];

        // Validate required fields
        $required_fields = [
            'billing_name' => 'Họ tên',
            'billing_email' => 'Email',
            'billing_phone' => 'Số điện thoại',
            'billing_address' => 'Địa chỉ',
            'billing_city' => 'Thành phố',
            'payment_method' => 'Phương thức thanh toán'
        ];

        foreach($required_fields as $field => $label) {
            if(empty($data[$field])) {
                $errors[] = "$label là bắt buộc";
            }
        }

        // Validate email
        if(!empty($data['billing_email']) && !filter_var($data['billing_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ";
        }

        // Validate phone
        if(!empty($data['billing_phone']) && !preg_match('/^[0-9]{10,11}$/', $data['billing_phone'])) {
            $errors[] = "Số điện thoại không hợp lệ";
        }

        // Validate payment method
        $valid_methods = ['cod', 'vnpay', 'bank_transfer'];
        if(!in_array($data['payment_method'], $valid_methods)) {
            $errors[] = "Phương thức thanh toán không hợp lệ";
        }

        return [
            'success' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? 'Validation passed' : implode(', ', $errors)
        ];
    }

    private function calculateShipping($cart_total) {
        // Miễn phí ship cho đơn hàng trên 500k
        $free_shipping_min = 500000;
        return $cart_total >= $free_shipping_min ? 0 : 30000;
    }

    private function calculateTax($cart_total) {
        // VAT 10%
        return $cart_total * 0.1;
    }

    private function applyCoupon($coupon_code, $cart_total) {
        $query = "SELECT * FROM coupons 
                  WHERE code = :code 
                  AND status = 'active' 
                  AND (start_date IS NULL OR start_date <= NOW()) 
                  AND (end_date IS NULL OR end_date >= NOW())
                  AND (usage_limit IS NULL OR used_count < usage_limit)
                  AND min_amount <= :cart_total";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $coupon_code);
        $stmt->bindParam(':cart_total', $cart_total);
        $stmt->execute();
        
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($coupon) {
            if($coupon['type'] === 'fixed') {
                return min($coupon['value'], $cart_total);
            } else { // percentage
                $discount = $cart_total * ($coupon['value'] / 100);
                return $coupon['max_discount'] ? min($discount, $coupon['max_discount']) : $discount;
            }
        }
        
        return 0;
    }

    private function generateOrderNumber() {
        return 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function sendOrderConfirmationEmail($order_id) {
        // Implement email sending logic here
        // You can use PHPMailer or similar library
        $order = $this->order->getById($order_id);
        
        // For now, just log the email (you should implement actual email sending)
        error_log("Order confirmation email should be sent for order #{$order['order_number']}");
    }
}
?>