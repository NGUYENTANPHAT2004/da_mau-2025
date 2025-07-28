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
        $limit = 20;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($current_page < 1) $current_page = 1;
        $offset = ($current_page - 1) * $limit;
        $products = $this->product->getAll($limit, $offset);
        $total_products = $this->product->getTotalCount();
        $total_pages = ceil($total_products / $limit);
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
        // Kiểm tra quyền admin
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }
        
        // Debug: Log dữ liệu nhận được
        error_log('POST data: ' . print_r($_POST, true));
        error_log('FILES data: ' . print_r($_FILES, true));
        
        $db = new Database();
        $category = new Category($db->getConnection());
        
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $existingImage = $_POST['existing_image'] ?? '';
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
            return;
        }
        
        // Xử lý tải lên hình ảnh
        $image = $existingImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadDir = 'uploads/categories/';
            
            // Tạo thư mục nếu nó không tồn tại
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            
            // Kiểm tra xem tệp hình ảnh có phải là hình ảnh thực sự không
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                echo json_encode(['success' => false, 'message' => 'File không phải là hình ảnh']);
                return;
            }
            
            // Kiểm tra kích thước tệp (giới hạn 2MB)
            if ($_FILES['image']['size'] > 2000000) {
                echo json_encode(['success' => false, 'message' => 'Kích thước file quá lớn (tối đa 2MB)']);
                return;
            }
            
            // Cho phép một số định dạng tệp nhất định
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file JPG, JPEG, PNG & GIF']);
                return;
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = $fileName;
                
                // Xóa hình ảnh cũ nếu tồn tại và một hình ảnh mới được tải lên
                if (!empty($existingImage) && $id && file_exists($uploadDir . $existingImage)) {
                    unlink($uploadDir . $existingImage);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi khi tải lên hình ảnh']);
                return;
            }
        }
        
        $data = [
            'name' => $name,
            'description' => $description,
            'image' => $image
        ];
        
        try {
            if ($id) {
                // Cập nhật danh mục hiện có
                if ($category->update($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Cập nhật danh mục thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật danh mục']);
                }
            } else {
                // Tạo danh mục mới
                if ($category->create($data)) {
                    echo json_encode(['success' => true, 'message' => 'Thêm danh mục thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể thêm danh mục']);
                }
            }
        } catch (Exception $e) {
            error_log('Error in saveCategory: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
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