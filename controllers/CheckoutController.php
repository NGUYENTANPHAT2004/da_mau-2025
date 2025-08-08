<?php
class CheckoutController {
    private $db;
    private $cart;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cart = new Cart($this->db);
        $this->order = new Order($this->db);
    }

    public function index() {
        if (!isLoggedIn()) {
            redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];
        $cart_items = $this->cart->getCartItems($user_id);
        
        if (empty($cart_items)) {
            showAlert('Giỏ hàng của bạn đang trống', 'warning');
            redirect(BASE_URL . 'cart');
            return;
        }

        $cart_total = $this->cart->getCartTotal($user_id);
        $shipping_fee = $this->calculateShipping($cart_total);
        $tax_amount = $this->calculateTax($cart_total);
        $final_total = $cart_total + $shipping_fee + $tax_amount;

        include 'views/layouts/header.php';
        include 'views/checkout/index.php';
        include 'views/layouts/footer.php';
    }

    public function process() {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $cart_items = $this->cart->getCartItems($user_id);
        
        if (empty($cart_items)) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            return;
        }

        // Validate checkout data
        $validation = $this->validateCheckoutData($_POST);
        if (!$validation['success']) {
            echo json_encode($validation);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Calculate totals
            $cart_total = $this->cart->getCartTotal($user_id);
            $shipping_fee = $this->calculateShipping($cart_total);
            $tax_amount = $this->calculateTax($cart_total);
            $discount_amount = 0;

            // Apply coupon if provided
            if (!empty($_POST['coupon_code'])) {
                $discount_amount = $this->applyCoupon($_POST['coupon_code'], $cart_total);
            }

            $final_total = $cart_total + $shipping_fee + $tax_amount - $discount_amount;

            // Create order - UPDATED FOR DATABASE STRUCTURE
            $order_data = [
                'user_id' => $user_id,
                'order_number' => 'ORD' . time() . rand(1000, 9999),
                'status' => 'pending',
                'total_amount' => $final_total,  // Changed from 'total'
                'subtotal' => $cart_total,
                'tax_amount' => $tax_amount,
                'shipping_amount' => $shipping_fee,  // Changed from 'shipping_fee'
                'discount_amount' => $discount_amount,
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
                'payment_status' => 'pending',
                'notes' => $_POST['notes'] ?? ''
            ];

            $order_id = $this->order->create($order_data);
            
            if (!$order_id) {
                throw new Exception('Không thể tạo đơn hàng');
            }

            // Add order items - UPDATED FOR DATABASE STRUCTURE
            foreach ($cart_items as $item) {
                $item_data = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity']
                ];
                
                if (!$this->order->addOrderItem($order_id, $item_data)) {
                    throw new Exception('Không thể thêm sản phẩm vào đơn hàng');
                }
            }

            // Clear cart
            $this->cart->clearCart($user_id);

            // Send notification if function exists
            if (function_exists('sendNotification')) {
                sendNotification($user_id, 'order', 'Đơn hàng mới', 
                               'Đơn hàng #' . $order_data['order_number'] . ' đã được tạo thành công');
            }

            $this->db->commit();

            echo json_encode([
                'success' => true, 
                'message' => 'Đặt hàng thành công',
                'order_id' => $order_id,
                'redirect' => BASE_URL . 'order-success/' . $order_id
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Checkout Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function success($order_id) {
        if (!isLoggedIn()) {
            redirect(BASE_URL . 'login');
            return;
        }

        $order = $this->order->getById($order_id);
        
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            redirect(BASE_URL . 'orders');
            return;
        }

        $order_items = $this->order->getOrderItems($order_id);

        include 'views/layouts/header.php';
        include 'views/checkout/success.php';
        include 'views/layouts/footer.php';
    }

    public function applyCouponAjax() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        $coupon_code = $_POST['coupon_code'] ?? '';
        
        if (empty($coupon_code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $cart_total = $this->cart->getCartTotal($user_id);
        
        if ($cart_total <= 0) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            return;
        }

        $discount = $this->applyCoupon($coupon_code, $cart_total);
        
        if ($discount > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Áp dụng mã giảm giá thành công',
                'discount' => $discount,
                'new_total' => $cart_total - $discount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn']);
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

        foreach ($required_fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "$label là bắt buộc";
            }
        }

        // Validate email
        if (!empty($data['billing_email']) && !filter_var($data['billing_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ";
        }

        // Validate phone
        if (!empty($data['billing_phone']) && !preg_match('/^[0-9]{10,11}$/', $data['billing_phone'])) {
            $errors[] = "Số điện thoại không hợp lệ";
        }

        // Validate payment method
        $valid_methods = ['cod', 'vnpay', 'momo', 'bank_transfer'];
        if (!in_array($data['payment_method'], $valid_methods)) {
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
        try {
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
            
            if ($coupon) {
                if ($coupon['type'] === 'fixed') {
                    return min($coupon['value'], $cart_total);
                } else { // percentage
                    $discount = $cart_total * ($coupon['value'] / 100);
                    return $coupon['max_discount'] ? min($discount, $coupon['max_discount']) : $discount;
                }
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("Coupon Apply Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>