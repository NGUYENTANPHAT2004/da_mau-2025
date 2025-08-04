<?php
// models/Notification.php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, type, title, message, data, created_at) 
                  VALUES (:user_id, :type, :title, :message, :data, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':data', $data['data']);
        
        return $stmt->execute();
    }

    public function getUserNotifications($user_id, $limit = 20, $unread_only = false) {
        $whereClause = "WHERE user_id = :user_id";
        if($unread_only) {
            $whereClause .= " AND read_at IS NULL";
        }
        
        $query = "SELECT * FROM " . $this->table_name . " 
                  {$whereClause}
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notification_id, $user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET read_at = NOW() 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET read_at = NOW() 
                  WHERE user_id = :user_id AND read_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND read_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function deleteNotification($notification_id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }

    // Notification types and templates
    public function sendOrderNotification($user_id, $order_data, $type) {
        $templates = [
            'order_placed' => [
                'title' => 'Đơn hàng đã được đặt',
                'message' => 'Đơn hàng #{order_number} đã được đặt thành công với tổng tiền {total_amount}',
                'icon' => 'shopping-cart'
            ],
            'order_confirmed' => [
                'title' => 'Đơn hàng được xác nhận',
                'message' => 'Đơn hàng #{order_number} đã được xác nhận và đang được chuẩn bị',
                'icon' => 'check-circle'
            ],
            'order_shipped' => [
                'title' => 'Đơn hàng đã được gửi',
                'message' => 'Đơn hàng #{order_number} đã được gửi đi. Dự kiến giao hàng trong 2-3 ngày',
                'icon' => 'truck'
            ],
            'order_delivered' => [
                'title' => 'Đơn hàng đã được giao',
                'message' => 'Đơn hàng #{order_number} đã được giao thành công. Cảm ơn bạn đã mua hàng!',
                'icon' => 'gift'
            ]
        ];

        if(!isset($templates[$type])) return false;

        $template = $templates[$type];
        
        // Replace placeholders
        $message = str_replace(
            ['{order_number}', '{total_amount}'],
            [$order_data['order_number'], number_format($order_data['total_amount']) . ' VNĐ'],
            $template['message']
        );

        $notification_data = [
            'user_id' => $user_id,
            'type' => $type,
            'title' => $template['title'],
            'message' => $message,
            'data' => json_encode([
                'order_id' => $order_data['id'],
                'order_number' => $order_data['order_number'],
                'icon' => $template['icon'],
                'action_url' => BASE_URL . 'order-detail/' . $order_data['id']
            ])
        ];

        return $this->create($notification_data);
    }

    public function sendProductNotification($user_id, $product_data, $type) {
        $templates = [
            'product_back_in_stock' => [
                'title' => 'Sản phẩm đã có hàng',
                'message' => 'Sản phẩm {product_name} đã có hàng trở lại. Đặt hàng ngay!',
                'icon' => 'box'
            ],
            'product_price_drop' => [
                'title' => 'Giá sản phẩm giảm',
                'message' => 'Sản phẩm {product_name} đã giảm giá xuống {new_price}',
                'icon' => 'tag'
            ]
        ];

        if(!isset($templates[$type])) return false;

        $template = $templates[$type];
        
        $message = str_replace(
            ['{product_name}', '{new_price}'],
            [$product_data['name'], number_format($product_data['price']) . ' VNĐ'],
            $template['message']
        );

        $notification_data = [
            'user_id' => $user_id,
            'type' => $type,
            'title' => $template['title'],
            'message' => $message,
            'data' => json_encode([
                'product_id' => $product_data['id'],
                'product_name' => $product_data['name'],
                'icon' => $template['icon'],
                'action_url' => BASE_URL . 'product-detail/' . $product_data['id']
            ])
        ];

        return $this->create($notification_data);
    }
}



?>