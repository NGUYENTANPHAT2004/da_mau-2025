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

// CORS headers for API calls
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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
            if($id) {
                $controller = new ProductController();
                $controller->detail($id);
            } else {
                redirect('products');
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
            
        case 'forgot-password':
            $controller = new UserController();
            $controller->forgotPassword();
            break;
            
        case 'reset-password':
            $controller = new UserController();
            $controller->resetPassword($id);
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
                redirect('orders');
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
            
        // ===== WISHLIST MANAGEMENT =====
        case 'wishlist':
            $controller = new WishlistController();
            if($id === 'add') {
                $controller->add();
            } elseif($id === 'remove') {
                $controller->remove();
            } elseif($id === 'toggle') {
                $controller->toggle();
            } elseif($id === 'clear') {
                $controller->clear();
            } elseif($id === 'move-to-cart') {
                $controller->moveToCart();
            } elseif($id === 'get-count') {
                $controller->getCount();
            } else {
                $controller->index();
            }
            break;
            
        // ===== CHECKOUT PROCESS =====
        case 'checkout':
            $controller = new CheckoutController();
            if($id === 'process') {
                $controller->process();
            } elseif($id === 'apply-coupon') {
                $controller->applyCoupon();
            } else {
                $controller->index();
            }
            break;
            
        case 'order-success':
            if($id) {
                $controller = new CheckoutController();
                $controller->success($id);
            } else {
                redirect('home');
            }
            break;
            
        // ===== PAYMENT =====
        case 'payment':
            $controller = new PaymentController();
            if($id === 'vnpay-return') {
                $controller->vnpayReturn();
            } elseif($id === 'momo-return') {
                $controller->momoReturn();
            } else {
                redirect('home');
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
            
        case 'review':
            $controller = new ReviewController();
            if($id === 'helpful') {
                $controller->markHelpful();
            }
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
                redirect('articles');
            }
            break;
            
        // ===== SEARCH & FILTER =====
        case 'search':
            $controller = new SearchController();
            if($id === 'ajax') {
                $controller->ajaxSearch();
            } elseif($id === 'suggestions') {
                $controller->getSuggestions();
            } else {
                $controller->index();
            }
            break;
            
        case 'filter':
            $controller = new ProductController();
            $controller->filter();
            break;
            
        // ===== ADMIN ROUTES =====
        case 'admin':
            if(!isAdmin()) {
                redirect('login');
                break;
            }
            
            $controller = new AdminController();
            
            switch($id) {
                case 'dashboard':
                case '':
                case null:
                    $controller->dashboard();
                    break;
                    
                case 'products':
                    if($subAction === 'add') {
                        $controller->addProduct();
                    } elseif($subAction === 'edit') {
                        $controller->editProduct($_GET['product_id'] ?? null);
                    } elseif($subAction === 'delete') {
                        $controller->deleteProduct();
                    } elseif($subAction === 'bulk-action') {
                        $controller->bulkProductAction();
                    } else {
                        $controller->products();
                    }
                    break;
                    
                case 'categories':
                    if($subAction === 'add') {
                        $controller->addCategory();
                    } elseif($subAction === 'edit') {
                        $controller->editCategory($_GET['category_id'] ?? null);
                    } elseif($subAction === 'delete') {
                        $controller->deleteCategory();
                    } else {
                        $controller->categories();
                    }
                    break;
                    
                case 'orders':
                    if($subAction === 'detail') {
                        $controller->orderDetail($_GET['order_id'] ?? null);
                    } elseif($subAction === 'update-status') {
                        $controller->updateOrderStatus();
                    } elseif($subAction === 'print-invoice') {
                        $controller->printInvoice($_GET['order_id'] ?? null);
                    } else {
                        $controller->orders();
                    }
                    break;
                    
                case 'customers':
                    if($subAction === 'detail') {
                        $controller->customerDetail($_GET['customer_id'] ?? null);
                    } elseif($subAction === 'export') {
                        $controller->exportCustomers();
                    } else {
                        $controller->customers();
                    }
                    break;
                    
                case 'reviews':
                    if($subAction === 'approve') {
                        $controller->approveReview();
                    } elseif($subAction === 'reject') {
                        $controller->rejectReview();
                    } else {
                        $controller->reviews();
                    }
                    break;
                    
                case 'coupons':
                    if($subAction === 'add') {
                        $controller->addCoupon();
                    } elseif($subAction === 'edit') {
                        $controller->editCoupon($_GET['coupon_id'] ?? null);
                    } elseif($subAction === 'delete') {
                        $controller->deleteCoupon();
                    } else {
                        $controller->coupons();
                    }
                    break;
                    
                case 'analytics':
                    if($subAction === 'sales') {
                        $controller->salesAnalytics();
                    } elseif($subAction === 'products') {
                        $controller->productAnalytics();
                    } elseif($subAction === 'customers') {
                        $controller->customerAnalytics();
                    } else {
                        $controller->analytics();
                    }
                    break;
                    
                case 'settings':
                    if($subAction === 'general') {
                        $controller->generalSettings();
                    } elseif($subAction === 'email') {
                        $controller->emailSettings();
                    } elseif($subAction === 'payment') {
                        $controller->paymentSettings();
                    } elseif($subAction === 'shipping') {
                        $controller->shippingSettings();
                    } else {
                        $controller->settings();
                    }
                    break;
                    
                case 'articles':
                    if($subAction === 'add') {
                        $controller->addArticle();
                    } elseif($subAction === 'edit') {
                        $controller->editArticle($_GET['article_id'] ?? null);
                    } elseif($subAction === 'delete') {
                        $controller->deleteArticle();
                    } else {
                        $controller->articles();
                    }
                    break;
                    
                case 'media':
                    if($subAction === 'upload') {
                        $controller->uploadMedia();
                    } elseif($subAction === 'delete') {
                        $controller->deleteMedia();
                    } else {
                        $controller->mediaLibrary();
                    }
                    break;
                    
                case 'backup':
                    if($subAction === 'create') {
                        $controller->createBackup();
                    } elseif($subAction === 'restore') {
                        $controller->restoreBackup();
                    } elseif($subAction === 'download') {
                        $controller->downloadBackup($_GET['file'] ?? null);
                    } else {
                        $controller->backup();
                    }
                    break;
                    
                default:
                    $controller->dashboard();
                    break;
            }
            break;
            
        // ===== API ROUTES =====
        case 'api':
            header('Content-Type: application/json');
            
            switch($id) {
                case 'products':
                    $controller = new ApiController();
                    if($subAction === 'search') {
                        $controller->searchProducts();
                    } elseif($subAction === 'filter') {
                        $controller->filterProducts();
                    } elseif($subAction === 'related') {
                        $controller->getRelatedProducts($_GET['product_id'] ?? null);
                    } else {
                        $controller->getProducts();
                    }
                    break;
                    
                case 'categories':
                    $controller = new ApiController();
                    $controller->getCategories();
                    break;
                    
                case 'cart':
                    $controller = new ApiController();
                    if($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addToCart();
                    } elseif($_SERVER['REQUEST_METHOD'] === 'PUT') {
                        $controller->updateCart();
                    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                        $controller->removeFromCart();
                    } else {
                        $controller->getCart();
                    }
                    break;
                    
                case 'wishlist':
                    $controller = new ApiController();
                    if($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addToWishlist();
                    } elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                        $controller->removeFromWishlist();
                    } else {
                        $controller->getWishlist();
                    }
                    break;
                    
                case 'user':
                    $controller = new ApiController();
                    if($subAction === 'login') {
                        $controller->login();
                    } elseif($subAction === 'register') {
                        $controller->register();
                    } elseif($subAction === 'profile') {
                        $controller->getUserProfile();
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Endpoint not found']);
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'API endpoint not found']);
                    break;
            }
            break;
            
        // ===== UTILITY PAGES =====
        case 'contact':
            $controller = new PageController();
            $controller->contact();
            break;
            
        case 'about':
            $controller = new PageController();
            $controller->about();
            break;
            
        case 'privacy':
            $controller = new PageController();
            $controller->privacy();
            break;
            
        case 'terms':
            $controller = new PageController();
            $controller->terms();
            break;
            
        case 'faq':
            $controller = new PageController();
            $controller->faq();
            break;
            
        case 'size-guide':
            $controller = new PageController();
            $controller->sizeGuide();
            break;
            
        case 'shipping-info':
            $controller = new PageController();
            $controller->shippingInfo();
            break;
            
        case 'return-policy':
            $controller = new PageController();
            $controller->returnPolicy();
            break;
            
        // ===== SPECIAL ROUTES =====
        case 'sitemap':
            $controller = new SitemapController();
            $controller->generate();
            break;
            
        case 'robots':
            header('Content-Type: text/plain');
            echo "User-agent: *\n";
            echo "Allow: /\n";
            echo "Sitemap: " . BASE_URL . "sitemap.xml\n";
            break;
            
        case 'manifest':
            header('Content-Type: application/json');
            $controller = new PWAController();
            $controller->manifest();
            break;
            
        case 'sw':
            header('Content-Type: application/javascript');
            $controller = new PWAController();
            $controller->serviceWorker();
            break;
            
        // ===== AJAX ROUTES =====
        case 'ajax':
            switch($id) {
                case 'product-quick-view':
                    $controller = new AjaxController();
                    $controller->productQuickView($_POST['product_id'] ?? null);
                    break;
                    
                case 'update-cart-item':
                    $controller = new AjaxController();
                    $controller->updateCartItem();
                    break;
                    
                case 'apply-coupon':
                    $controller = new AjaxController();
                    $controller->applyCoupon();
                    break;
                    
                case 'get-shipping-cost':
                    $controller = new AjaxController();
                    $controller->getShippingCost();
                    break;
                    
                case 'newsletter-subscribe':
                    $controller = new AjaxController();
                    $controller->newsletterSubscribe();
                    break;
                    
                case 'contact-form':
                    $controller = new AjaxController();
                    $controller->contactForm();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'AJAX endpoint not found']);
                    break;
            }
            break;
            
        // ===== WEBHOOKS =====
        case 'webhook':
            switch($id) {
                case 'vnpay':
                    $controller = new WebhookController();
                    $controller->vnpayIPN();
                    break;
                    
                case 'momo':
                    $controller = new WebhookController();
                    $controller->momoIPN();
                    break;
                    
                default:
                    http_response_code(404);
                    break;
            }
            break;
            
        // ===== ERROR PAGES =====
        case '404':
        case 'not-found':
            http_response_code(404);
            include 'views/layouts/header.php';
            include 'views/errors/404.php';
            include 'views/layouts/footer.php';
            break;
            
        case '500':
        case 'error':
            http_response_code(500);
            include 'views/layouts/header.php';
            include 'views/errors/500.php';
            include 'views/layouts/footer.php';
            break;
            
        // ===== MAINTENANCE =====
        case 'maintenance':
            if(!isAdmin()) {
                http_response_code(503);
                include 'views/maintenance.php';
                break;
            }
            // Admin can bypass maintenance
            $controller = new HomeController();
            $controller->index();
            break;
            
        // ===== DEFAULT FALLBACK =====
        default:
            // Try to find page by slug
            $controller = new PageController();
            if($controller->findPageBySlug($action)) {
                $controller->showPage($action);
            } else {
                // 404 if nothing matches
                http_response_code(404);
                include 'views/layouts/header.php';
                include 'views/errors/404.php';
                include 'views/layouts/footer.php';
            }
            break;
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Router Error: " . $e->getMessage());
    
    // Show 500 error page
    http_response_code(500);
    if(!headers_sent()) {
        include 'views/layouts/header.php';
        include 'views/errors/500.php';
        include 'views/layouts/footer.php';
    }
}

// Function to check if maintenance mode is enabled
function isMaintenanceMode() {
    return file_exists(ROOT_PATH . '/.maintenance');
}

// Check maintenance mode (except for admin)
if(isMaintenanceMode() && !isAdmin() && $action !== 'maintenance') {
    http_response_code(503);
    include 'views/maintenance.php';
    exit;
}
?>