<?php
// controllers/ApiController.php
class ApiController {
    private $db;
    private $product;
    private $cart;
    private $wishlist;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->cart = new Cart($this->db);
        $this->wishlist = new Wishlist($this->db);
        $this->user = new User($this->db);
    }

    // Products API
    public function getProducts() {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 12);
        $offset = ($page - 1) * $limit;
        
        try {
            $products = $this->product->getAll($limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->product->getTotalCount()
                ]
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }

    public function searchProducts() {
        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 10);
        
        if(strlen($query) < 2) {
            echo json_encode(['success' => false, 'message' => 'Query too short']);
            return;
        }
        
        try {
            $products = $this->product->search($query, $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $products,
                'query' => $query
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Search failed']);
        }
    }

    public function filterProducts() {
        $filters = [
            'category_id' => $_GET['category'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest'
        ];
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 12);
        $offset = ($page - 1) * $limit;
        
        try {
            $products = $this->product->getFiltered($filters, $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'data' => $products,
                'filters' => $filters
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Filter failed']);
        }
    }

    public function getRelatedProducts($product_id) {
        if(!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        try {
            $product = $this->product->getById($product_id);
            if(!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                return;
            }
            
            $related = $this->product->getByCategory($product['category_id'], 6);
            
            // Remove current product from related
            $related = array_filter($related, function($item) use ($product_id) {
                return $item['id'] != $product_id;
            });
            
            echo json_encode([
                'success' => true,
                'data' => array_values($related)
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to get related products']);
        }
    }

    // Cart API
    public function getCart() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $cart_items = $this->cart->getCartItems($_SESSION['user_id']);
            $cart_total = $this->cart->getCartTotal($_SESSION['user_id']);
            $cart_count = $this->cart->getCartItemCount($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'items' => $cart_items,
                    'total' => $cart_total,
                    'count' => $cart_count
                ]
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to get cart']);
        }
    }

    public function addToCart() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $product_id = $input['product_id'] ?? null;
        $quantity = $input['quantity'] ?? 1;
        
        if(!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        try {
            $result = $this->cart->addItem($_SESSION['user_id'], $product_id, $quantity);
            
            if($result) {
                $cart_count = $this->cart->getCartItemCount($_SESSION['user_id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Added to cart successfully',
                    'cart_count' => $cart_count
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }

    // Wishlist API
    public function getWishlist() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $wishlist_items = $this->wishlist->getWishlistItems($_SESSION['user_id']);
            $wishlist_count = $this->wishlist->getWishlistCount($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'items' => $wishlist_items,
                    'count' => $wishlist_count
                ]
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to get wishlist']);
        }
    }

    // User API
    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if(!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }
        
        try {
            if($this->user->login($email, $password)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $_SESSION['user_id'],
                        'name' => $_SESSION['user_name'],
                        'email' => $_SESSION['user_email'],
                        'role' => $_SESSION['user_role']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Login failed']);
        }
    }

    public function getUserProfile() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            $user_info = $this->user->getUserById($_SESSION['user_id']);
            unset($user_info['password']); // Remove password from response
            
            echo json_encode([
                'success' => true,
                'data' => $user_info
            ]);
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to get profile']);
        }
    }
}

// Enhanced Analytics Model
class AdvancedAnalytics {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getDashboardKPIs() {
        $data = [];
        
        // Revenue metrics
        $data['revenue'] = [
            'total' => $this->getTotalRevenue(),
            'monthly' => $this->getMonthlyRevenue(),
            'weekly' => $this->getWeeklyRevenue(),
            'daily' => $this->getDailyRevenue(),
            'growth' => $this->getRevenueGrowth()
        ];
        
        // Order metrics
        $data['orders'] = [
            'total' => $this->getTotalOrders(),
            'pending' => $this->getPendingOrders(),
            'completed' => $this->getCompletedOrders(),
            'cancelled' => $this->getCancelledOrders(),
            'conversion_rate' => $this->getConversionRate()
        ];
        
        // Customer metrics
        $data['customers'] = [
            'total' => $this->getTotalCustomers(),
            'new_monthly' => $this->getNewCustomersThisMonth(),
            'returning' => $this->getReturningCustomers(),
            'lifetime_value' => $this->getAverageLifetimeValue()
        ];
        
        // Product metrics
        $data['products'] = [
            'total' => $this->getTotalProducts(),
            'low_stock' => $this->getLowStockProducts(),
            'out_of_stock' => $this->getOutOfStockProducts(),
            'top_selling' => $this->getTopSellingProducts(5)
        ];
        
        return $data;
    }

    public function getSalesChart($period = '30d') {
        $dateCondition = $this->getDateCondition($period);
        
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as orders,
                    SUM(total_amount) as revenue
                  FROM orders 
                  WHERE {$dateCondition}
                  AND status NOT IN ('cancelled', 'refunded')
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProductsChart($limit = 10) {
        $query = "SELECT 
                    p.name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.total_price) as revenue
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE o.status NOT IN ('cancelled', 'refunded')
                  GROUP BY p.id, p.name
                  ORDER BY total_sold DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryPerformance() {
        $query = "SELECT 
                    c.name as category,
                    COUNT(DISTINCT p.id) as products_count,
                    COALESCE(SUM(oi.quantity), 0) as total_sold,
                    COALESCE(SUM(oi.total_price), 0) as revenue
                  FROM categories c
                  LEFT JOIN products p ON c.id = p.category_id
                  LEFT JOIN order_items oi ON p.id = oi.product_id
                  LEFT JOIN orders o ON oi.order_id = o.id AND o.status NOT IN ('cancelled', 'refunded')
                  WHERE c.status = 'active'
                  GROUP BY c.id, c.name
                  ORDER BY revenue DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerSegmentation() {
        $query = "SELECT 
                    CASE 
                        WHEN total_spent >= 10000000 THEN 'VIP'
                        WHEN total_spent >= 5000000 THEN 'Premium'
                        WHEN total_spent >= 1000000 THEN 'Regular'
                        ELSE 'New'
                    END as segment,
                    COUNT(*) as customer_count,
                    AVG(total_spent) as avg_spent
                  FROM (
                      SELECT 
                          u.id,
                          COALESCE(SUM(o.total_amount), 0) as total_spent
                      FROM users u
                      LEFT JOIN orders o ON u.id = o.user_id AND o.status NOT IN ('cancelled', 'refunded')
                      WHERE u.role = 'customer'
                      GROUP BY u.id
                  ) customer_totals
                  GROUP BY segment
                  ORDER BY avg_spent DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrafficSources() {
        // This would typically come from Google Analytics or similar
        // For demo purposes, returning mock data
        return [
            ['source' => 'Organic Search', 'visitors' => 45.2, 'conversions' => 12.3],
            ['source' => 'Direct', 'visitors' => 23.1, 'conversions' => 18.7],
            ['source' => 'Social Media', 'visitors' => 18.5, 'conversions' => 8.2],
            ['source' => 'Email', 'visitors' => 8.7, 'conversions' => 25.1],
            ['source' => 'Paid Ads', 'visitors' => 4.5, 'conversions' => 15.6]
        ];
    }

    private function getTotalRevenue() {
        $query = "SELECT SUM(total_amount) as total FROM orders WHERE status NOT IN ('cancelled', 'refunded')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    private function getMonthlyRevenue() {
        $query = "SELECT SUM(total_amount) as total FROM orders 
                  WHERE MONTH(created_at) = MONTH(NOW()) 
                  AND YEAR(created_at) = YEAR(NOW())
                  AND status NOT IN ('cancelled', 'refunded')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    private function getRevenueGrowth() {
        $currentMonth = $this->getMonthlyRevenue();
        
        $query = "SELECT SUM(total_amount) as total FROM orders 
                  WHERE MONTH(created_at) = MONTH(NOW() - INTERVAL 1 MONTH) 
                  AND YEAR(created_at) = YEAR(NOW() - INTERVAL 1 MONTH)
                  AND status NOT IN ('cancelled', 'refunded')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $lastMonth = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 1;
        
        return $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
    }

    private function getTotalOrders() {
        $query = "SELECT COUNT(*) as total FROM orders";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    private function getConversionRate() {
        // Simplified calculation - would need proper tracking in real scenario
        $orders = $this->getTotalOrders();
        $sessions = $orders * 15; // Assume 15 sessions per order on average
        return $sessions > 0 ? ($orders / $sessions) * 100 : 0;
    }

    private function getDateCondition($period) {
        switch($period) {
            case '7d':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30d':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case '90d':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case '1y':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }
}

// Enhanced Admin Controller methods for analytics
class EnhancedAdminController extends AdminController {
    private $analytics;

    public function __construct() {
        parent::__construct();
        $this->analytics = new AdvancedAnalytics($this->db);
    }

    public function analyticsApi() {
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? 'kpis';
        $period = $_GET['period'] ?? '30d';
        
        try {
            switch($type) {
                case 'kpis':
                    $data = $this->analytics->getDashboardKPIs();
                    break;
                case 'sales-chart':
                    $data = $this->analytics->getSalesChart($period);
                    break;
                case 'top-products':
                    $data = $this->analytics->getTopProductsChart(10);
                    break;
                case 'categories':
                    $data = $this->analytics->getCategoryPerformance();
                    break;
                case 'customers':
                    $data = $this->analytics->getCustomerSegmentation();
                    break;
                case 'traffic':
                    $data = $this->analytics->getTrafficSources();
                    break;
                default:
                    throw new Exception('Invalid analytics type');
            }
            
            echo json_encode(['success' => true, 'data' => $data]);
        } catch(Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportData() {
        $type = $_GET['type'] ?? 'orders';
        $format = $_GET['format'] ?? 'csv';
        
        switch($type) {
            case 'orders':
                $this->exportOrders($format);
                break;
            case 'customers':
                $this->exportCustomers($format);
                break;
            case 'products':
                $this->exportProducts($format);
                break;
            default:
                redirect('admin/analytics');
        }
    }

    private function exportOrders($format) {
        $query = "SELECT o.*, u.fullname, u.email 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($format === 'csv') {
            $this->outputCSV($orders, 'orders_export_' . date('Y-m-d'));
        } else {
            $this->outputExcel($orders, 'orders_export_' . date('Y-m-d'));
        }
    }

    private function outputCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if(!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }
}
?>