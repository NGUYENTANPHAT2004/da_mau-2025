<?php

class SearchController {
    private $db;
    private $product;
    private $category;
    private $article;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
        $this->category = new Category($this->db);
        $this->article = new Article($this->db);
    }

    public function index() {
        $query = $_GET['q'] ?? '';
        $category_id = $_GET['category'] ?? '';
        $min_price = $_GET['min_price'] ?? '';
        $max_price = $_GET['max_price'] ?? '';
        $sort = $_GET['sort'] ?? 'relevance';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $results = [];
        $total_count = 0;

        if($query) {
            // Save search query to analytics
            $this->saveSearchQuery($query);
            
            // Perform search
            $search_data = $this->performAdvancedSearch($query, [
                'category_id' => $category_id,
                'min_price' => $min_price,
                'max_price' => $max_price,
                'sort' => $sort,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $results = $search_data['products'];
            $total_count = $search_data['total'];
        }

        $categories = $this->category->getAll();
        $total_pages = ceil($total_count / $limit);
        
        // Get search suggestions
        $suggestions = $this->getSearchSuggestions($query);
        
        // Get popular searches
        $popular_searches = $this->getPopularSearches();

        include 'views/layouts/header.php';
        include 'views/search/index.php';
        include 'views/layouts/footer.php';
    }

    public function ajaxSearch() {
        $query = $_POST['q'] ?? '';
        $limit = 8;

        if(strlen($query) < 2) {
            echo json_encode(['success' => false, 'message' => 'Query too short']);
            return;
        }

        $results = $this->performAdvancedSearch($query, ['limit' => $limit]);
        
        echo json_encode([
            'success' => true,
            'products' => $results['products'],
            'total' => $results['total']
        ]);
    }

    public function getSuggestions() {
        $query = $_GET['q'] ?? '';
        
        if(strlen($query) < 2) {
            echo json_encode([]);
            return;
        }

        $suggestions = $this->getSearchSuggestions($query, 6);
        
        echo json_encode($suggestions);
    }

    private function performAdvancedSearch($query, $options = []) {
        $category_id = $options['category_id'] ?? '';
        $min_price = $options['min_price'] ?? '';
        $max_price = $options['max_price'] ?? '';
        $sort = $options['sort'] ?? 'relevance';
        $limit = $options['limit'] ?? 12;
        $offset = $options['offset'] ?? 0;

        // Build search query
        $whereClause = "WHERE p.status = 'active' AND (
            p.name LIKE :query 
            OR p.description LIKE :query 
            OR p.short_description LIKE :query
            OR c.name LIKE :query
        )";
        
        $params = [':query' => "%{$query}%"];

        // Add filters
        if($category_id) {
            $whereClause .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }

        if($min_price) {
            $whereClause .= " AND COALESCE(p.sale_price, p.price) >= :min_price";
            $params[':min_price'] = $min_price;
        }

        if($max_price) {
            $whereClause .= " AND COALESCE(p.sale_price, p.price) <= :max_price";
            $params[':max_price'] = $max_price;
        }

        // Add sorting
        $orderClause = $this->getSortClause($sort);

        // Count query
        $countQuery = "SELECT COUNT(*) as total 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       {$whereClause}";
        
        $stmt = $this->db->prepare($countQuery);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Main query
        $mainQuery = "SELECT p.*, c.name as category_name,
                             MATCH(p.name, p.description) AGAINST(:search_query) as relevance_score
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      {$whereClause}
                      {$orderClause}
                      LIMIT :limit OFFSET :offset";

        $params[':search_query'] = $query;
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($mainQuery);
        foreach($params as $key => $value) {
            if($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'products' => $products,
            'total' => $total
        ];
    }

    private function getSortClause($sort) {
        switch($sort) {
            case 'price_asc':
                return "ORDER BY COALESCE(p.sale_price, p.price) ASC";
            case 'price_desc':
                return "ORDER BY COALESCE(p.sale_price, p.price) DESC";
            case 'newest':
                return "ORDER BY p.created_at DESC";
            case 'rating':
                return "ORDER BY p.rating DESC, p.review_count DESC";
            case 'popular':
                return "ORDER BY p.views DESC";
            case 'name_asc':
                return "ORDER BY p.name ASC";
            case 'name_desc':
                return "ORDER BY p.name DESC";
            case 'relevance':
            default:
                return "ORDER BY relevance_score DESC, p.rating DESC";
        }
    }

    private function getSearchSuggestions($query, $limit = 10) {
        // Product name suggestions
        $productQuery = "SELECT DISTINCT name as suggestion, 'product' as type 
                        FROM products 
                        WHERE name LIKE :query AND status = 'active'
                        LIMIT :limit";
        
        $stmt = $this->db->prepare($productQuery);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Category suggestions
        $categoryQuery = "SELECT DISTINCT name as suggestion, 'category' as type 
                         FROM categories 
                         WHERE name LIKE :query AND status = 'active'
                         LIMIT 3";
        
        $stmt = $this->db->prepare($categoryQuery);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->execute();
        
        $categorySuggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_merge($suggestions, $categorySuggestions);
    }

    private function saveSearchQuery($query) {
        // Save to search analytics table
        $saveQuery = "INSERT INTO search_analytics (query, search_count, created_at) 
                     VALUES (:query, 1, NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     search_count = search_count + 1, 
                     updated_at = NOW()";
        
        $stmt = $this->db->prepare($saveQuery);
        $stmt->bindParam(':query', $query);
        $stmt->execute();
    }

    private function getPopularSearches($limit = 10) {
        $query = "SELECT query, search_count 
                 FROM search_analytics 
                 ORDER BY search_count DESC 
                 LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>