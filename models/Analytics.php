<?php
class Analytics {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDashboardStats() {
        $stats = [];
        
        // Tổng doanh thu
        $query = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
        
        // Tổng đơn hàng
        $query = "SELECT COUNT(*) as total_orders FROM orders";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;
        
        // Tổng khách hàng
        $query = "SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_customers'] ?? 0;
        
        // Tổng sản phẩm
        $query = "SELECT COUNT(*) as total_products FROM products WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'] ?? 0;
        
        // Đơn hàng hôm nay
        $query = "SELECT COUNT(*) as today_orders FROM orders WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['today_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['today_orders'] ?? 0;
        
        // Doanh thu hôm nay
        $query = "SELECT SUM(total_amount) as today_revenue FROM orders 
                  WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['today_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['today_revenue'] ?? 0;
        
        return $stats;
    }

    public function getMonthlyRevenue() {
        $query = "SELECT 
                    MONTH(created_at) as month,
                    YEAR(created_at) as year,
                    SUM(total_amount) as revenue 
                  FROM orders 
                  WHERE status != 'cancelled' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY YEAR(created_at), MONTH(created_at) 
                  ORDER BY year, month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProducts($limit = 10) {
        $query = "SELECT p.name, p.price, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  JOIN orders o ON oi.order_id = o.id 
                  WHERE o.status != 'cancelled' 
                  GROUP BY p.id 
                  ORDER BY total_sold DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentOrders($limit = 10) {
        $query = "SELECT o.*, u.fullname as customer_name 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>