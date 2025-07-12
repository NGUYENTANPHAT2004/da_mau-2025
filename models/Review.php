<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        // Kiểm tra user đã mua sản phẩm này chưa
        if(!$this->hasUserPurchasedProduct($data['user_id'], $data['product_id'])) {
            return false;
        }

        // Kiểm tra user đã đánh giá sản phẩm này chưa
        if($this->hasUserReviewedProduct($data['user_id'], $data['product_id'])) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, product_id, rating, comment, status, created_at) 
                  VALUES (:user_id, :product_id, :rating, :comment, 'approved', NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':product_id', $data['product_id']);
        $stmt->bindParam(':rating', $data['rating']);
        $stmt->bindParam(':comment', $data['comment']);
        
        if($stmt->execute()) {
            $this->updateProductRating($data['product_id']);
            return true;
        }
        return false;
    }

    public function getProductReviews($product_id, $limit = 10, $offset = 0) {
        $query = "SELECT r.*, u.fullname as user_name 
                  FROM " . $this->table_name . " r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.product_id = :product_id AND r.status = 'approved' 
                  ORDER BY r.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductRatingStats($product_id) {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                  FROM " . $this->table_name . " 
                  WHERE product_id = :product_id AND status = 'approved'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function hasUserPurchasedProduct($user_id, $product_id) {
        $query = "SELECT COUNT(*) as count FROM order_items oi 
                  JOIN orders o ON oi.order_id = o.id 
                  WHERE o.user_id = :user_id AND oi.product_id = :product_id 
                  AND o.status IN ('delivered', 'completed')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    private function hasUserReviewedProduct($user_id, $product_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    private function updateProductRating($product_id) {
        $stats = $this->getProductRatingStats($product_id);
        
        $query = "UPDATE products 
                  SET rating = :rating, review_count = :review_count 
                  WHERE id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rating', $stats['average_rating']);
        $stmt->bindParam(':review_count', $stats['total_reviews']);
        $stmt->bindParam(':product_id', $product_id);
        
        return $stmt->execute();
    }

    public function canUserReviewProduct($user_id, $product_id) {
        return $this->hasUserPurchasedProduct($user_id, $product_id) && 
               !$this->hasUserReviewedProduct($user_id, $product_id);
    }

    public function getUserReview($user_id, $product_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateReview($review_id, $rating, $comment) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rating = :rating, comment = :comment, updated_at = NOW() 
                  WHERE id = :review_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':review_id', $review_id);
        
        if($stmt->execute()) {
            // Cập nhật lại rating sản phẩm
            $review = $this->getReviewById($review_id);
            $this->updateProductRating($review['product_id']);
            return true;
        }
        return false;
    }

    private function getReviewById($review_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :review_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':review_id', $review_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteReview($review_id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = :review_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':review_id', $review_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getLatestReviews($limit = 5) {
        $query = "SELECT r.*, u.fullname as user_name, p.name as product_name 
                  FROM " . $this->table_name . " r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN products p ON r.product_id = p.id 
                  WHERE r.status = 'approved' 
                  ORDER BY r.created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>