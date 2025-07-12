<?php
class Article {
    private $conn;
    private $table_name = "articles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT a.*, u.fullname as author_name 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN users u ON a.author_id = u.id 
                  WHERE a.status = 'published' 
                  ORDER BY a.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT a.*, u.fullname as author_name 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN users u ON a.author_id = u.id 
                  WHERE a.id = :id AND a.status = 'published'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Tăng lượt xem
        $this->increaseViews($id);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function increaseViews($id) {
        $query = "UPDATE " . $this->table_name . " SET views = views + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getFeatured($limit = 3) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = 'published' AND featured = 1 
                  ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($keyword, $limit = 10) {
        $query = "SELECT a.*, u.fullname as author_name 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN users u ON a.author_id = u.id 
                  WHERE (a.title LIKE :keyword OR a.content LIKE :keyword) 
                  AND a.status = 'published' 
                  ORDER BY a.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$keyword}%";
        $stmt->bindParam(':keyword', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, content, excerpt, image, author_id, category, featured, status, created_at) 
                  VALUES (:title, :content, :excerpt, :image, :author_id, :category, :featured, 'published', NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':excerpt', $data['excerpt']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':author_id', $data['author_id']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':featured', $data['featured']);
        
        return $stmt->execute();
    }
}
?>


