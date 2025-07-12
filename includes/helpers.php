<?php
/**
 * Helper functions cho hệ thống ecommerce
 */

/**
 * Lấy badge class cho trạng thái đơn hàng
 */
function getStatusBadge($status) {
    switch($status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

/**
 * Lấy text hiển thị cho trạng thái đơn hàng
 */
function getStatusText($status) {
    switch($status) {
        case 'pending': return 'Chờ xử lý';
        case 'processing': return 'Đang xử lý';
        case 'shipped': return 'Đã gửi hàng';
        case 'delivered': return 'Đã giao hàng';
        case 'cancelled': return 'Đã hủy';
        default: return 'Không xác định';
    }
}

/**
 * Format giá tiền theo định dạng Việt Nam
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

/**
 * Tính phần trăm giảm giá
 */
function calculateDiscount($original_price, $sale_price) {
    if($original_price <= 0) return 0;
    return round((($original_price - $sale_price) / $original_price) * 100);
}

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra user có phải admin không
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

/**
 * Redirect đến trang khác
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Tạo slug từ text
 */
function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Cắt text với độ dài cho trước
 */
function truncateText($text, $length = 100) {
    if(strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Lấy thời gian tương đối (ví dụ: 2 giờ trước)
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if($time < 60) {
        return 'Vừa xong';
    } elseif($time < 3600) {
        return floor($time / 60) . ' phút trước';
    } elseif($time < 86400) {
        return floor($time / 3600) . ' giờ trước';
    } elseif($time < 2592000) {
        return floor($time / 86400) . ' ngày trước';
    } elseif($time < 31536000) {
        return floor($time / 2592000) . ' tháng trước';
    } else {
        return floor($time / 31536000) . ' năm trước';
    }
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Upload file
 */
function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if(!isset($file['error']) || $file['error'] !== 0) {
        return false;
    }
    
    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if(!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    $newFileName = time() . '_' . generateRandomString(5) . '.' . $fileExtension;
    $uploadPath = $destination . '/' . $newFileName;
    
    if(!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }
    
    if(move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $newFileName;
    }
    
    return false;
}
?> 