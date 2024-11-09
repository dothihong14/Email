<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Bắt đầu phiên làm việc để sử dụng biến session
session_start();

// Kết nối cơ sở dữ liệu
include("connect.inp");

if (!$conn) {
    $_SESSION['message'] = "Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error();
    header("Location: signin.php");
    exit();
}

// Lấy dữ liệu từ biểu mẫu
$hoten = isset($_POST['hoten']) ? trim($_POST['hoten']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$matkhau = isset($_POST['matkhau']) ? $_POST['matkhau'] : '';
$xacnhanmatkhau = isset($_POST['xacnhanmatkhau']) ? $_POST['xacnhanmatkhau'] : '';

if (!empty($hoten) && !empty($email) && !empty($matkhau) && !empty($xacnhanmatkhau)) {
    if ($matkhau !== $xacnhanmatkhau) {
        $_SESSION['message'] = "Mật khẩu xác nhận không khớp!";
        header("Location: signin.php");
        exit();
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($matkhau, PASSWORD_BCRYPT);

    // Kiểm tra xem email đã tồn tại hay chưa
    $check_query = "SELECT trangthai FROM user WHERE email = ?";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        $_SESSION['message'] = "Lỗi chuẩn bị câu lệnh: " . $conn->error;
        header("Location: signin.php");
        exit();
    }
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['trangthai'] == 0) {
            $_SESSION['message'] = "Email này đã được sử dụng, vui lòng sử dụng email khác.";
        } elseif ($row['trangthai'] == 1 && sendConfirmationEmail($email, $hoten)) {
            $_SESSION['message'] = "Email xác nhận đăng ký đã được gửi đến hộp thư của bạn, vui lòng kiểm tra!";
        }
        $check_stmt->close();
        $conn->close();
        header("Location: signin.php");
        exit();
    } else {
        // Chèn dữ liệu vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO user (hoten, email, matkhau, trangthai) VALUES (?, ?, ?, 1)");
        if (!$stmt) {
            $_SESSION['message'] = "Lỗi chuẩn bị câu lệnh chèn: " . $conn->error;
            header("Location: signin.php");
            exit();
        }
        $stmt->bind_param("sss", $hoten, $email, $hashed_password);
        if ($stmt->execute()) {
            // Gửi email xác nhận
            if (sendConfirmationEmail($email, $hoten)) {
                $_SESSION['message'] = "Email xác nhận đăng ký đã được gửi đến hộp thư của bạn, vui lòng kiểm tra!";
            } else {
                $_SESSION['message'] = "Gửi email xác nhận thất bại.";
            }
        } else {
            $_SESSION['message'] = "Lỗi khi thêm tài khoản: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        header("Location: signin.php");
        exit();
    }
} else {
    $_SESSION['message'] = "Lỗi: Các trường không được để trống.";
    header("Location: signin.php");
    exit();
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
        $mail->Username = 'dohongba03@gmail.com'; // Thay bằng email của bạn
        $mail->Password = 'enjw uhjb hzeu frkf'; // Thay bằng mật khẩu ứng dụng hoặc mật khẩu của bạn
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Thông tin người gửi và người nhận
        $mail->setFrom('dohongba03@gmail.com', 'Tủ nhà Mây'); // Thay đổi nếu cần
        $mail->addAddress($recipientEmail, $recipientName);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đăng ký tài khoản';
        $mail->Body = getHtmlContent($recipientName, $recipientEmail);
        $mail->AltBody = 'Cảm ơn bạn đã đăng ký.';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Ghi log lỗi hoặc thực hiện các bước khác nếu cần
        return false;
    }
}

// Hàm tạo nội dung email HTML
function getHtmlContent($hoten, $email) {
    // Bạn nên tạo một liên kết xác nhận thực tế tại đây
    // Ví dụ: https://yourdomain.com/confirm.php?email=...
    $confirmationLink = "https://yourdomain.com/confirm.php?email=" . urlencode($email);

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
                .footer a:hover {
                    text-decoration: underline;
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
                        <a href='$confirmationLink' class='button'>Xác minh email của tôi</a>
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
