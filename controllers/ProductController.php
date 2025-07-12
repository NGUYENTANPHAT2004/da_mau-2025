<?php
class ProductController {
    private $db;
    private $product;
    private $category;
    private $review;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->category = new Category($this->db);
        $this->review = new Review($this->db);
    }
    public function index() {
        $action = 'products';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        if(isset($_GET['category'])) {
            $products = $this->product->getByCategory($_GET['category'], $limit);
        } elseif(isset($_GET['search'])) {
            $products = $this->product->search($_GET['search'], $limit);
        } else {
            $products = $this->product->getAll($limit, $offset);
        }
        
        $categories = $this->category->getAll();
        
        include 'views/layouts/header.php';
        include 'views/products/index.php';
        include 'views/layouts/footer.php';
    }

    public function detail($id) {
        $action = 'product_detail';
        $product = $this->product->getById($id);
        if(!$product) {
            redirect('404');
            return;
        }
        
        // Lấy thông tin đánh giá
        $reviews = $this->review->getProductReviews($id, 10);
        $rating_stats = $this->review->getProductRatingStats($id);
        $can_review = isLoggedIn() ? $this->review->canUserReviewProduct($_SESSION['user_id'], $id) : false;
        $user_review = isLoggedIn() ? $this->review->getUserReview($_SESSION['user_id'], $id) : null;
        
        $related_products = $this->product->getByCategory($product['category_id'], 4);
        
        include 'views/layouts/header.php';
        include 'views/products/detail.php';
        include 'views/layouts/footer.php';
    }
}
?>