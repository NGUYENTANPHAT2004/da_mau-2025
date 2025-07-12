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
            redirect('login');
            return;
        }
        
        $cart_items = $this->cart->getCartItems($_SESSION['user_id']);
        $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
        
        include 'views/layouts/header.php';
        include 'views/cart/index.php';
        include 'views/layouts/footer.php';
    }

    public function add() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'] ?? 1;
            
            // Kiểm tra sản phẩm có tồn tại không
            $product = $this->product->getById($product_id);
            if(!$product) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
                return;
            }
            
            // Kiểm tra số lượng trong kho
            if($product['quantity'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']);
                return;
            }
            
            if($this->cart->addItem($_SESSION['user_id'], $product_id, $quantity)) {
                $cart_count = $this->cart->getCartItemCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã thêm vào giỏ hàng',
                    'cart_count' => $cart_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
            }
        }
    }

    public function update() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            
            if($this->cart->updateQuantity($_SESSION['user_id'], $product_id, $quantity)) {
                $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cập nhật thành công',
                    'cart_total' => number_format($cart_total)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
            }
        }
    }

    public function remove() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            $product_id = $_POST['product_id'];
            
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
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
            }
        }
    }

    public function clear() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($this->cart->clearCart($_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa toàn bộ giỏ hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    public function getCount() {
        if(isLoggedIn()) {
            $count = $this->cart->getCartItemCount($_SESSION['user_id']);
            echo json_encode(['count' => $count]);
        } else {
            echo json_encode(['count' => 0]);
        }
    }
}
?>