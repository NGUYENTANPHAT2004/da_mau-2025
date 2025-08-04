<?php
class Analytics {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDashboardStats() {
        $stats = [];
        
        try {
            // Tổng doanh thu
            $query = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE status != 'cancelled'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_revenue'] = $result['total_revenue'] ?? 0;
            
            // Tổng đơn hàng
            $query = "SELECT COUNT(*) as total_orders FROM orders";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_orders'] = $result['total_orders'] ?? 0;
            
            // Tổng khách hàng
            $query = "SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_customers'] = $result['total_customers'] ?? 0;
            
            // Tổng sản phẩm
            $query = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_products'] = $result['total_products'] ?? 0;
            
            // Đơn hàng hôm nay
            $query = "SELECT COUNT(*) as today_orders FROM orders WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['today_orders'] = $result['today_orders'] ?? 0;
            
            // Doanh thu hôm nay
            $query = "SELECT COALESCE(SUM(total_amount), 0) as today_revenue FROM orders 
                      WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['today_revenue'] = $result['today_revenue'] ?? 0;

            // Doanh thu tháng này
            $query = "SELECT COALESCE(SUM(total_amount), 0) as month_revenue FROM orders 
                      WHERE MONTH(created_at) = MONTH(CURDATE()) 
                      AND YEAR(created_at) = YEAR(CURDATE())
                      AND status != 'cancelled'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['month_revenue'] = $result['month_revenue'] ?? 0;

            // Đơn hàng tháng này
            $query = "SELECT COUNT(*) as month_orders FROM orders 
                      WHERE MONTH(created_at) = MONTH(CURDATE()) 
                      AND YEAR(created_at) = YEAR(CURDATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['month_orders'] = $result['month_orders'] ?? 0;
            
        } catch(Exception $e) {
            error_log("Analytics Error: " . $e->getMessage());
            // Return default values on error
            $stats = [
                'total_revenue' => 0,
                'total_orders' => 0,
                'total_customers' => 0,
                'total_products' => 0,
                'today_orders' => 0,
                'today_revenue' => 0,
                'month_revenue' => 0,
                'month_orders' => 0
            ];
        }
        
        return $stats;
    }

    public function getMonthlyRevenue() {
        try {
            $query = "SELECT 
                        MONTH(created_at) as month,
                        YEAR(created_at) as year,
                        MONTHNAME(created_at) as month_name,
                        COALESCE(SUM(total_amount), 0) as revenue 
                      FROM orders 
                      WHERE status != 'cancelled' 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY YEAR(created_at), MONTH(created_at) 
                      ORDER BY year, month";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ensure we always return some data
            if(empty($data)) {
                return [
                    ['month' => date('n'), 'year' => date('Y'), 'month_name' => date('F'), 'revenue' => 0]
                ];
            }
            
            return $data;
        } catch(Exception $e) {
            error_log("Monthly Revenue Error: " . $e->getMessage());
            return [
                ['month' => date('n'), 'year' => date('Y'), 'month_name' => date('F'), 'revenue' => 0]
            ];
        }
    }

    public function getTopProducts($limit = 10) {
        try {
            $query = "SELECT 
                        p.id,
                        p.name, 
                        p.price, 
                        p.image,
                        COALESCE(SUM(oi.quantity), 0) as total_sold, 
                        COALESCE(SUM(oi.quantity * oi.unit_price), 0) as revenue
                      FROM products p 
                      LEFT JOIN order_items oi ON p.id = oi.product_id 
                      LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                      WHERE p.status = 'active'
                      GROUP BY p.id, p.name, p.price, p.image
                      ORDER BY total_sold DESC, revenue DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return empty array if no data
            return $data ?: [];
        } catch(Exception $e) {
            error_log("Top Products Error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrders($limit = 10) {
        try {
            $query = "SELECT 
                        o.id,
                        o.order_number,
                        o.total_amount,
                        o.status,
                        o.created_at,
                        COALESCE(u.fullname, o.billing_name) as customer_name,
                        COALESCE(u.email, o.billing_email) as customer_email
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $data ?: [];
        } catch(Exception $e) {
            error_log("Recent Orders Error: " . $e->getMessage());
            return [];
        }
    }

    public function getProductStats() {
        try {
            $stats = [];
            
            // Sản phẩm sắp hết hàng (< 10)
            $query = "SELECT COUNT(*) as low_stock FROM products WHERE quantity < 10 AND quantity > 0 AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['low_stock'] = $result['low_stock'] ?? 0;
            
            // Sản phẩm hết hàng
            $query = "SELECT COUNT(*) as out_of_stock FROM products WHERE quantity = 0 AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['out_of_stock'] = $result['out_of_stock'] ?? 0;
            
            return $stats;
        } catch(Exception $e) {
            error_log("Product Stats Error: " . $e->getMessage());
            return ['low_stock' => 0, 'out_of_stock' => 0];
        }
    }

    public function getOrderStatusStats() {
        try {
            $query = "SELECT 
                        status,
                        COUNT(*) as count
                      FROM orders 
                      GROUP BY status";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to associative array
            $stats = [];
            foreach($data as $row) {
                $stats[$row['status']] = $row['count'];
            }
            
            // Ensure all statuses exist
            $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            foreach($statuses as $status) {
                if(!isset($stats[$status])) {
                    $stats[$status] = 0;
                }
            }
            
            return $stats;
        } catch(Exception $e) {
            error_log("Order Status Stats Error: " . $e->getMessage());
            return [
                'pending' => 0,
                'processing' => 0,
                'shipped' => 0,
                'delivered' => 0,
                'cancelled' => 0
            ];
        }
    }
}
?>