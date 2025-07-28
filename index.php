<?php
// Load bootstrap
require_once 'bootstrap.php';

// Parse URL
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

$action = $urlParts[0] ?? 'home';
$id = $urlParts[1] ?? null;

// Route handling
switch($action) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;
        
    case 'products':
        $controller = new ProductController();
        $controller->index();
        break;
        
    case 'product_detail':
        if($id) {
            $controller = new ProductController();
            $controller->detail($id);
        } else {
            redirect('products');
        }
        break;
        
    // User routes
    case 'login':
        $controller = new UserController();
        $controller->login();
        break;
        
    case 'register':
        $controller = new UserController();
        $controller->register();
        break;
        
    case 'profile':
        $controller = new UserController();
        $controller->profile();
        break;
        
    case 'orders':
        $controller = new UserController();
        $controller->orders();
        break;
        
    case 'order-detail':
        if($id) {
            $controller = new UserController();
            $controller->orderDetail($id);
        } else {
            redirect('orders');
        }
        break;
        
    case 'logout':
        $controller = new UserController();
        $controller->logout();
        break;
        
    // Cart routes
    case 'cart':
        $controller = new CartController();
        $controller->index();
        break;
        
    case 'add-to-cart':
        $controller = new CartController();
        $controller->add();
        break;
        
    case 'update-cart':
        $controller = new CartController();
        $controller->update();
        break;
        
    case 'remove-from-cart':
        $controller = new CartController();
        $controller->remove();
        break;
        
    case 'clear-cart':
        $controller = new CartController();
        $controller->clear();
        break;
        
    case 'get-cart-count':
        $controller = new CartController();
        $controller->getCount();
        break;
        
    // Admin routes
    case 'admin':
        $controller = new AdminController();
        $controller->dashboard();
        break;
        
    case 'admin-products':
        $controller = new AdminController();
        $controller->products();
        break;
        
    case 'admin-articles':
        $controller = new AdminController();
        $controller->articles();
        break;
        
    case 'admin-orders':
        $controller = new AdminController();
        $controller->orders();
        break;
        
    case 'admin-categories':
        $controller = new AdminController();
        $controller->categories();
        break;
        
    // Reviews
    case 'add-review':
        $controller = new ReviewController();
        $controller->addReview();
        break;
        
    case 'update-review':
        $controller = new ReviewController();
        $controller->updateReview();
        break;
        
    case 'delete-review':
        $controller = new ReviewController();
        $controller->deleteReview();
        break;
        
    // Articles
    case 'articles':
        $controller = new ArticleController();
        $controller->index();
        break;
        
    case 'article-detail':
        if($id) {
            $controller = new ArticleController();
            $controller->detail($id);
        } else {
            redirect('articles');
        }
        break;
    
    // Thêm route get-category
    case 'get-category':
        if($id) {
            $controller = new AdminController();
            $controller->getCategory($id);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID danh mục không hợp lệ']);
        }
        break;
        
    // Thêm route này nếu chưa có
    case 'save-category':
        $controller = new AdminController();
        $controller->saveCategory();
        break;
        
    default:
        $controller = new HomeController();
        $controller->index();
        break;
}
?>