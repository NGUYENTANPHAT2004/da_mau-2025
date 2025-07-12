<?php
class Payment {
    private $conn;
    private $table_name = "payments";

    // Cấu hình VNPay
    private $vnp_TmnCode = "YOUR_TMN_CODE"; // Mã website của bạn tại VNPay
    private $vnp_HashSecret = "YOUR_HASH_SECRET"; // Chuỗi bí mật
    private $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán sandbox
    private $vnp_ReturnUrl; // URL trả về

    public function __construct($db) {
        $this->conn = $db;
        $this->vnp_ReturnUrl = BASE_URL . "?action=payment_return";
    }

    public function createVNPayURL($orderId, $amount, $orderInfo) {
        $vnp_TxnRef = $orderId . '_' . time();
        $vnp_Amount = $amount * 100; // VNPay yêu cầu số tiền * 100
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $orderInfo,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Lưu thông tin thanh toán
        $this->savePaymentRequest($vnp_TxnRef, $orderId, $amount, 'vnpay', 'pending');

        return $vnp_Url;
    }

    public function verifyVNPayReturn($inputData) {
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $hashData = rtrim($hashData, '&');
        
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);
        
        if ($secureHash === $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00') {
                // Thanh toán thành công
                $this->updatePaymentStatus($inputData['vnp_TxnRef'], 'completed');
                return true;
            } else {
                // Thanh toán thất bại
                $this->updatePaymentStatus($inputData['vnp_TxnRef'], 'failed');
                return false;
            }
        }
        return false;
    }

    public function savePaymentRequest($txnRef, $orderId, $amount, $method, $status) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (transaction_ref, order_id, amount, payment_method, status, created_at) 
                  VALUES (:txn_ref, :order_id, :amount, :method, :status, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':txn_ref', $txnRef);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    public function updatePaymentStatus($txnRef, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = NOW() 
                  WHERE transaction_ref = :txn_ref";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':txn_ref', $txnRef);
        
        if ($stmt->execute() && $status === 'completed') {
            // Cập nhật trạng thái đơn hàng
            $orderId = explode('_', $txnRef)[0];
            $this->updateOrderStatus($orderId, 'paid');
        }
        
        return $stmt->execute();
    }

    private function updateOrderStatus($orderId, $status) {
        $query = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);
        return $stmt->execute();
    }

    public function getPaymentHistory($orderId = null) {
        $query = "SELECT p.*, o.customer_name 
                  FROM " . $this->table_name . " p 
                  JOIN orders o ON p.order_id = o.id";
        
        if ($orderId) {
            $query .= " WHERE p.order_id = :order_id";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($orderId) {
            $stmt->bindParam(':order_id', $orderId);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>