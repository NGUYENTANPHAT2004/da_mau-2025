<?php
/**
 * Fix Product Model để khớp với database hiện tại
 * Thay thế file models/Product.php bằng code này
 */
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 12, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name,
                         COALESCE(pi.image, p.image) as main_image
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  WHERE p.status = 'active' 
                  ORDER BY p.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id AND p.status != 'inactive'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategory($category_id, $limit = 12) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = :category_id AND p.status = 'active' 
                  ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($keyword, $limit = 12) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE (p.name LIKE :keyword OR p.description LIKE :keyword) 
                  AND p.status = 'active' 
                  ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$keyword}%";
        $stmt->bindParam(':keyword', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // Generate slug if not provided
        $slug = isset($data['slug']) ? $data['slug'] : $this->generateSlug($data['name']);
        
        // Generate SKU if not provided
        $sku = isset($data['sku']) ? $data['sku'] : $this->generateSKU($data['name']);
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, slug, description, short_description, price, sale_price, sku, quantity, category_id, 
                   brand, weight, dimensions, status, featured, image, meta_title, meta_description, created_at) 
                  VALUES 
                  (:name, :slug, :description, :short_description, :price, :sale_price, :sku, :quantity, :category_id,
                   :brand, :weight, :dimensions, 'active', :featured, :image, :meta_title, :meta_description, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Set defaults
        $featured = isset($data['featured']) ? (int)$data['featured'] : 0;
        $short_description = isset($data['short_description']) ? $data['short_description'] : substr($data['description'] ?? '', 0, 200);
        $brand = isset($data['brand']) ? $data['brand'] : null;
        $weight = isset($data['weight']) ? $data['weight'] : null;
        $dimensions = isset($data['dimensions']) ? $data['dimensions'] : null;
        $image = isset($data['image']) ? $data['image'] : null;
        $meta_title = isset($data['meta_title']) ? $data['meta_title'] : $data['name'];
        $meta_description = isset($data['meta_description']) ? $data['meta_description'] : $short_description;
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':short_description', $short_description);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':sale_price', $data['sale_price']);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':brand', $brand);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':dimensions', $dimensions);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':meta_title', $meta_title);
        $stmt->bindParam(':meta_description', $meta_description);
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        // Generate slug if not provided
        $slug = isset($data['slug']) ? $data['slug'] : $this->generateSlug($data['name']);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, slug = :slug, description = :description, short_description = :short_description,
                      price = :price, sale_price = :sale_price, sku = :sku, quantity = :quantity, 
                      category_id = :category_id, brand = :brand, weight = :weight, dimensions = :dimensions,
                      featured = :featured, image = :image, meta_title = :meta_title, 
                      meta_description = :meta_description, updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Set defaults
        $featured = isset($data['featured']) ? (int)$data['featured'] : 0;
        $short_description = isset($data['short_description']) ? $data['short_description'] : substr($data['description'] ?? '', 0, 200);
        $brand = isset($data['brand']) ? $data['brand'] : null;
        $weight = isset($data['weight']) ? $data['weight'] : null;
        $dimensions = isset($data['dimensions']) ? $data['dimensions'] : null;
        $sku = isset($data['sku']) ? $data['sku'] : $this->generateSKU($data['name']);
        $image = isset($data['image']) ? $data['image'] : null;
        $meta_title = isset($data['meta_title']) ? $data['meta_title'] : $data['name'];
        $meta_description = isset($data['meta_description']) ? $data['meta_description'] : $short_description;
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':short_description', $short_description);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':sale_price', $data['sale_price']);
        $stmt->bindParam(':sku', $sku);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':brand', $brand);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':dimensions', $dimensions);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':meta_title', $meta_title);
        $stmt->bindParam(':meta_description', $meta_description);
        
        return $stmt->execute();
    }

    public function delete($id) {
        // Soft delete
        $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function getProductImages($product_id) {
        $query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order ASC, is_primary DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductAttributes($product_id) {
        $query = "SELECT * FROM product_attributes WHERE product_id = :product_id ORDER BY attribute_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function generateSlug($text) {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Vietnamese to ASCII conversion
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        
        $ascii = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        $text = str_replace($vietnamese, $ascii, $text);
        
        // Remove special characters
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        
        // Replace spaces and multiple dashes with single dash
        $text = preg_replace('/[\s-]+/', '-', $text);
        
        // Remove leading/trailing dashes
        return trim($text, '-');
    }

    private function generateSKU($name) {
        // Extract first letters and add random number
        $words = explode(' ', $name);
        $sku = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $sku .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Add random number
        $sku .= rand(100, 999);
        
        // Check if SKU exists, if yes, regenerate
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE sku = :sku";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sku', $sku);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // If SKU exists, add timestamp
            $sku .= date('His');
        }
        
        return $sku;
    }

    // Additional methods for featured products, best sellers, etc.
    public function getFeaturedProducts($limit = 8) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active' AND p.featured = 1 
                  ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBestSellers($limit = 8) {
        $query = "SELECT p.*, c.name as category_name, 
                         COALESCE(SUM(oi.quantity), 0) as total_sold
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN order_items oi ON p.id = oi.product_id
                  WHERE p.status = 'active' 
                  GROUP BY p.id
                  ORDER BY total_sold DESC, p.created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelatedProducts($product_id, $category_id, $limit = 4) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active' AND p.category_id = :category_id AND p.id != :product_id
                  ORDER BY RAND() LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>