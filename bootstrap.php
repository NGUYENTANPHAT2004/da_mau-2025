<?php
// Start session
session_start();

// Define constants
define('BASE_URL', 'http://localhost/ecommerce_mvc/');
define('SITE_NAME', 'FPoly Shop');
define('ADMIN_EMAIL', 'admin@fpolyshop.com');
define('ROOT_PATH', __DIR__);

// Include helper functions
require_once ROOT_PATH . '/includes/helpers.php';

// Include database configuration
require_once ROOT_PATH . '/config/database.php';

// Include all models
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Product.php';
require_once ROOT_PATH . '/models/Category.php';
require_once ROOT_PATH . '/models/Cart.php';
require_once ROOT_PATH . '/models/Order.php';
require_once ROOT_PATH . '/models/Article.php';
require_once ROOT_PATH . '/models/Analytics.php';
require_once ROOT_PATH . '/models/Payment.php';
require_once ROOT_PATH . '/models/Review.php';

// Include all controllers
require_once ROOT_PATH . '/controllers/HomeController.php';
require_once ROOT_PATH . '/controllers/ProductController.php';
require_once ROOT_PATH . '/controllers/ArticleController.php';
require_once ROOT_PATH . '/controllers/AdminController.php';
require_once ROOT_PATH . '/controllers/ReviewController.php';
require_once ROOT_PATH . '/controllers/UserController.php';
require_once ROOT_PATH . '/controllers/CartController.php';
?> 