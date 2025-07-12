<?php
class Cart {
    private $conn;
    private $table_name = "cart_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addItem($user_id, $product_id, $quantity = 1) {
        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            // Cập nhật số lượng
            $query = "UPDATE " . $this->table_name . " 
                      SET quantity = quantity + :quantity 
                      WHERE user_id = :user_id AND product_id = :product_id";
        } else {
            // Thêm mới
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, product_id, quantity, created_at) 
                      VALUES (:user_id, :product_id, :quantity, NOW())";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        
        return $stmt->execute();
    }

    public function getCartItems($user_id) {
        $query = "SELECT c.*, p.name, p.price, p.sale_price, p.image 
                  FROM " . $this->table_name . " c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id 
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateQuantity($user_id, $product_id, $quantity) {
        if($quantity <= 0) {
            return $this->removeItem($user_id, $product_id);
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET quantity = :quantity 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        
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

    public function clearCart($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function getCartTotal($user_id) {
        $query = "SELECT SUM(c.quantity * COALESCE(p.sale_price, p.price)) as total 
                  FROM " . $this->table_name . " c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getCartItemCount($user_id) {
        $query = "SELECT SUM(quantity) as total_count 
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_count'] ?? 0;
    }
}

?>