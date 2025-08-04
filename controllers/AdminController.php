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
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($current_page - 1) * $limit;

        $products = $this->product->getAll($limit, $offset);
        $categoryModel = new Category($this->db);
        $categories = $categoryModel->getAll();
        $total_products = $this->product->getTotalCount();
        $total_pages = ceil($total_products / $limit);

        include 'views/layouts/header.php';
        include 'views/admin/products.php';
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

    public function orders() {
        $action = 'admin_orders';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $orders = $this->order->getAll($limit, $offset);
        $total_orders = $this->order->getTotalCount();
        $total_pages = ceil($total_orders / $limit);
        $current_page = $page;
        
        include 'views/layouts/header.php';
        include 'views/admin/orders.php';
        include 'views/layouts/footer.php';
    }

    public function articles() {
        $action = 'admin_articles';
        if($_POST) {
            $this->handleArticleForm();
        }
        
        $articles = $this->article->getAll(20);
        
        include 'views/layouts/header.php';
        include 'views/admin/articles.php';
        include 'views/layouts/footer.php';
    }

    // AJAX endpoints
    public function getCategory() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        $categoryModel = new Category($this->db);
        $category = $categoryModel->getById($id);
        
        if($category) {
            echo json_encode(['success' => true, 'category' => $category]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy danh mục']);
        }
    }

    public function saveCategory() {
        header('Content-Type: application/json');
        
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }
        
        // Validate input
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $id = $_POST['id'] ?? null;
        
        if (empty(trim($name))) {
            echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
            return;
        }
        
        $categoryModel = new Category($this->db);
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = $this->uploadImage('category');
            if (!$image) {
                echo json_encode(['success' => false, 'message' => 'Không thể tải lên hình ảnh']);
                return;
            }
        } else if ($id) {
            // Keep existing image for updates
            $existing = $categoryModel->getById($id);
            $image = $existing['image'] ?? null;
        }
        
        $data = [
            'name' => trim($name),
            'description' => trim($description),
            'image' => $image
        ];
        
        try {
            if ($id) {
                if ($categoryModel->update($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Cập nhật danh mục thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật danh mục']);
                }
            } else {
                if ($categoryModel->create($data)) {
                    echo json_encode(['success' => true, 'message' => 'Thêm danh mục thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể thêm danh mục']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function deleteCategory() {
        header('Content-Type: application/json');
        
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }
        
        $categoryModel = new Category($this->db);
        
        try {
            if ($categoryModel->delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Xóa danh mục thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function saveProduct() {
        header('Content-Type: application/json');
        
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }

        // Validate required fields
        $required_fields = ['name', 'price', 'category_id', 'quantity'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
                return;
            }
        }

        $id = $_POST['product_id'] ?? null;
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = $this->uploadImage('product');
            if (!$image) {
                echo json_encode(['success' => false, 'message' => 'Không thể tải lên hình ảnh']);
                return;
            }
        } else if ($id) {
            // Keep existing image for updates
            $existing = $this->product->getById($id);
            $image = $existing['image'] ?? null;
        }

        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price' => (float)$_POST['price'],
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'category_id' => (int)$_POST['category_id'],
            'quantity' => (int)$_POST['quantity'],
            'image' => $image
        ];

        try {
            if ($id) {
                if ($this->product->update($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật sản phẩm']);
                }
            } else {
                if ($this->product->create($data)) {
                    echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể thêm sản phẩm']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function getProduct() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        $product = $this->product->getById($id);
        
        if($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        }
    }

    public function deleteProduct() {
        header('Content-Type: application/json');
        
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }
        
        $id = $_POST['product_id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }
        
        try {
            if ($this->product->delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function updateOrderStatus() {
        header('Content-Type: application/json');
        
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }

        $order_id = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$order_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            return;
        }

        try {
            if ($this->order->updateStatus($order_id, $status)) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    private function handleArticleForm() {
        if (!isset($_POST['title']) || !isset($_POST['content'])) {
            return;
        }

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = $this->uploadImage('article');
        }

        $data = [
            'title' => trim($_POST['title']),
            'content' => $_POST['content'],
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'category' => $_POST['category'] ?? 'news',
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'author_id' => $_SESSION['user_id'],
            'image' => $image
        ];
        
        if($this->article->create($data)) {
            $success = "Thêm bài viết thành công!";
        } else {
            $error = "Có lỗi xảy ra!";
        }
    }

    private function uploadImage($type) {
        $uploadDir = "uploads/{$type}s/";
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            return false;
        }
        
        // Check file size (max 2MB)
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            return false;
        }
        
        $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            return $fileName;
        }
        
        return false;
    }
}
?>