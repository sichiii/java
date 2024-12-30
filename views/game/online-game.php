<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /java/views/auth/login.php');
    exit;
}

$roomCode = $_GET['code'] ?? '';
if (empty($roomCode)) {
    header('Location: /java/views/game/home.php');
    exit;
}

require_once '../../app/models/Room.php';
require_once '../../app/models/Game.php';
require_once '../../app/models/User.php';

$room = new Room();
$game = new Game();
$user = new User();

$roomData = $room->getRoomByCode($roomCode);
$gameData = $game->getGameByRoomId($roomData['id']);
$currentUser = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>線上對戰 - 五子棋</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #00c6ff, #92fe9d);
        }
        
        .game-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .game-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .room-code {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .game-status {
            font-size: 18px;
            color: #666;
        }

        .board {
            width: 600px;
            height: 600px;
            margin: 20px auto;
            background: #dcb35c;
            position: relative;
            border: 2px solid #8b4513;
        }

        .grid {
            position: absolute;
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: repeat(15, 1fr);
            grid-template-rows: repeat(15, 1fr);
        }

        .cell {
            border: 1px solid #8b4513;
            cursor: pointer;
        }

        .piece {
            width: 80%;
            height: 80%;
            border-radius: 50%;
            margin: 10%;
        }

        .black {
            background: #000;
        }

        .white {
            background: #fff;
            border: 1px solid #000;
        }

        .btn-leave {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-leave:hover {
            background: #d32f2f;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .modal-content h3 {
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
        }

        .modal-content .game-btn {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 15px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-content .game-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-info">
            <div class="room-code">房間驗證碼：<?php echo htmlspecialchars($roomCode); ?></div>
            <div class="game-status" id="gameStatus">
                <?php if ($roomData['status'] === 'waiting'): ?>
                    等待對手加入...
                <?php else: ?>
                    遊戲進行中
                <?php endif; ?>
            </div>
        </div>

        <div class="board">
            <div class="grid" id="gameBoard">
                <?php for ($i = 0; $i < 15; $i++): ?>
                    <?php for ($j = 0; $j < 15; $j++): ?>
                        <div class="cell" data-row="<?php echo $i; ?>" data-col="<?php echo $j; ?>"></div>
                    <?php endfor; ?>
                <?php endfor; ?>
            </div>
        </div>

        <button class="btn-leave" onclick="leaveGame()">離開遊戲</button>
    </div>

    <!-- 角色選擇模態框 -->
    <div id="roleSelectionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>選擇您的角色</h3>
            <button onclick="selectRole('black')" class="game-btn">選擇黑子</button>
            <button onclick="selectRole('white')" class="game-btn">選擇白子</button>
        </div>
    </div>

    <script>
        const gameBoard = document.getElementById('gameBoard');
        const gameStatus = document.getElementById('gameStatus');
        const roomCode = '<?php echo $roomCode; ?>';
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        let isMyTurn = false;

        // 每秒更新遊戲狀態
        setInterval(updateGameState, 1000);

        // 添加棋盤點擊事件
        gameBoard.addEventListener('click', (e) => {
            const cell = e.target.closest('.cell');
            if (!cell || !isMyTurn) return;

            const row = cell.dataset.row;
            const col = cell.dataset.col;
            
            makeMove(row, col);
        });

        // 更新遊戲狀態
        function updateGameState() {
            fetch(`/java/api/game/status.php?code=${roomCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateBoard(data.gameState);
                        updateStatus(data.status, data.currentTurn);
                    }
                });
        }

        // 下棋
        function makeMove(row, col) {
            fetch('/java/api/game/move.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    code: roomCode,
                    row: row,
                    col: col
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                }
            });
        }

        // 離開遊戲
        function leaveGame() {
            if (confirm('確定要離開遊戲嗎？')) {
                window.location.href = '/java/views/game/home.php';
            }
        }

        // 更新棋盤顯示
        function updateBoard(gameState) {
            if (!gameState) return;
            
            const cells = document.querySelectorAll('.cell');
            cells.forEach(cell => {
                const row = cell.dataset.row;
                const col = cell.dataset.col;
                cell.innerHTML = '';
                
                if (gameState[row][col] === 1) {
                    cell.innerHTML = '<div class="piece black"></div>';
                } else if (gameState[row][col] === 2) {
                    cell.innerHTML = '<div class="piece white"></div>';
                }
            });
        }

        // 更新遊戲狀態顯示
        function updateStatus(status, currentTurn) {
            let statusText = '';
            
            if (status === 'waiting') {
                statusText = '等待對手加入...';
            } else if (status === 'playing') {
                isMyTurn = currentTurn === currentUserId;
                statusText = isMyTurn ? '輪到您下棋' : '等待對手下棋';
            } else if (status === 'finished') {
                statusText = '遊戲結束';
                isMyTurn = false;
            }
            
            gameStatus.textContent = statusText;
        }

        // 角色選擇相關函數
        function showRoleSelection() {
            document.getElementById('roleSelectionModal').style.display = 'block';
        }

        function selectRole(role) {
            fetch('/java/api/game/select-role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    code: roomCode,
                    role: role
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('roleSelectionModal').style.display = 'none';
                } else {
                    alert(data.message);
                }
            });
        }

        // 定期檢查房間狀態
        setInterval(() => {
            fetch(`/java/api/game/status.php?code=${roomCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === 'waiting_selection' && !document.getElementById('roleSelectionModal').style.display === 'block') {
                            showRoleSelection();
                        }
                        updateGameState(data);
                    }
                });
        }, 1000);
    </script>
</body>
</html>
