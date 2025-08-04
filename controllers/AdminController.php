<?php
class AdminController {
    private $db;
    private $product;
    private $category;

    public function __construct() {
        if(!isAdmin()) {
            redirect('login');
            return;
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->category = new Category($this->db);
    }

    public function dashboard() {
        $action = 'admin';
        
        // Lấy thống kê cơ bản
        $total_products = $this->product->getTotalCount();
        $total_categories = $this->category->getTotalCount();
        
        // Mock data cho dashboard - có thể thay thế bằng Analytics class sau
        $total_revenue = 150000000;
        $new_orders = 25;
        $total_customers = 150;
        $monthly_revenue = [
            ['month' => 'Tháng 10', 'revenue' => 45000000],
            ['month' => 'Tháng 11', 'revenue' => 52000000],
            ['month' => 'Tháng 12', 'revenue' => 48000000],
        ];
        $top_products = [];
        $recent_orders = [];

        include 'views/layouts/header.php';
        include 'views/admin/dashboard.php';
        include 'views/layouts/footer.php';
    }

    public function products() {
        $action = 'admin_products';
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($current_page - 1) * $limit;

        // Xử lý filter
        $filters = [
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        if(array_filter($filters)) {
            $products = $this->product->getFiltered($filters, $limit, $offset);
        } else {
            $products = $this->product->getAll($limit, $offset);
        }

        $categories = $this->category->getAll();
        $total_products = $this->product->getTotalCount();
        $total_pages = ceil($total_products / $limit);

        include 'views/layouts/header.php';
        include 'views/admin/products.php';
        include 'views/layouts/footer.php';
    }

    public function categories() {
        $action = 'admin_categories';
        $categories = $this->category->getAllWithProductCount();
        
        include 'views/layouts/header.php';
        include 'views/admin/categories.php';
        include 'views/layouts/footer.php';
    }

    // AJAX: Lấy thông tin danh mục
    public function getCategory() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        $category = $this->category->getById($id);
        
        if($category) {
            echo json_encode(['success' => true, 'category' => $category]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy danh mục']);
        }
    }

    // AJAX: Lưu danh mục
    public function saveCategory() {
        header('Content-Type: application/json');
        
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            return;
        }
        
        // Validate input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $id = $_POST['id'] ?? null;
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
            return;
        }
        
        // Kiểm tra tên danh mục đã tồn tại chưa
        if($this->category->nameExists($name, $id)) {
            echo json_encode(['success' => false, 'message' => 'Tên danh mục đã tồn tại']);
            return;
        }
        
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
            $existing = $this->category->getById($id);
            $image = $existing['image'] ?? null;
        }
        
        $data = [
            'name' => $name,
            'description' => $description,
            'image' => $image
        ];
        
        try {
            if ($id) {
                if ($this->category->update($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Cập nhật danh mục thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật danh mục']);
                }
            } else {
                if ($this->category->create($data)) {
                    echo json_encode(['success' => true, 'message' => 'Thêm danh mục thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Không thể thêm danh mục']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    // AJAX: Xóa danh mục
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
        
        try {
            $result = $this->category->delete($id);
            if ($result === false) {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục vì còn sản phẩm thuộc danh mục này']);
            } elseif ($result) {
                echo json_encode(['success' => true, 'message' => 'Xóa danh mục thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    // AJAX: Lấy thông tin sản phẩm
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

    // AJAX: Lưu sản phẩm
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
        
        // Validate price
        if(!is_numeric($_POST['price']) || $_POST['price'] < 0) {
            echo json_encode(['success' => false, 'message' => 'Giá sản phẩm không hợp lệ']);
            return;
        }
        
        // Validate quantity
        if(!is_numeric($_POST['quantity']) || $_POST['quantity'] < 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
            return;
        }

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

    // AJAX: Xóa sản phẩm
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

    // Upload image helper
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