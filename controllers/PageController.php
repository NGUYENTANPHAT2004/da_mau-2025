<?php
class PageController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function contact() {
        $success = '';
        $error = '';
        
        if($_POST) {
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $subject = sanitizeInput($_POST['subject']);
            $message = sanitizeInput($_POST['message']);
            
            // Validate
            if(!$name || !$email || !$subject || !$message) {
                $error = 'Vui lòng điền đầy đủ thông tin';
            } elseif(!isValidEmail($email)) {
                $error = 'Email không hợp lệ';
            } else {
                // Save contact message
                $query = "INSERT INTO contact_messages (name, email, subject, message, created_at) 
                         VALUES (:name, :email, :subject, :message, NOW())";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':subject', $subject);
                $stmt->bindParam(':message', $message);
                
                if($stmt->execute()) {
                    $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.';
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại!';
                }
            }
        }
        
        include 'views/layouts/header.php';
        include 'views/pages/contact.php';
        include 'views/layouts/footer.php';
    }

    public function about() {
        include 'views/layouts/header.php';
        include 'views/pages/about.php';
        include 'views/layouts/footer.php';
    }

    public function findPageBySlug($slug) {
        $query = "SELECT * FROM pages WHERE slug = :slug AND status = 'published'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function showPage($slug) {
        $query = "SELECT * FROM pages WHERE slug = :slug AND status = 'published'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
        
        include 'views/layouts/header.php';
        include 'views/pages/dynamic.php';
        include 'views/layouts/footer.php';
    }
}
?>