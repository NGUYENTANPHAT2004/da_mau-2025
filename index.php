<?php
// Load bootstrap
require_once 'bootstrap.php';

// Parse URL
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

$action = $urlParts[0] ?? 'home';
$id = $urlParts[1] ?? null;
$subAction = $urlParts[2] ?? null;

// Set headers for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
}

// Route handling
try {
    switch($action) {
        // ===== MAIN PAGES =====
        case 'home':
        case '':
            $controller = new HomeController();
            $controller->index();
            break;
            
        case 'products':
            $controller = new ProductController();
            $controller->index();
            break;
            
        case 'product-detail':
        case 'product_detail':
            if($id) {
                $controller = new ProductController();
                $controller->detail($id);
            } else {
                redirect(BASE_URL . 'products');
            }
            break;
            
        // ===== USER AUTHENTICATION =====
        case 'login':
            $controller = new UserController();
            $controller->login();
            break;
            
        case 'register':
            $controller = new UserController();
            $controller->register();
            break;
            
        case 'logout':
            $controller = new UserController();
            $controller->logout();
            break;
            
        // ===== USER PROFILE =====
        case 'profile':
            $controller = new UserController();
            $controller->profile();
            break;
            
        case 'change-password':
            $controller = new UserController();
            $controller->changePassword();
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
                redirect(BASE_URL . 'orders');
            }
            break;
            
        // ===== CART MANAGEMENT =====
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
            
        // ===== CHECKOUT PROCESS =====
        case 'checkout':
            if(!class_exists('CheckoutController')) {
                // Simple checkout redirect for now
                redirect(BASE_URL . 'cart');
                break;
            }
            $controller = new CheckoutController();
            if($id === 'process') {
                $controller->process();
            } else {
                $controller->index();
            }
            break;
            
        case 'order-success':
            if($id && class_exists('CheckoutController')) {
                $controller = new CheckoutController();
                $controller->success($id);
            } else {
                redirect(BASE_URL . 'home');
            }
            break;
            
        // ===== REVIEWS =====
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
            
        // ===== ARTICLES & BLOG =====
        case 'articles':
        case 'blog':
            $controller = new ArticleController();
            $controller->index();
            break;
            
        case 'article-detail':
        case 'blog-detail':
            if($id) {
                $controller = new ArticleController();
                $controller->detail($id);
            } else {
                redirect(BASE_URL . 'articles');
            }
            break;
            
        // ===== SEARCH =====
        case 'search':
            if(class_exists('SearchController')) {
                $controller = new SearchController();
                if($id === 'ajax') {
                    $controller->ajaxSearch();
                } else {
                    $controller->index();
                }
            } else {
                // Simple search fallback
                $controller = new ProductController();
                $controller->index();
            }
            break;
            
        // ===== ADMIN ROUTES =====
        case 'admin':
            if(!isAdmin()) {
                redirect(BASE_URL . 'login');
                break;
            }
            
            $controller = new AdminController();
            $controller->dashboard();
            break;

        case 'admin-products':
            if(!isAdmin()) {
                redirect(BASE_URL . 'login');
                break;
            }
            $controller = new AdminController();
            $controller->products();
            break;

        case 'admin-categories':
            if(!isAdmin()) {
                redirect(BASE_URL . 'login');
                break;
            }
            $controller = new AdminController();
            $controller->categories();
            break;

        case 'admin-orders':
            if(!isAdmin()) {
                redirect(BASE_URL . 'login');
                break;
            }
            $controller = new AdminController();
            $controller->orders();
            break;

        case 'admin-articles':
            if(!isAdmin()) {
                redirect(BASE_URL . 'login');
                break;
            }
            $controller = new AdminController();
            $controller->articles();
            break;

        // ===== ADMIN AJAX ENDPOINTS =====
        case 'get-category':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->getCategory();
            break;

        case 'save-category':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->saveCategory();
            break;

        case 'delete-category':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->deleteCategory();
            break;

        case 'get-product':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->getProduct();
            break;

        case 'save-product':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->saveProduct();
            break;

        case 'delete-product':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->deleteProduct();
            break;

        case 'update-order-status':
            if(!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                break;
            }
            $controller = new AdminController();
            $controller->updateOrderStatus();
            break;
            
        // ===== CONTACT PAGE =====
        case 'contact':
            if(class_exists('PageController')) {
                $controller = new PageController();
                $controller->contact();
            } else {
                redirect(BASE_URL . 'home');
            }
            break;
            
        case 'about':
            if(class_exists('PageController')) {
                $controller = new PageController();
                $controller->about();
            } else {
                redirect(BASE_URL . 'home');
            }
            break;
            
        // ===== 404 ERROR =====
        case '404':
        case 'not-found':
        default:
            http_response_code(404);
            include 'views/layouts/header.php';
            include 'views/errors/404.php';
            include 'views/layouts/footer.php';
            break;
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Router Error: " . $e->getMessage());
    
    // Show 500 error page for non-AJAX requests
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(500);
        include 'views/layouts/header.php';
        include 'views/errors/500.php';
        include 'views/layouts/footer.php';
    } else {
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}
?>