<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkEmailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register($fullname, $email, $password, $phone = '') {
        // Kiểm tra email đã tồn tại
        if ($this->checkEmailExists($email)) {
            // Email đã tồn tại, trả về false
            return 'exists';
        }
        $query = "INSERT INTO " . $this->table_name . " 
                  (fullname, email, password, phone, role, created_at) 
                  VALUES (:fullname, :email, :password, :phone, 'customer', NOW())";
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':phone', $phone);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function login($email, $password) {
        $query = "SELECT id, fullname, email, password, role FROM " . $this->table_name . " 
                  WHERE email = :email AND status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                return true;
            }
        }
        return false;
    }

    public function logout() {
        session_destroy();
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($user_id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET fullname = :fullname, phone = :phone, address = :address, updated_at = NOW() 
                  WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        
        return $stmt->execute();
    }

    public function changePassword($user_id, $current_password, $new_password) {
        // Kiểm tra mật khẩu hiện tại
        $query = "SELECT password FROM " . $this->table_name . " WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$user || !password_verify($current_password, $user['password'])) {
            return false;
        }
        
        // Cập nhật mật khẩu mới
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password, updated_at = NOW() 
                  WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':password', $password_hash);
        
        return $stmt->execute();
    }
}
?>