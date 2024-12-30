<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /java/views/auth/login.php');
    exit;
}

require_once '../../app/models/User.php';
$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>五子棋 - 遊戲大廳</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(to right, #00c6ff, #92fe9d);
            font-family: Arial, sans-serif;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        
        .welcome-section {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-section h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .user-info {
            color: #666;
            font-size: 18px;
        }
        
        .game-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .game-btn {
            width: 250px;
            padding: 15px 0;
            font-size: 18px;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .game-btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        
        .logout-section {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn-logout {
            background-color: #f44336;
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-logout:hover {
            background-color: #da190b;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal.show {
            display: flex !important;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: #fff;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 90%;
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-content h3 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
            text-align: center;
        }
        
        .modal-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .modal-btn {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .modal-btn.btn-secondary {
            background-color: #666;
        }
        
        .modal-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        #room-code {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 15px;
            font-size: 16px;
            text-align: center;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-section">
            <h2>歡迎來到五子棋遊戲大廳</h2>
            <div class="user-info">
                玩家：<?php echo htmlspecialchars($userData['username']); ?>
                <br>
                積分：<?php echo $userData['rating']; ?>
            </div>
        </div>

        <div class="game-buttons">
            <button onclick="window.location.href='/java/views/game/single-player.php'" class="game-btn">
                單人對戰 AI
            </button>
            <button onclick="window.location.href='/java/views/game/offline-mode.php'" class="game-btn">
                單機雙人模式
            </button>
            <button onclick="showOnlineOptions()" class="game-btn">
                線上對戰
            </button>
            <button onclick="window.location.href='/java/views/game/leaderboard.php'" class="game-btn">
                排行榜
            </button>
        </div>

        <div class="logout-section">
            <button onclick="window.location.href='/java/views/auth/logout.php'" class="btn-logout">
                登出
            </button>
        </div>
    </div>

    <!-- 線上對戰選項的模態框 -->
    <div id="onlineModal" class="modal">
        <div class="modal-content">
            <h3>選擇遊戲模式</h3>
            <div class="modal-buttons">
                <button onclick="createRoom()" class="modal-btn">創建房間</button>
                <button onclick="showJoinRoom()" class="modal-btn">加入房間</button>
                <button onclick="hideOnlineOptions()" class="modal-btn btn-secondary">返回</button>
            </div>
        </div>
    </div>

    <!-- 加入房間的模態框 -->
    <div id="joinModal" class="modal">
        <div class="modal-content">
            <h3>輸入房間代碼</h3>
            <input type="text" id="room-code" maxlength="6" placeholder="輸入6位房間代碼">
            <div class="modal-buttons">
                <button onclick="joinRoom()" class="modal-btn">加入</button>
                <button onclick="hideJoinRoom()" class="modal-btn btn-secondary">返回</button>
            </div>
        </div>
    </div>

    <script>
    function showOnlineOptions() {
        const modal = document.getElementById('onlineModal');
        modal.classList.add('show');
        console.log('Showing online options modal');
    }

    function hideOnlineOptions() {
        const modal = document.getElementById('onlineModal');
        modal.classList.remove('show');
        console.log('Hiding online options modal');
    }

    function showJoinRoom() {
        document.getElementById('onlineModal').classList.remove('show');
        document.getElementById('joinModal').classList.add('show');
    }

    function hideJoinRoom() {
        document.getElementById('joinModal').classList.remove('show');
        document.getElementById('onlineModal').classList.add('show');
    }

    function createRoom() {
        fetch('/java/api/game/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('房間創建成功！房間代碼：' + data.code);
                window.location.href = `/java/views/game/room.php?code=${data.code}`;
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('創建房間時發生錯誤');
        });
    }

    function joinRoom() {
        const code = document.getElementById('room-code').value.trim().toUpperCase();
        if (!code) {
            alert('請輸入房間代碼');
            return;
        }
        
        const formData = new FormData();
        formData.append('code', code);
        
        fetch('/java/api/game/join.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `/java/views/game/room.php?code=${data.code}`;
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('加入房間時發生錯誤');
        });
    }

    // 點擊模態框外部時關閉
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show');
            console.log('Closing modal by outside click');
        }
    }
    </script>
</body>
</html>
