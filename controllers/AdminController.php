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
        $action = 'admin';
        $stats = $this->analytics->getDashboardStats();
        $monthly_revenue = $this->analytics->getMonthlyRevenue();
        $top_products = $this->analytics->getTopProducts(5);
        $recent_orders = $this->analytics->getRecentOrders(10);

        // Tách biến cho view
        $total_revenue = $stats['total_revenue'] ?? 0;
        $new_orders = $stats['today_orders'] ?? 0;
        $total_products = $stats['total_products'] ?? 0;
        $total_customers = $stats['total_customers'] ?? 0;

        include 'views/layouts/header.php';
        include 'views/admin/dashboard.php';
        include 'views/layouts/footer.php';
    }

    public function products() {
        $action = 'admin_products';
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
        $action = 'admin_articles';
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
        $action = 'admin_orders';
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

    public function categories() {
        $action = 'admin_categories';
        $categoryModel = new Category($this->db);
        $categories = $categoryModel->getAll();
        include 'views/layouts/header.php';
        include 'views/admin/categories.php';
        include 'views/layouts/footer.php';
    }

    public function getCategory($id) {
        $categoryModel = new Category($this->db);
        $category = $categoryModel->getById($id);
        if($category) {
            echo json_encode(['success' => true, 'category' => $category]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy danh mục']);
        }
    }

    public function saveCategory() {
        $categoryModel = new Category($this->db);
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'image' => isset($_FILES['image']) ? $this->uploadImage('category') : ($_POST['image'] ?? null)
        ];
        if(isset($_POST['id']) && $_POST['id']) {
            // Update
            $result = $categoryModel->update($_POST['id'], $data);
            $msg = $result ? 'Cập nhật thành công' : 'Cập nhật thất bại';
        } else {
            // Create
            $result = $categoryModel->create($data);
            $msg = $result ? 'Thêm thành công' : 'Thêm thất bại';
        }
        echo json_encode(['success' => $result, 'message' => $msg]);
    }

    public function deleteCategory() {
        $categoryModel = new Category($this->db);
        $result = false;
        if(isset($_POST['id'])) {
            $result = $categoryModel->delete($_POST['id']);
        }
        echo json_encode(['success' => $result]);
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