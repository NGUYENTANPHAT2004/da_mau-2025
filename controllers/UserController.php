<?php
class UserController {
    private $db;
    private $user;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->order = new Order($this->db);
    }

    public function login() {
        if(isLoggedIn()) {
            redirect('home');
            return;
        }

        $error = '';
        if($_POST) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            if($this->user->login($email, $password)) {
                if(isAdmin()) {
                    redirect('admin');
                } else {
                    redirect('home');
                }
                return;
            } else {
                $error = "Email hoặc mật khẩu không đúng!";
            }
        }
        
        include 'views/layouts/header.php';
        include 'views/user/login.php';
        include 'views/layouts/footer.php';
    }

    public function register() {
        if(isLoggedIn()) {
            redirect('home');
            return;
        }

        $error = '';
        $success = '';
        
        if($_POST) {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $phone = $_POST['phone'] ?? '';
            
            // Validate
            if($password !== $confirm_password) {
                $error = "Mật khẩu xác nhận không khớp!";
            } elseif(strlen($password) < 6) {
                $error = "Mật khẩu phải có ít nhất 6 ký tự!";
            } elseif($this->user->register($fullname, $email, $password, $phone)) {
                $success = "Đăng ký thành công! Vui lòng đăng nhập.";
            } else {
                $error = "Email đã được sử dụng hoặc có lỗi xảy ra!";
            }
        }
        
        include 'views/layouts/header.php';
        include 'views/user/register.php';
        include 'views/layouts/footer.php';
    }

    public function profile() {
        if(!isLoggedIn()) {
            redirect('login');
            return;
        }

        $user_info = $this->user->getUserById($_SESSION['user_id']);
        $user_orders = $this->order->getUserOrders($_SESSION['user_id']);
        
        $success = '';
        $error = '';
        
        if($_POST) {
            $data = [
                'fullname' => $_POST['fullname'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address']
            ];
            
            if($this->user->updateProfile($_SESSION['user_id'], $data)) {
                $success = "Cập nhật thông tin thành công!";
                $_SESSION['user_name'] = $data['fullname'];
                $user_info = $this->user->getUserById($_SESSION['user_id']);
            } else {
                $error = "Có lỗi xảy ra khi cập nhật!";
            }
        }
        
        include 'views/layouts/header.php';
        include 'views/user/profile.php';
        include 'views/layouts/footer.php';
    }

    public function changePassword() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate
            if($new_password !== $confirm_password) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu mới không khớp']);
                return;
            }
            
            if(strlen($new_password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
                return;
            }
            
            if($this->user->changePassword($_SESSION['user_id'], $current_password, $new_password)) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
            }
        }
    }

    public function orders() {
        if(!isLoggedIn()) {
            redirect('login');
            return;
        }

        $orders = $this->order->getUserOrders($_SESSION['user_id']);
        
        include 'views/layouts/header.php';
        include 'views/user/orders.php';
        include 'views/layouts/footer.php';
    }

    public function orderDetail($order_id) {
        if(!isLoggedIn()) {
            redirect('login');
            return;
        }

        $order = $this->order->getById($order_id);
        
        // Kiểm tra quyền xem đơn hàng
        if(!$order || $order['user_id'] != $_SESSION['user_id']) {
            redirect('orders');
            return;
        }
        
        $order_items = $this->order->getOrderItems($order_id);
        
        include 'views/layouts/header.php';
        include 'views/user/order_detail.php';
        include 'views/layouts/footer.php';
    }

    public function logout() {
        session_destroy();
        redirect('home');
    }
}
?>