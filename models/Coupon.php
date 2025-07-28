<?php
class Coupon {
    private $conn;
    private $table_name = "coupons";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 20, $offset = 0, $filters = []) {
        $whereClause = "";
        $params = [];

        if(!empty($filters['status'])) {
            $whereClause .= " WHERE status = :status";
            $params[':status'] = $filters['status'];
        }

        if(!empty($filters['search'])) {
            $whereClause .= ($whereClause ? " AND" : " WHERE") . " (code LIKE :search OR name LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $query = "SELECT * FROM " . $this->table_name . " 
                  {$whereClause}
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCode($code) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (code, name, description, type, value, min_amount, max_discount, 
                   usage_limit, user_limit, status, start_date, end_date, created_at) 
                  VALUES (:code, :name, :description, :type, :value, :min_amount, :max_discount, 
                          :usage_limit, :user_limit, :status, :start_date, :end_date, NOW())";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':code', $data['code']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':value', $data['value']);
        $stmt->bindParam(':min_amount', $data['min_amount']);
        $stmt->bindParam(':max_discount', $data['max_discount']);
        $stmt->bindParam(':usage_limit', $data['usage_limit']);
        $stmt->bindParam(':user_limit', $data['user_limit']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);

        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET code = :code, name = :name, description = :description, 
                      type = :type, value = :value, min_amount = :min_amount, 
                      max_discount = :max_discount, usage_limit = :usage_limit, 
                      user_limit = :user_limit, status = :status, 
                      start_date = :start_date, end_date = :end_date, 
                      updated_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':code', $data['code']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':value', $data['value']);
        $stmt->bindParam(':min_amount', $data['min_amount']);
        $stmt->bindParam(':max_discount', $data['max_discount']);
        $stmt->bindParam(':usage_limit', $data['usage_limit']);
        $stmt->bindParam(':user_limit', $data['user_limit']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function isValidCoupon($code, $cart_total = 0, $user_id = null) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE code = :code 
                  AND status = 'active' 
                  AND (start_date IS NULL OR start_date <= NOW()) 
                  AND (end_date IS NULL OR end_date >= NOW())
                  AND (usage_limit IS NULL OR used_count < usage_limit)
                  AND min_amount <= :cart_total";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':cart_total', $cart_total);
        $stmt->execute();
        
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$coupon) return false;

        // Check user usage limit
        if($user_id && $coupon['user_limit']) {
            $userUsageQuery = "SELECT COUNT(*) as usage_count 
                              FROM orders 
                              WHERE user_id = :user_id AND coupon_code = :code";
            
            $userStmt = $this->conn->prepare($userUsageQuery);
            $userStmt->bindParam(':user_id', $user_id);
            $userStmt->bindParam(':code', $code);
            $userStmt->execute();
            
            $userUsage = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if($userUsage['usage_count'] >= $coupon['user_limit']) {
                return false;
            }
        }

        return $coupon;
    }

    public function calculateDiscount($coupon, $cart_total) {
        if($coupon['type'] === 'fixed') {
            return min($coupon['value'], $cart_total);
        } else { // percentage
            $discount = $cart_total * ($coupon['value'] / 100);
            return $coupon['max_discount'] ? min($discount, $coupon['max_discount']) : $discount;
        }
    }

    public function incrementUsage($code) {
        $query = "UPDATE " . $this->table_name . " 
                  SET used_count = used_count + 1 
                  WHERE code = :code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        return $stmt->execute();
    }

    public function getTotalCount($filters = []) {
        $whereClause = "";
        $params = [];

        if(!empty($filters['status'])) {
            $whereClause .= " WHERE status = :status";
            $params[':status'] = $filters['status'];
        }

        if(!empty($filters['search'])) {
            $whereClause .= ($whereClause ? " AND" : " WHERE") . " (code LIKE :search OR name LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . $whereClause;
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getActiveCoupons($limit = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = 'active' 
                  AND (start_date IS NULL OR start_date <= NOW()) 
                  AND (end_date IS NULL OR end_date >= NOW())
                  AND (usage_limit IS NULL OR used_count < usage_limit)
                  ORDER BY created_at DESC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>