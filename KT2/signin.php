<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
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
        }
        .signup-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .signup-container input[type="text"],
        .signup-container input[type="email"],
        .signup-container input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .password-field {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-field input {
            width: 100%;
            padding-right: 40px;
        }
        .password-toggle {
        position: absolute;
        right: 10px;
        cursor: pointer;
        width: 20px; /* Điều chỉnh kích thước theo ý muốn */
        height: 20px; /* Điều chỉnh kích thước theo ý muốn */
}
        .signup-container input[type="submit"] {
            width: 30%;
            padding: 10px;
            background-color: #5a67d8;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .signup-container input[type="submit"]:hover {
            background-color: #4c51bf;
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
    </style>
    <script>
        function validateForm() {
            const hoten = document.forms["registrationForm"]["hoten"].value;
            const email = document.forms["registrationForm"]["email"].value;
            const matkhau = document.forms["registrationForm"]["matkhau"].value;
            const xacnhanmatkhau = document.forms["registrationForm"]["xacnhanmatkhau"].value;

            if (!hoten || !email || !matkhau || !xacnhanmatkhau) {
                alert("Vui lòng điền đầy đủ thông tin!");
                return false;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Email không đúng định dạng!");
                return false;
            }

            const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
            if (!passwordPattern.test(matkhau)) {
                alert("Mật khẩu phải có ít nhất 8 ký tự, bao gồm ít nhất 1 chữ cái viết hoa, 1 chữ số và 1 ký tự đặc biệt.");
                return false;
            }

            if (matkhau !== xacnhanmatkhau) {
                alert("Mật khẩu xác nhận không khớp!");
                return false;
            }

            return true;
        }

        function resendConfirmationEmail() {
            alert("Email xác nhận đã được gửi lại.");
            // Gọi Ajax hoặc điều hướng tới mã PHP gửi lại email ở đây.
        }

        // Function to toggle password visibility
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);

            if (field.type === "password") {
                field.type = "text";
                icon.src = "image/hiện.jpg"; // Đổi sang icon mắt mở
            } else {
                field.type = "password";
                icon.src = "image/ẩn.jpg"; // Đổi sang icon mắt gạch chéo
            }
        }
    </script>
</head>
<body>
    <div class="signup-container">
        <h2>Đăng ký tài khoản</h2>
        <form name="registrationForm" method="POST" action="xlsignin.php" onsubmit="return validateForm();">
            <input type="text" name="hoten" placeholder="Họ tên"><br>
            <input type="email" name="email" placeholder="Email"><br>

            <div class="password-field">
                <input type="password" id="matkhau" name="matkhau" placeholder="Mật khẩu">
                <img src="image/hiện.jpg" alt="Toggle Password" id="matkhau-icon" class="password-toggle" onclick="togglePassword('matkhau', 'matkhau-icon')">
            </div>

            <div class="password-field">
                <input type="password" id="xacnhanmatkhau" name="xacnhanmatkhau" placeholder="Xác nhận mật khẩu">
                <img src="image/ẩn.jpg" alt="Toggle Password" id="xacnhanmatkhau-icon" class="password-toggle" onclick="togglePassword('xacnhanmatkhau', 'xacnhanmatkhau-icon')">
            </div>

            <input type="submit" value="Đăng ký">
        </form>
        <div class="resend-email">
            <a href="javascript:void(0);" onclick="resendConfirmationEmail()">Gửi lại email xác nhận</a>
        </div>
    </div>
</body>
</html>
