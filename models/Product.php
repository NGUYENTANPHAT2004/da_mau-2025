<?php
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 12, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active' 
                  ORDER BY p.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id AND p.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategory($category_id, $limit = 12) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE category_id = :category_id AND status = 'active' 
                  ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($keyword, $limit = 12) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE (p.name LIKE :keyword OR p.description LIKE :keyword) 
                  AND p.status = 'active' 
                  ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$keyword}%";
        $stmt->bindParam(':keyword', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, price, sale_price, image, category_id, quantity, status, created_at) 
                  VALUES (:name, :description, :price, :sale_price, :image, :category_id, :quantity, 'active', NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':sale_price', $data['sale_price']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':quantity', $data['quantity']);
        
        return $stmt->execute();
    }
}
?>