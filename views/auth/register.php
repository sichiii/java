<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊 - 五子棋遊戲</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(to right, #00c6ff, #92fe9d);
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            width: 400px;
        }

        h1 {
            color: #333;
            margin-bottom: 40px;
            font-size: 32px;
            text-align: center;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #222;
            font-size: 16px;
            font-weight: 550;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid #e8e8e8;
            border-radius: 15px;
            font-size: 16px;
            background-color: #fff;
            box-sizing: border-box;
            transition: all 0.3s ease;
            height: 50px;
        }

        .form-group input:focus {
            border-color: #00c6ff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 198, 255, 0.1);
        }

        .btn-primary {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 15px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            font-weight: normal;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .auth-links {
            margin-top: 20px;
            text-align: center;
        }

        .auth-links a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>註冊帳號</h1>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <form method="POST" action="/java/views/auth/process_register.php" class="auth-form">
            <div class="form-group">
                <label for="username">用戶名</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">電子郵件</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-primary">註冊</button>
            
            <div class="auth-links">
                <a href="/java/views/auth/login.php">已有帳號？立即登入</a>
            </div>
        </form>
    </div>
</body>
</html> 