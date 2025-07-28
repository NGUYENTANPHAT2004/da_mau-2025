<?php
// models/Wishlist.php
class Wishlist {
    private $conn;
    private $table_name = "wishlist";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addItem($user_id, $product_id) {
        // Kiểm tra sản phẩm đã có trong wishlist chưa
        if($this->isInWishlist($user_id, $product_id)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " (user_id, product_id, created_at) 
                  VALUES (:user_id, :product_id, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        
        return $stmt->execute();
    }

    public function removeItem($user_id, $product_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        
        return $stmt->execute();
    }

    public function getWishlistItems($user_id) {
        $query = "SELECT w.*, p.name, p.price, p.sale_price, p.image, p.slug, 
                         c.name as category_name, p.rating, p.review_count
                  FROM " . $this->table_name . " w 
                  JOIN products p ON w.product_id = p.id 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE w.user_id = :user_id AND p.status = 'active'
                  ORDER BY w.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isInWishlist($user_id, $product_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function getWishlistCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function clearWishlist($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function moveToCart($user_id, $product_id) {
        // Thêm vào giỏ hàng
        $cart = new Cart($this->conn);
        $result = $cart->addItem($user_id, $product_id, 1);
        
        if($result) {
            // Xóa khỏi wishlist
            return $this->removeItem($user_id, $product_id);
        }
        
        return false;
    }
}

// controllers/WishlistController.php
class WishlistController {
    private $db;
    private $wishlist;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->wishlist = new Wishlist($this->db);
        $this->product = new Product($this->db);
    }

    public function index() {
        if(!isLoggedIn()) {
            redirect('login');
            return;
        }

        $wishlist_items = $this->wishlist->getWishlistItems($_SESSION['user_id']);
        
        include 'views/layouts/header.php';
        include 'views/wishlist/index.php';
        include 'views/layouts/footer.php';
    }

    public function add() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST && isset($_POST['product_id'])) {
            $product_id = $_POST['product_id'];
            
            // Kiểm tra sản phẩm có tồn tại không
            $product = $this->product->getById($product_id);
            if(!$product) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
                return;
            }

            if($this->wishlist->addItem($_SESSION['user_id'], $product_id)) {
                $wishlist_count = $this->wishlist->getWishlistCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã thêm vào danh sách yêu thích',
                    'wishlist_count' => $wishlist_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm đã có trong danh sách yêu thích']);
            }
        }
    }

    public function remove() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST && isset($_POST['product_id'])) {
            $product_id = $_POST['product_id'];
            
            if($this->wishlist->removeItem($_SESSION['user_id'], $product_id)) {
                $wishlist_count = $this->wishlist->getWishlistCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã xóa khỏi danh sách yêu thích',
                    'wishlist_count' => $wishlist_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
            }
        }
    }

    public function moveToCart() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST && isset($_POST['product_id'])) {
            $product_id = $_POST['product_id'];
            
            if($this->wishlist->moveToCart($_SESSION['user_id'], $product_id)) {
                $wishlist_count = $this->wishlist->getWishlistCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã chuyển vào giỏ hàng',
                    'wishlist_count' => $wishlist_count
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

        if($this->wishlist->clearWishlist($_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa toàn bộ danh sách yêu thích']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    public function getCount() {
        if(isLoggedIn()) {
            $count = $this->wishlist->getWishlistCount($_SESSION['user_id']);
            echo json_encode(['count' => $count]);
        } else {
            echo json_encode(['count' => 0]);
        }
    }

    public function toggle() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST && isset($_POST['product_id'])) {
            $product_id = $_POST['product_id'];
            
            if($this->wishlist->isInWishlist($_SESSION['user_id'], $product_id)) {
                // Xóa khỏi wishlist
                $result = $this->wishlist->removeItem($_SESSION['user_id'], $product_id);
                $action = 'removed';
                $message = 'Đã xóa khỏi danh sách yêu thích';
            } else {
                // Thêm vào wishlist
                $result = $this->wishlist->addItem($_SESSION['user_id'], $product_id);
                $action = 'added';
                $message = 'Đã thêm vào danh sách yêu thích';
            }

            if($result) {
                $wishlist_count = $this->wishlist->getWishlistCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true, 
                    'action' => $action,
                    'message' => $message,
                    'wishlist_count' => $wishlist_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
            }
        }
    }
}

?>