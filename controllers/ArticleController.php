<?php
class ArticleController {
    private $db;
    private $article;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->article = new Article($this->db);
    }

    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6;
        $offset = ($page - 1) * $limit;
        
        if(isset($_GET['search'])) {
            $articles = $this->article->search($_GET['search'], $limit);
        } else {
            $articles = $this->article->getAll($limit, $offset);
        }
        
        $featured_articles = $this->article->getFeatured(3);
        
        include 'views/layouts/header.php';
        include 'views/articles/index.php';
        include 'views/layouts/footer.php';
    }

    public function detail($id) {
        $article = $this->article->getById($id);
        if(!$article) {
            redirect('articles');
            return;
        }
        
        $related_articles = $this->article->getAll(3);
        
        include 'views/layouts/header.php';
        include 'views/articles/detail.php';
        include 'views/layouts/footer.php';
    }
}
?>