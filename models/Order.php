<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();
            
            // Tạo đơn hàng
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, total_amount, customer_name, customer_phone, customer_address, payment_method, notes, created_at) 
                      VALUES (:user_id, :total_amount, :customer_name, :customer_phone, :customer_address, :payment_method, :notes, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':customer_name', $data['customer_name']);
            $stmt->bindParam(':customer_phone', $data['customer_phone']);
            $stmt->bindParam(':customer_address', $data['customer_address']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':notes', $data['notes']);
            
            $stmt->execute();
            $orderId = $this->conn->lastInsertId();
            
            // Thêm chi tiết đơn hàng từ giỏ hàng
            $cart = new Cart($this->conn);
            $cartItems = $cart->getCartItems($data['user_id']);
            
            foreach($cartItems as $item) {
                $this->addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['sale_price'] ?? $item['price']);
            }
            
            $this->conn->commit();
            return $orderId;
            
        } catch(Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    private function addOrderItem($orderId, $productId, $quantity, $price) {
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                  VALUES (:order_id, :product_id, :quantity, :price)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        
        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT o.*, u.fullname as customer_name_full 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, p.name as product_name, p.image 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOrders($userId) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($orderId, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);
        
        return $stmt->execute();
    }

    public function getAll($limit = 20, $offset = 0) {
        $query = "SELECT o.*, u.fullname as customer_name, u.email as customer_email, u.phone as customer_phone 
                  FROM " . $this->table_name . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>