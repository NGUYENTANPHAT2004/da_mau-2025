<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        try {
            // UPDATED QUERY TO MATCH DATABASE SCHEMA
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, order_number, status, total_amount, subtotal, tax_amount, shipping_amount, 
                       discount_amount, coupon_code, billing_name, billing_email, billing_phone, 
                       billing_address, billing_city, billing_state, billing_zipcode,
                       shipping_name, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zipcode,
                       payment_method, payment_status, notes, created_at) 
                      VALUES 
                      (:user_id, :order_number, :status, :total_amount, :subtotal, :tax_amount, :shipping_amount,
                       :discount_amount, :coupon_code, :billing_name, :billing_email, :billing_phone,
                       :billing_address, :billing_city, :billing_state, :billing_zipcode,
                       :shipping_name, :shipping_phone, :shipping_address, :shipping_city, :shipping_state, :shipping_zipcode,
                       :payment_method, :payment_status, :notes, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':order_number', $data['order_number']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':total_amount', $data['total_amount']);  // Changed from 'total'
            $stmt->bindParam(':subtotal', $data['subtotal']);
            $stmt->bindParam(':tax_amount', $data['tax_amount']);
            $stmt->bindParam(':shipping_amount', $data['shipping_amount']);  // Changed from 'shipping_fee'
            $stmt->bindParam(':discount_amount', $data['discount_amount']);
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
            $stmt->bindParam(':payment_status', $data['payment_status']);
            $stmt->bindParam(':notes', $data['notes']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            error_log("User Orders Get Error: " . $e->getMessage());
            return [];
        }
    }

    public function getAll($page = 1, $limit = 20, $status = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $where_clause = $status ? "WHERE o.status = :status" : "";
            
            $query = "SELECT o.*, 
                             COALESCE(u.fullname, o.billing_name) as customer_name
                      FROM " . $this->table_name . " o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      $where_clause
                      ORDER BY o.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Orders Get All Error: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalCount($status = '') {
        try {
            $where_clause = $status ? "WHERE status = :status" : "";
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " $where_clause";
            
            $stmt = $this->conn->prepare($query);
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'];
        } catch (Exception $e) {
            error_log("Orders Count Error: " . $e->getMessage());
            return 0;
        }
    }

    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status, updated_at = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order Status Update Error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . " SET ";
            $fields = [];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = :$key";
                }
            }
            
            $query .= implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order Update Error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            // First delete order items
            $query = "DELETE FROM order_items WHERE order_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Then delete the order
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order Delete Error: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderByNumber($order_number) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE order_number = :order_number";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_number', $order_number);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Order Get By Number Error: " . $e->getMessage());
            return false;
        }
    }

    public function getRevenueByPeriod($start_date, $end_date) {
        try {
            // Updated to use total_amount instead of total
            $query = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(total_amount) as total_revenue,
                        AVG(total_amount) as average_order_value
                      FROM " . $this->table_name . " 
                      WHERE created_at BETWEEN :start_date AND :end_date 
                      AND status NOT IN ('cancelled')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Revenue Report Error: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'average_order_value' => 0
            ];
        }
    }

    // New method to update payment status
    public function updatePaymentStatus($id, $payment_status, $transaction_id = null) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET payment_status = :payment_status";
            
            if ($transaction_id) {
                $query .= ", transaction_id = :transaction_id";
            }
            
            $query .= ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':payment_status', $payment_status);
            $stmt->bindParam(':id', $id);
            
            if ($transaction_id) {
                $stmt->bindParam(':transaction_id', $transaction_id);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Payment Status Update Error: " . $e->getMessage());
            return false;
        }
    }
}
?>