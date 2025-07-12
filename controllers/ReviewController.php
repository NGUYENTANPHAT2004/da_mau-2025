<?php
class ReviewController {
    private $db;
    private $review;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->review = new Review($this->db);
    }

    public function addReview() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
            return;
        }

        if($_POST) {
            $data = [
                'user_id' => $_SESSION['user_id'],
                'product_id' => $_POST['product_id'],
                'rating' => $_POST['rating'],
                'comment' => $_POST['comment']
            ];

            if($this->review->create($data)) {
                echo json_encode(['success' => true, 'message' => 'Đánh giá của bạn đã được gửi thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra hoặc bạn đã đánh giá sản phẩm này rồi!']);
            }
        }
    }

    public function updateReview() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            if($this->review->updateReview($_POST['review_id'], $_POST['rating'], $_POST['comment'])) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật đánh giá thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật!']);
            }
        }
    }

    public function deleteReview() {
        if(!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            return;
        }

        if($_POST) {
            if($this->review->deleteReview($_POST['review_id'], $_SESSION['user_id'])) {
                echo json_encode(['success' => true, 'message' => 'Xóa đánh giá thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa!']);
            }
        }
    }
}
?>