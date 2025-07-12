<?php
class HomeController {
    private $db;
    private $product;
    private $category;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->category = new Category($this->db);
    }

    public function index() {
        $action = 'home';
        $featured_products = $this->product->getAll(8);
        $categories = $this->category->getAll();
        
        include 'views/layouts/header.php';
        include 'views/home/index.php';
        include 'views/layouts/footer.php';
    }
}
?>