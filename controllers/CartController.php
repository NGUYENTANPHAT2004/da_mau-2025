<?php
class CartController {
    private $db;
    private $cart;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cart = new Cart($this->db);
        $this->product = new Product($this->db);
    }

    public function index() {
        if(!isLoggedIn()) {
            redirect(BASE_URL . 'login');
            return;
        }
        
        $action = 'cart';
        $cart_items = $this->cart->getCartItems($_SESSION['user_id']);
        $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
        
        include 'views/layouts/header.php';
        include 'views/cart/index.php';
        include 'views/layouts/footer.php';
    }

    public function add() {
        header('Content-Type: application/json');
        
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if(!isset($_POST['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $product_id = (int)$_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        // Validate quantity
        if($quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
            return;
        }
        
        // Check if product exists
        $product = $this->product->getById($product_id);
        if(!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            return;
        }
        
        // Check stock
        if($product['quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho. Còn lại: ' . $product['quantity']]);
            return;
        }
        
        try {
            if($this->cart->addItem($_SESSION['user_id'], $product_id, $quantity)) {
                $cart_count = $this->cart->getCartItemCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã thêm vào giỏ hàng',
                    'cart_count' => $cart_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function update() {
        header('Content-Type: application/json');
        
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if(!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Validate quantity
        if($quantity < 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
            return;
        }
        
        // If quantity is 0, remove item
        if($quantity == 0) {
            return $this->remove();
        }
        
        // Check stock
        $product = $this->product->getById($product_id);
        if($product && $product['quantity'] < $quantity) {
            echo json_encode([
                'success' => false, 
                'message' => 'Không đủ hàng trong kho. Còn lại: ' . $product['quantity']
            ]);
            return;
        }
        
        try {
            if($this->cart->updateQuantity($_SESSION['user_id'], $product_id, $quantity)) {
                $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
                $cart_count = $this->cart->getCartItemCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cập nhật thành công',
                    'cart_total' => number_format($cart_total),
                    'cart_count' => $cart_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function remove() {
        header('Content-Type: application/json');
        
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if(!isset($_POST['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $product_id = (int)$_POST['product_id'];
        
        try {
            if($this->cart->removeItem($_SESSION['user_id'], $product_id)) {
                $cart_count = $this->cart->getCartItemCount($_SESSION['user_id']);
                $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã xóa khỏi giỏ hàng',
                    'cart_count' => $cart_count,
                    'cart_total' => number_format($cart_total)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function clear() {
        header('Content-Type: application/json');
        
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        try {
            if($this->cart->clearCart($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã xóa toàn bộ giỏ hàng',
                    'cart_count' => 0,
                    'cart_total' => '0'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa giỏ hàng']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function getCount() {
        header('Content-Type: application/json');
        
        if(isLoggedIn()) {
            try {
                $count = $this->cart->getCartItemCount($_SESSION['user_id']);
                echo json_encode(['success' => true, 'count' => $count]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'count' => 0]);
            }
        } else {
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }
}
?>