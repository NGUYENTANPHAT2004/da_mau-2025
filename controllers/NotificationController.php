<?php
// controllers/NotificationController.php
class NotificationController {
    private $db;
    private $notification;

    public function __construct() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->notification = new Notification($this->db);
    }

    public function getNotifications() {
        $limit = $_GET['limit'] ?? 20;
        $unread_only = isset($_GET['unread_only']);
        
        $notifications = $this->notification->getUserNotifications(
            $_SESSION['user_id'], 
            $limit, 
            $unread_only
        );
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $this->notification->getUnreadCount($_SESSION['user_id'])
        ]);
    }

    public function markAsRead() {
        if($_POST && isset($_POST['notification_id'])) {
            $result = $this->notification->markAsRead(
                $_POST['notification_id'], 
                $_SESSION['user_id']
            );
            
            echo json_encode([
                'success' => $result,
                'unread_count' => $this->notification->getUnreadCount($_SESSION['user_id'])
            ]);
        }
    }

    public function markAllAsRead() {
        $result = $this->notification->markAllAsRead($_SESSION['user_id']);
        
        echo json_encode([
            'success' => $result,
            'unread_count' => 0
        ]);
    }

    public function deleteNotification() {
        if($_POST && isset($_POST['notification_id'])) {
            $result = $this->notification->deleteNotification(
                $_POST['notification_id'], 
                $_SESSION['user_id']
            );
            
            echo json_encode(['success' => $result]);
        }
    }

    public function getUnreadCount() {
        $count = $this->notification->getUnreadCount($_SESSION['user_id']);
        echo json_encode(['count' => $count]);
    }
}


?>