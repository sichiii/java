<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /java/views/auth/login.php');
    exit;
}

require_once '../../app/models/Room.php';
require_once '../../app/models/User.php';

$room = new Room();
$user = new User();

// 獲取房間代碼
$roomCode = isset($_GET['code']) ? $_GET['code'] : null;
if (!$roomCode) {
    header('Location: /java/views/game/home.php');
    exit;
}

// 獲取房間信息
$roomData = $room->getRoomByCode($roomCode);
if (!$roomData) {
    header('Location: /java/views/game/home.php');
    exit;
}

$userData = $user->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>五子棋 - 線上對戰</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        #gameBoard {
            border: 1px solid #000;
            margin: 20px auto;
            background-color: #DEB887;
        }

        .game-info {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .room-code {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin: 20px 0;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            min-width: 300px;
        }

        .color-buttons {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            justify-content: center;
        }

        .color-btn {
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .color-btn.black {
            background: #333;
            color: white;
        }

        .color-btn.white {
            background: #fff;
            color: #333;
            border: 2px solid #333;
        }

        .game-controls {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .game-btn {
            min-width: 120px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
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

        .game-message {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>線上對戰</h2>
        <div class="room-code">房間代碼：<?php echo htmlspecialchars($roomCode); ?></div>
        <div class="game-info">
            玩家：<?php echo htmlspecialchars($userData['username']); ?> 
            (積分：<?php echo $userData['rating']; ?>)
        </div>
        <div id="game-message" class="game-message"></div>
        <div id="current-turn" class="game-info">等待對手加入...</div>
        
        <canvas id="gameBoard" width="600" height="600"></canvas>
        
        <div class="game-controls">
            <button id="rematchBtn" onclick="requestRematch()" class="game-btn" style="display: none;">
                再來一局
            </button>
            <button onclick="window.location.href='/java/views/game/home.php'" class="game-btn">
                返回大廳
            </button>
        </div>
    </div>

    <!-- 選擇顏色的模態框 -->
    <div id="colorSelectModal" class="modal">
        <div class="modal-content">
            <h3>選擇棋子顏色</h3>
            <div class="color-buttons">
                <button onclick="selectColor('black')" class="color-btn black">選擇黑子</button>
                <button onclick="selectColor('white')" class="color-btn white">選擇白子</button>
            </div>
        </div>
    </div>

    <script>
        // 基本遊戲設置
        const BOARD_SIZE = 15;
        const CELL_SIZE = 600 / BOARD_SIZE;
        const PADDING = CELL_SIZE / 2;
        
        const canvas = document.getElementById('gameBoard');
        const ctx = canvas.getContext('2d');
        const currentTurnDisplay = document.getElementById('current-turn');
        const gameMessage = document.getElementById('game-message');
        const rematchBtn = document.getElementById('rematchBtn');
        
        let gameState = {
            board: Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(0)),
            isMyTurn: false,
            myColor: null,
            gameStarted: false,
            gameEnded: false
        };
        
        // WebSocket 連接
        let ws;
        let wsReconnectAttempts = 0;
        const MAX_RECONNECT_ATTEMPTS = 5;
        
        function connectWebSocket() {
            ws = new WebSocket('ws://localhost:8080');
            
            ws.onopen = () => {
                console.log('Connected to WebSocket server');
                wsReconnectAttempts = 0;
                gameMessage.textContent = '';
                
                // 連接成功後立即發送加入房間的消息
                ws.send(JSON.stringify({
                    type: 'join',
                    roomCode: roomCode,
                    userId: userId
                }));
            };
            
            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                gameMessage.textContent = 'WebSocket 連接錯誤，正在嘗試重新連接...';
            };
            
            ws.onclose = () => {
                console.log('Disconnected from WebSocket server');
                gameMessage.textContent = 'WebSocket 連接已斷開，正在重新連接...';
                
                // 嘗試重新連接
                if (wsReconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                    wsReconnectAttempts++;
                    setTimeout(connectWebSocket, 3000);
                } else {
                    gameMessage.textContent = 'WebSocket 連接失敗，請重新整理頁面';
                }
            };
            
            ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                console.log('Received:', data);
                
                switch(data.type) {
                    case 'player_joined':
                        showColorSelection();
                        gameMessage.textContent = '對手已加入，請選擇顏色';
                        currentTurnDisplay.textContent = '選擇棋子顏色';
                        break;
                        
                    case 'game_start':
                        gameState.gameStarted = true;
                        gameState.isMyTurn = gameState.myColor === 'black';
                        gameState.gameEnded = false;
                        rematchBtn.style.display = 'none';
                        gameMessage.textContent = '';
                        updateGameStatus();
                        break;
                        
                    case 'move':
                        const { row, col, color, nextTurn } = data;
                        gameState.board[row][col] = color === 'black' ? 1 : 2;
                        gameState.isMyTurn = nextTurn === gameState.myColor;
                        drawBoard();
                        updateGameStatus();
                        break;
                        
                    case 'game_end':
                        gameState.gameEnded = true;
                        gameMessage.textContent = data.message;
                        rematchBtn.style.display = 'inline-block';
                        break;
                        
                    case 'player_left':
                        handlePlayerLeft(data);
                        break;
                        
                    case 'rematch_start':
                        resetGame();
                        showColorSelection();
                        break;
                }
            };
        }
        
        // 初始化 WebSocket 連接
        connectWebSocket();
        
        // 修改其他發送消息的函數，添加連接檢查
        function sendWebSocketMessage(message) {
            if (ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify(message));
            } else {
                gameMessage.textContent = 'WebSocket 連接已斷開，正在重新連接...';
                setTimeout(() => sendWebSocketMessage(message), 1000);
            }
        }
        
        // 更新其他使用 ws.send 的函數
        function selectColor(color) {
            sendWebSocketMessage({
                type: 'select_color',
                roomCode: roomCode,
                userId: userId,
                color: color
            });
            gameState.myColor = color;
            document.getElementById('colorSelectModal').style.display = 'none';
        }
        
        function showColorSelection() {
            document.getElementById('colorSelectModal').style.display = 'flex';
        }
        
        function makeMove(row, col) {
            if (!gameState.isMyTurn || gameState.gameEnded) return;
            if (gameState.board[row][col] !== 0) return;
            
            sendWebSocketMessage({
                type: 'move',
                roomCode: roomCode,
                userId: userId,
                row: row,
                col: col
            });
        }
        
        function handlePlayerLeft(data) {
            gameMessage.textContent = '對手已離開遊戲';
            if (data.winner === userId) {
                gameMessage.textContent += '，你獲勝了！';
            }
            gameState.gameEnded = true;
            rematchBtn.style.display = 'inline-block';
        }
        
        function requestRematch() {
            sendWebSocketMessage({
                type: 'rematch',
                roomCode: roomCode,
                userId: userId
            });
        }
        
        function resetGame() {
            gameState = {
                board: Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(0)),
                isMyTurn: false,
                myColor: null,
                gameStarted: false,
                gameEnded: false
            };
            gameMessage.textContent = '';
            rematchBtn.style.display = 'none';
            drawBoard();
            updateGameStatus();
        }
        
        function updateGameStatus() {
            if (gameState.gameEnded) return;
            if (!gameState.gameStarted) {
                currentTurnDisplay.textContent = '等待對手加入...';
            } else {
                currentTurnDisplay.textContent = gameState.isMyTurn ? 
                    '輪到你下棋' : '等待對手下棋';
            }
        }
        
        // 初始化遊戲
        canvas.addEventListener('click', function(e) {
            if (!gameState.isMyTurn || gameState.gameEnded) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left - PADDING;
            const y = e.clientY - rect.top - PADDING;
            
            const col = Math.round(x / CELL_SIZE);
            const row = Math.round(y / CELL_SIZE);
            
            if (row >= 0 && row < BOARD_SIZE && col >= 0 && col < BOARD_SIZE) {
                makeMove(row, col);
            }
        });
        
        function drawBoard() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // 畫線
            for (let i = 0; i < BOARD_SIZE; i++) {
                ctx.beginPath();
                ctx.moveTo(PADDING + i * CELL_SIZE, PADDING);
                ctx.lineTo(PADDING + i * CELL_SIZE, canvas.height - PADDING);
                ctx.stroke();
                
                ctx.beginPath();
                ctx.moveTo(PADDING, PADDING + i * CELL_SIZE);
                ctx.lineTo(canvas.width - PADDING, PADDING + i * CELL_SIZE);
                ctx.stroke();
            }
            
            // 畫棋子
            for (let i = 0; i < BOARD_SIZE; i++) {
                for (let j = 0; j < BOARD_SIZE; j++) {
                    if (gameState.board[i][j] !== 0) {
                        const x = PADDING + j * CELL_SIZE;
                        const y = PADDING + i * CELL_SIZE;
                        
                        ctx.beginPath();
                        ctx.arc(x, y, CELL_SIZE * 0.4, 0, Math.PI * 2);
                        ctx.fillStyle = gameState.board[i][j] === 1 ? '#000' : '#fff';
                        ctx.fill();
                        ctx.strokeStyle = '#000';
                        ctx.stroke();
                    }
                }
            }
        }
        
        drawBoard();
    </script>
</body>
</html> 