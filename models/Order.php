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
            
            // Generate order number if not provided
            if(!isset($data['order_number']) || empty($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber();
            }
            
            // Tạo đơn hàng
            $query = "INSERT INTO " . $this->table_name . " 
                      (order_number, user_id, subtotal, shipping_amount, tax_amount, discount_amount, total_amount, 
                       coupon_code, billing_name, billing_email, billing_phone, billing_address, billing_city, 
                       billing_state, billing_zipcode, shipping_name, shipping_phone, shipping_address, 
                       shipping_city, shipping_state, shipping_zipcode, payment_method, notes, created_at) 
                      VALUES 
                      (:order_number, :user_id, :subtotal, :shipping_amount, :tax_amount, :discount_amount, :total_amount,
                       :coupon_code, :billing_name, :billing_email, :billing_phone, :billing_address, :billing_city,
                       :billing_state, :billing_zipcode, :shipping_name, :shipping_phone, :shipping_address,
                       :shipping_city, :shipping_state, :shipping_zipcode, :payment_method, :notes, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind all parameters with default values
            $stmt->bindParam(':order_number', $data['order_number']);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':shipping_amount', $data['shipping_amount']);
            $stmt->bindParam(':tax_amount', $data['tax_amount']);
            $stmt->bindParam(':discount_amount', $data['discount_amount']);
            $stmt->bindParam(':total_amount', $data['total_amount']);
            $stmt->bindParam(':coupon_code', $data['coupon_code']);
            $stmt->bindParam(':billing_name', $data['billing_name']);
            $stmt->bindParam(':billing_email', $data['billing_email']);
            $stmt->bindParam(':billing_phone', $data['billing_phone']);
            $stmt->bindParam(':billing_address', $data['billing_address']);
            $stmt->bindParam(':billing_city', $data['billing_city']);
            $stmt->bindParam(':billing_state', $data['billing_state']);
            $stmt->bindParam(':billing_zipcode', $data['billing_zipcode']);
            $stmt->bindParam(':shipping_name', $data['shipping_name']);
            $stmt->bindParam(':shipping_phone', $data['shipping_phone']);
            $stmt->bindParam(':shipping_address', $data['shipping_address']);
            $stmt->bindParam(':shipping_city', $data['shipping_city']);
            $stmt->bindParam(':shipping_state', $data['shipping_state']);
            $stmt->bindParam(':shipping_zipcode', $data['shipping_zipcode']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':notes', $data['notes']);
            
            $stmt->execute();
            $orderId = $this->conn->lastInsertId();
            
            $this->conn->commit();
            return $orderId;
            
        } catch(Exception $e) {
            $this->conn->rollback();
            error_log("Order Create Error: " . $e->getMessage());
            return false;
        }
    }

    public function addOrderItem($orderId, $data) {
        try {
            $query = "INSERT INTO order_items 
                      (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price, created_at) 
                      VALUES 
                      (:order_id, :product_id, :product_name, :product_sku, :quantity, :unit_price, :total_price, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':product_id', $data['product_id']);
            $stmt->bindParam(':product_name', $data['product_name']);
            $stmt->bindParam(':product_sku', $data['product_sku']);
            $stmt->bindParam(':quantity', $data['quantity']);
            $stmt->bindParam(':unit_price', $data['unit_price']);
            $stmt->bindParam(':total_price', $data['total_price']);
            
            return $stmt->execute();
        } catch(Exception $e) {
            error_log("Order Item Add Error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT o.*, 
                             COALESCE(u.fullname, o.billing_name) as customer_name_full,
                             u.email as user_email
                      FROM " . $this->table_name . " o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("Order Get By ID Error: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderItems($orderId) {
        try {
            $query = "SELECT oi.*, 
                             COALESCE(p.name, oi.product_name) as name,
                             COALESCE(p.image, '') as image
                      FROM order_items oi 
                      LEFT JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = :order_id
                      ORDER BY oi.created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("Order Items Get Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserOrders($userId, $limit = 50) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("User Orders Get Error: " . $e->getMessage());
            return [];
        }
    }

    public function updateStatus($orderId, $status) {
        try {
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            
            if(!in_array($status, $validStatuses)) {
                return false;
            }
            
            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status, updated_at = NOW() 
                      WHERE id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $orderId);
            
            return $stmt->execute();
        } catch(Exception $e) {
            error_log("Order Status Update Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($limit = 20, $offset = 0, $filters = []) {
        try {
            $whereClause = "";
            $params = [];
            
            // Apply filters
            if(!empty($filters['status'])) {
                $whereClause .= " WHERE o.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if(!empty($filters['search'])) {
                $searchCondition = " (o.order_number LIKE :search OR o.billing_name LIKE :search OR o.billing_email LIKE :search)";
                if($whereClause) {
                    $whereClause .= " AND " . $searchCondition;
                } else {
                    $whereClause .= " WHERE " . $searchCondition;
                }
                $params[':search'] = "%{$filters['search']}%";
            }
            
            if(!empty($filters['from_date'])) {
                $dateCondition = " DATE(o.created_at) >= :from_date";
                if($whereClause) {
                    $whereClause .= " AND " . $dateCondition;
                } else {
                    $whereClause .= " WHERE " . $dateCondition;
                }
                $params[':from_date'] = $filters['from_date'];
            }
            
            $query = "SELECT o.*, 
                             COALESCE(u.fullname, o.billing_name) as customer_name, 
                             COALESCE(u.email, o.billing_email) as customer_email,
                             COALESCE(u.phone, o.billing_phone) as customer_phone
                      FROM " . $this->table_name . " o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      {$whereClause}
                      ORDER BY o.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind filter parameters
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("Orders Get All Error: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalCount($filters = []) {
        try {
            $whereClause = "";
            $params = [];
            
            // Apply same filters as getAll
            if(!empty($filters['status'])) {
                $whereClause .= " WHERE status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if(!empty($filters['search'])) {
                $searchCondition = " (order_number LIKE :search OR billing_name LIKE :search OR billing_email LIKE :search)";
                if($whereClause) {
                    $whereClause .= " AND " . $searchCondition;
                } else {
                    $whereClause .= " WHERE " . $searchCondition;
                }
                $params[':search'] = "%{$filters['search']}%";
            }
            
            if(!empty($filters['from_date'])) {
                $dateCondition = " DATE(created_at) >= :from_date";
                if($whereClause) {
                    $whereClause .= " AND " . $dateCondition;
                } else {
                    $whereClause .= " WHERE " . $dateCondition;
                }
                $params[':from_date'] = $filters['from_date'];
            }
            
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . $whereClause;
            
            $stmt = $this->conn->prepare($query);
            
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch(Exception $e) {
            error_log("Orders Total Count Error: " . $e->getMessage());
            return 0;
        }
    }

    private function generateOrderNumber() {
        $prefix = 'ORD';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check if order number exists
        do {
            $orderNumber = $prefix . $date . $random;
            $query = "SELECT id FROM " . $this->table_name . " WHERE order_number = :order_number";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_number', $orderNumber);
            $stmt->execute();
            $exists = $stmt->rowCount() > 0;
            
            if($exists) {
                $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        } while($exists);
        
        return $orderNumber;
    }

    public function delete($id) {
        try {
            // Don't actually delete orders, just mark as cancelled
            return $this->updateStatus($id, 'cancelled');
        } catch(Exception $e) {
            error_log("Order Delete Error: " . $e->getMessage());
            return false;
        }
    }

    public function getOrdersByDateRange($startDate, $endDate) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                      ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("Orders By Date Range Error: " . $e->getMessage());
            return [];
        }
    }
}
?>