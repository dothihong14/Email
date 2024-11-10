<?php
// Kết nối cơ sở dữ liệu
require 'connect.inp';

// Kiểm tra nếu có tham số 'email' và 'token' trong URL
if (isset($_GET['email']) && isset($_GET['token']) && !empty($_GET['token'])) {
    $email = urldecode($_GET['email']);
    $token = $_GET['token'];

    // Kiểm tra sự tồn tại của email và token trong cơ sở dữ liệu
    $sql = "SELECT trangthai FROM user WHERE email = ? AND token = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Nếu người dùng tồn tại, kiểm tra trạng thái
            $stmt->bind_result($trangthai);
            $stmt->fetch();

            if ($trangthai == 1) {
                // Nếu trạng thái là 1 (chưa xác nhận), cập nhật thành 0 (đã xác nhận)
                $updateSql = "UPDATE user SET trangthai = 0, token = NULL WHERE email = ?";
                if ($updateStmt = $conn->prepare($updateSql)) {
                    $updateStmt->bind_param("s", $email);
                    $updateStmt->execute();

                    if ($updateStmt->affected_rows > 0) {
                        echo "Đăng ký thành công! Bạn sẽ được chuyển đến trang đăng nhập.";
                        header("refresh:3;url=login.php"); // Chuyển hướng sau 3 giây
                    } else {
                        echo "Có lỗi xảy ra trong quá trình xác nhận tài khoản.";
                    }
                    $updateStmt->close();
                } else {
                    echo "Lỗi khi chuẩn bị câu truy vấn cập nhật.";
                }
            } else {
                echo "Tài khoản này đã được xác nhận trước đó hoặc không tồn tại.";
            }
        } else {
            echo "Liên kết xác nhận không hợp lệ hoặc đã hết hạn.";
        }
        $stmt->close();
    } else {
        echo "Lỗi khi chuẩn bị câu truy vấn kiểm tra.";
    }
} else {
    echo "Thông tin xác nhận không đầy đủ hoặc token bị thiếu.";
}

// Đóng kết nối
$conn->close();
?>
