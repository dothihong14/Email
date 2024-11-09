<?php
session_start();

$message = '';
$type = ''; // 'success' hoặc 'error'
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $error_keywords = ['khớp', 'không', 'Lỗi', 'thất bại', 'sử dụng email khác'];
    $type = 'success';
    foreach ($error_keywords as $word) {
        if (stripos($message, $word) !== false) {
            $type = 'error';
            break;
        }
    }
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #FFFAFA;
        }
        .signup-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 320px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
        }
        .signup-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .signup-container input[type="text"],
        .signup-container input[type="email"],
        .password-field input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .error-message {
            color: red;
            font-size: 12px;
            text-align: left;
            width: 90%;
            margin: 0 auto;
            margin-bottom: 10px;
        }
        .password-field {
            position: relative;
            margin: 3px 0;
            width: 100%;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 20px;
            height: 20px;
        }
        .signup-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #5a67d8;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.5;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .signup-container input[type="submit"]:enabled {
            opacity: 1;
            pointer-events: all;
        }
        .resend-email {
            margin-top: 15px;
            font-size: 14px;
        }
        .resend-email a {
            color: #5a67d8;
            text-decoration: none;
        }
        .resend-email a:hover {
            text-decoration: underline;
        }

        .notification-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px;
            max-width: 300px;
            font-size: 13px;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }
        .notification-popup.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .notification-popup.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .notification-popup p {
            margin: 0;
        }
        .close-notification {
            float: right;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
    <script>
        function validateForm() {
            const hoten = document.forms["registrationForm"]["hoten"].value;
            const email = document.forms["registrationForm"]["email"].value;
            const matkhau = document.forms["registrationForm"]["matkhau"].value;
            const xacnhanmatkhau = document.forms["registrationForm"]["xacnhanmatkhau"].value;

            let isValid = true;

            const hotenError = document.getElementById("hoten-error");
            if (!hoten) {
                hotenError.textContent = "Hãy nhập họ tên của bạn";
                isValid = false;
            } else {
                hotenError.textContent = "";
            }

            const emailError = document.getElementById("email-error");
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                emailError.textContent = "Hãy nhập email của bạn";
                isValid = false;
            } else if (!emailPattern.test(email)) {
                emailError.textContent = "Email không đúng định dạng";
                isValid = false;
            } else {
                emailError.textContent = "";
            }

            const matkhauError = document.getElementById("matkhau-error");
            const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
            if (!passwordPattern.test(matkhau)) {
                matkhauError.textContent = "Mật khẩu ít nhất 8 ký tự bao gồm chữ hoa, số, ký tự đặc biệt";
                isValid = false;
            } else {
                matkhauError.textContent = "";
            }

            const xacnhanmatkhauError = document.getElementById("xacnhanmatkhau-error");
            if (matkhau !== xacnhanmatkhau) {
                xacnhanmatkhauError.textContent = "Mật khẩu không trùng khớp";
                isValid = false;
            } else {
                xacnhanmatkhauError.textContent = "";
            }

            document.getElementById("submit-btn").disabled = !isValid;
        }

        function showNotification(message, type) {
            const notification = document.getElementById("notification-popup");
            const notificationMessage = document.getElementById("notification-message");
            notificationMessage.textContent = message;
            notification.className = 'notification-popup ' + type;
            notification.style.display = "block";
        }

        window.onload = function() {
            <?php if (!empty($message)): ?>
                showNotification("<?php echo addslashes($message); ?>", "<?php echo $type; ?>");
            <?php endif; ?>
        };

        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);

            if (field.type === "password") {
                field.type = "text";
                icon.src = "image/hiện.jpg";
            } else {
                field.type = "password";
                icon.src = "image/ẩn.jpg";
            }
        }
    </script>
</head>
<body>
    <div class="signup-container">
        <h2>Đăng ký tài khoản</h2>
        <form name="registrationForm" method="POST" action="xlsignin.php" oninput="validateForm()">
            <input type="text" name="hoten" placeholder="Họ tên" required><br>
            <div id="hoten-error" class="error-message"></div>

            <input type="email" name="email" placeholder="Email" required><br>
            <div id="email-error" class="error-message"></div>
            
            <div class="password-field">
                <input type="password" id="matkhau" name="matkhau" placeholder="Mật khẩu">
                <img src="image/hiện.jpg" alt="Toggle Password" id="matkhau-icon" class="password-toggle" onclick="togglePassword('matkhau', 'matkhau-icon')">
            </div>
            <div id="matkhau-error" class="error-message"></div>

            <div class="password-field">
                <input type="password" id="xacnhanmatkhau" name="xacnhanmatkhau" placeholder="Xác nhận mật khẩu">
                <img src="image/hiện.jpg" alt="Toggle Password" id="xacnhanmatkhau-icon" class="password-toggle" onclick="togglePassword('xacnhanmatkhau', 'xacnhanmatkhau-icon')">
            </div>
            <div id="xacnhanmatkhau-error" class="error-message"></div>

            <input type="submit" value="Đăng ký" id="submit-btn" disabled>
        </form>
        <div class="resend-email">
            Bạn chưa nhận được email xác nhận? <a href="resend.php">Gửi lại</a>
        </div>
    </div>

    <div id="notification-popup" class="notification-popup">
        <span class="close-notification" onclick="document.getElementById('notification-popup').style.display='none'">&times;</span>
        <p id="notification-message"></p>
    </div>
</body>
</html>
