<?php
class AdminController {
    private $db;
    private $analytics;
    private $product;
    private $article;
    private $order;

    public function __construct() {
        if(!isAdmin()) {
            redirect('login');
            return;
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->analytics = new Analytics($this->db);
        $this->product = new Product($this->db);
        $this->article = new Article($this->db);
        $this->order = new Order($this->db);
    }

    public function dashboard() {
        $stats = $this->analytics->getDashboardStats();
        $monthly_revenue = $this->analytics->getMonthlyRevenue();
        $top_products = $this->analytics->getTopProducts(5);
        $recent_orders = $this->analytics->getRecentOrders(10);
        
        include 'views/layouts/header.php';
        include 'views/admin/dashboard.php';
        include 'views/layouts/footer.php';
    }

    public function products() {
        if($_POST) {
            // Xử lý thêm/sửa sản phẩm
            $this->handleProductForm();
        }
        
        $products = $this->product->getAll(50);
        
        include 'views/layouts/header.php';
        include 'views/admin/products.php';
        include 'views/layouts/footer.php';
    }

    public function articles() {
        if($_POST) {
            // Xử lý thêm/sửa bài viết
            $this->handleArticleForm();
        }
        
        $articles = $this->article->getAll(20);
        
        include 'views/layouts/header.php';
        include 'views/admin/articles.php';
        include 'views/layouts/footer.php';
    }

    public function orders() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $orders = $this->order->getAll($limit, $offset);
        $total_orders = $this->order->getTotalCount();
        $total_pages = ceil($total_orders / $limit);
        
        include 'views/layouts/header.php';
        include 'views/admin/orders.php';
        include 'views/layouts/footer.php';
    }

    private function handleProductForm() {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'sale_price' => $_POST['sale_price'] ?? null,
            'category_id' => $_POST['category_id'],
            'quantity' => $_POST['quantity'],
            'image' => $this->uploadImage('product')
        ];
        
        if($this->product->create($data)) {
            $success = "Thêm sản phẩm thành công!";
        } else {
            $error = "Có lỗi xảy ra!";
        }
    }

    private function handleArticleForm() {
        $data = [
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'],
            'category' => $_POST['category'],
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'author_id' => $_SESSION['user_id'],
            'image' => $this->uploadImage('article')
        ];
        
        if($this->article->create($data)) {
            $success = "Thêm bài viết thành công!";
        } else {
            $error = "Có lỗi xảy ra!";
        }
    }

    private function uploadImage($type) {
        if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = "uploads/{$type}s/";
            if(!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . $_FILES['image']['name'];
            $uploadPath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                return $fileName;
            }
        }
        return null;
    }
}
?>