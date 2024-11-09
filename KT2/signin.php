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
    // Tạo liên kết xác nhận với địa chỉ thực tế
    $confirmationLink = "https://yourdomain.com/confirm.php?email=" . urlencode($email);

    // Nội dung email HTML mới
    return "
    <!DOCTYPE html>
    <html lang='vi'>
    <head>
      <meta charset='UTF-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
      <title>Xác nhận đăng ký tài khoản - Áo dài Tủ Nhà Mây</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          background-color: #F3EDEB;
          margin: 0;
          padding: 0;
          color: #4A4A4A;
          display: flex;
          justify-content: center;
          align-items: center;
        }
        .container {
          width: 100%;
          max-width: 600px;
          background-color: #ffffff;
          border-radius: 10px;
          box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
          overflow: hidden;
        }
        .header {
          background-color: rgba(224, 142, 94, 0.1);
          padding: 20px;
          text-align: center;
          border-top-left-radius: 10px;
          border-top-right-radius: 10px;
          margin: 0;
        }
        .logo img {
          width: 90px;
          height: 90px;
          border-radius: 50%;
          border: 2px solid #A07E6A;
        }
        .content {
          padding: 30px 20px;
          text-align: center;
          line-height: 1.8;
          color: #333;
        }
        .content h1 {
          color: #A07E6A;
          font-size: 24px;
          margin-bottom: 10px;
        }
        .content h2 {
          color: #A07E6A;
          font-size: 22px;
          margin-bottom: 8px;
        }
        .content p {
          font-size: 16px;
          margin: 12px 0;
          color: #555;
        }
        .button {
          display: inline-block;
          background-color: #A07E6A;
          color: #ffffff;
          padding: 14px 28px;
          border-radius: 6px;
          text-decoration: none;
          font-weight: bold;
          font-size: 16px;
          transition: background-color 0.3s ease, transform 0.3s ease;
          margin: 30px 0;
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .button:hover {
          background-color: #8D6D5B;
          transform: scale(1.05);
        }
        .footer {
          background-color: rgba(224, 142, 94, 0.1);
          padding: 20px;
          text-align: center;
          border-bottom-left-radius: 10px;
          border-bottom-right-radius: 10px;
          font-size: 14px;
          color: #666;
          margin: 0;
        }
        .footer p {
          font-size: 16px;
          color: #333;
          font-weight: normal;
        }
        .footer-links {
          margin-top: 15px;
          display: flex;
          justify-content: center;
          gap: 20px;
          flex-wrap: wrap;
        }
        .footer-links a {
          text-decoration: none;
          color: #333;
          display: flex;
          align-items: center;
          gap: 5px;
          font-weight: 500;
        }
        .footer-links img {
          width: 20px;
          height: 20px;
          vertical-align: middle;
        }
      </style>
    </head>
    <body>
      <div class='container'>
        <!-- Header -->
        <div class='header'>
          <div class='logo'>
            <img src='https://scontent.fhan14-1.fna.fbcdn.net/v/t39.30808-6/465940572_3349274525368692_7354616264747658971_n.jpg?_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeEU3vsJvsMHvnCqQhLkTHUmNjCpLXqON1o2MKkteo43Wr85d5XKaIeePb5riGgmINdhK9RM5pzCJwRxHR2SzYCt&_nc_ohc=fdiVCJeaZUsQ7kNvgELEoFo&_nc_zt=23&_nc_ht=scontent.fhan14-1.fna&_nc_gid=A8mnCHaPVF0e09iuDRw3p6x&oh=00_AYCo7hWRRSytpzk0iDm-NuKUlN1bFdCAwTN8tzLJ1-LWBw&oe=6734C307' alt='Tủ Nhà Mây Logo'>
          </div>
        </div>

        <!-- Nội dung email -->
        <div class='content'>
          <h1>Xác nhận đăng ký tài khoản!</h1>
          <h2>Chào mừng bạn đến với Áo dài Tủ Nhà Mây!</h2>
          <p>Xin chào $hoten,</p>
          <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Áo dài Tủ Nhà Mây</strong>. Để hoàn tất đăng ký, vui lòng xác nhận tài khoản của bạn bằng cách nhấn vào nút dưới đây:</p>
          <a href='$confirmationLink' class='button'>Xác nhận tài khoản</a>
          <p style='margin-top: 20px;'>Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email này.</p>
        </div>

        <!-- Footer -->
        <div class='footer'>
          <p>Kết nối với chúng tôi:</p>
          <div class='footer-links'>
            <a href='https://www.tunhamay.vn/' target='_blank'>
              <img src='https://cdn-icons-png.flaticon.com/512/25/25694.png' alt='Website Icon'>
              Website
            </a>
            <a href='tel:0363427583'>
              <img src='https://cdn-icons-png.flaticon.com/512/724/724664.png' alt='Hotline Icon'>
              Hotline: 0363427583
            </a>
            <a href='https://www.facebook.com/TuNhaMay' target='_blank'>
              <img src='https://cdn-icons-png.flaticon.com/512/124/124010.png' alt='Facebook Icon'>
              Tủ Nhà Mây
            </a>
            <a href='https://www.instagram.com/tunhamay.official/' target='_blank'>
              <img src='https://cdn-icons-png.flaticon.com/512/2111/2111463.png' alt='Instagram Logo'>
              Tủ Nhà Mây
            </a>
            <a href='mailto:Tunhamayofficial@gmail.com'>
              <img src='https://cdn-icons-png.flaticon.com/512/732/732200.png' alt='Email Icon'>
              Tunhamayofficial@gmail.com
            </a>
            <a href='https://goo.gl/maps/YOUR_MAP_LINK' target='_blank'>
              <img src='https://cdn-icons-png.flaticon.com/512/684/684908.png' alt='Location Icon'>
              137 Tôn Đức Thắng, Hà Nội
            </a>
          </div>
        </div>
      </div>
    </body>
    </html>";
}
?>
