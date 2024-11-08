<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kết nối cơ sở dữ liệu
include("connect.inp");

if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Lấy dữ liệu từ biểu mẫu
$hoten = isset($_POST['hoten']) ? $_POST['hoten'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$matkhau = isset($_POST['matkhau']) ? $_POST['matkhau'] : '';
$xacnhanmatkhau = isset($_POST['xacnhanmatkhau']) ? $_POST['xacnhanmatkhau'] : '';

if (!empty($hoten) && !empty($email) && !empty($matkhau) && !empty($xacnhanmatkhau)) {
    if ($matkhau !== $xacnhanmatkhau) {
        die("Mật khẩu xác nhận không khớp!");
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($matkhau, PASSWORD_BCRYPT);

    // Chuẩn bị câu lệnh SQL để chèn dữ liệu
    $stmt = $conn->prepare("INSERT INTO user (hoten, email, matkhau, trangthai) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $hoten, $email, $hashed_password);

    if ($stmt->execute()) {
        sendConfirmationEmail($email, $hoten);
    } else {
        echo "Lỗi: " . $stmt->error;
    }
} else {
    echo "Lỗi: Các trường không được để trống.";
}

// Hàm gửi email xác nhận
function sendConfirmationEmail($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->CharSet = 'UTF-8';
        $mail->Username = 'dohongba03@gmail.com';
        $mail->Password = 'enjw uhjb hzeu frkf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Thông tin người gửi và người nhận
        $mail->setFrom('dohongba03@gmail.com', 'Tên của bạn');
        $mail->addAddress($recipientEmail, $recipientName);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đăng ký tài khoản';
        $mail->Body = getHtmlContent($recipientName);
        $mail->AltBody = 'Cảm ơn bạn đã đăng ký.';

        $mail->send();
        echo 'Email xác nhận đã được gửi thành công.';
    } catch (Exception $e) {
        echo "Gửi email thất bại. Lỗi: {$mail->ErrorInfo}";
    }
}

// Hàm tạo nội dung email HTML
function getHtmlContent($hoten) {
    return "
    <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Fin Flow - Hoàn tất Thiết lập Tài khoản</title>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f0f8ff;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    padding: 20px;
                    border-radius: 8px;
                    text-align: center;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }
                .logo img {
                    width: 80px;
                    margin-bottom: 20px;
                }
                .header {
                    font-size: 26px;
                    font-weight: bold;
                    color: #004aad;
                    margin-bottom: 10px;
                }
                .subheader {
                    color: #6b7c93;
                    font-size: 16px;
                    line-height: 1.5;
                    margin-top: 10px;
                    margin-bottom: 20px;
                }
                .button {
                    display: inline-block;
                    margin-top: 15px;
                    padding: 12px 24px;
                    font-size: 16px;
                    color: #ffffff;
                    background-color: #004aad;
                    border-radius: 5px;
                    text-decoration: none;
                }
                .section-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #333333;
                    margin: 10px 0;
                }
                .footer {
                    font-size: 12px;
                    color: #999999;
                    margin-top: 20px;
                }
                .footer a {
                    color: #004aad;
                    text-decoration: none;
                    margin: 0 5px;
                }
                .box {
                    background-color: #f4f8fc;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                    display: flex;
                    align-items: center;
                    text-align: left;
                }
                .box-icon {
                    font-size: 28px;
                    color: #004aad;
                    margin-right: 15px;
                }
                .box-content {
                    flex: 1;
                }
                .box-content p {
                    color: #333333;
                    font-size: 14px;
                    margin: 5px 0;
                }
                .box-content .button {
                    margin-top: 10px;
                    padding: 10px 20px;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>
                    <img src='https://downloadlogomienphi.com/sites/default/files/logos/download-logo-vector-sapo-mien-phi.jpg' alt='Sapo Logo'>
                </div>
                <div class='header'>
                    Chào mừng!<br>Hoàn tất Thiết lập Tài khoản của bạn
                </div>
                <p class='subheader'>
                    Chào $hoten,<br>
                    Chào mừng bạn đến với Sapo! Để tận dụng tối đa tài khoản của bạn, vui lòng dành ít phút để hoàn tất thiết lập. Điều này sẽ đảm bảo rằng bạn có quyền truy cập đầy đủ vào tất cả các tính năng và dịch vụ của chúng tôi.
                </p>
                <!-- Xác minh Email -->
                <div class='box'>
                    <i class='fas fa-envelope box-icon'></i>
                    <div class='box-content'>
                        <h2 class='section-title'>Xác minh Email của bạn</h2>
                        <p>Xác nhận địa chỉ email của bạn bằng cách nhấp vào liên kết bên dưới.</p>
                        <a href='#' class='button'>Xác minh email của tôi</a>
                    </div>
                </div>
                <!-- Footer -->
                <div class='footer'>
                    Fin Flow
                    <br>
                    <a href='#'>Chính sách bảo mật</a> | <a href='#'>Liên hệ chúng tôi</a> | <a href='#'>Hủy đăng ký</a>
                </div>
            </div>
        </body>
    </html>
    ";
}
?>
