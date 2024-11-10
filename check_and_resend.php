<?php
// Bắt đầu session
session_start();

// Kết nối cơ sở dữ liệu
include("connect.inp");
// Hàm gửi email xác nhận // chưa check lại chỗ này
function sendConfirmationEmail($email) {
    // Gọi hàm gửi email (sử dụng mail(), PHPMailer, v.v.)
    $subject = "Xác nhận tài khoản của bạn";
    $message = "Vui lòng xác nhận tài khoản của bạn bằng cách mở email này.";
    $headers = "From: no-reply@yourdomain.com";
    
    mail($email, $subject, $message, $headers);
}
// Kiểm tra các email chưa mở sau 7 ngày và gửi lại
$today = new DateTime();
$query = "SELECT * FROM user WHERE trangthai = 1 AND trangthaimomail = 'chưa mở'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($user = mysqli_fetch_assoc($result)) {
    $sentDate = new DateTime($user['ngaygui']);
    $interval = $today->diff($sentDate)->days;
    
    if ($interval >= 7) {
        // Gửi lại email xác nhận
        sendConfirmationEmail($user['email']);
        
        // Cập nhật lại ngày gửi mail lần đầu và trạng thái email đã mở
        $stmtUpdate = mysqli_prepare($conn, "UPDATE user SET ngaygui = ?, trangthaimomail = 'chưa mở' WHERE email = ?");
        $newSentDate = $today->format('Y-m-d H:i:s');
        mysqli_stmt_bind_param($stmtUpdate, 'ss', $newSentDate, $user['email']);
        mysqli_stmt_execute($stmtUpdate);
        
        // Thông báo rằng email đã được gửi lại
        $_SESSION['message'] = "Email xác nhận đã được gửi lại cho: " . $user['email'];
        header("Location: signin.php"); // Chuyển hướng đến trang đăng nhập 
        exit();
    }
}
?>
