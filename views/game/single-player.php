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
    <title>五子棋 - 單人對戰 AI</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #00c6ff, #92fe9d);
            min-height: 100vh;
        }

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

        .game-message {
            margin: 10px 0;
            font-size: 20px;
            font-weight: bold;
            color: #333;
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .game-btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .game-btn:active {
            transform: translateY(1px);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .back-btn {
            background-color: #666;
            border: 2px solid #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>單人對戰 AI</h2>
        <div class="game-info">
            玩家：<?php echo htmlspecialchars($userData['username']); ?> 
            (積分：<?php echo $userData['rating']; ?>)
        </div>
        <div id="game-message" class="game-message"></div>
        <div id="current-turn" class="game-info">當前回合：黑子</div>
        
        <canvas id="gameBoard" width="600" height="600"></canvas>
        
        <div class="game-controls">
            <button onclick="resetGame()" class="game-btn">
                重新開始
            </button>
            <button onclick="window.location.href='/java/views/game/home.php'" class="game-btn">
                返回大廳
            </button>
        </div>
    </div>

    <script>
        const BOARD_SIZE = 15;
        const CELL_SIZE = 600 / BOARD_SIZE;
        const PADDING = CELL_SIZE / 2;
        
        const canvas = document.getElementById('gameBoard');
        const ctx = canvas.getContext('2d');
        const currentTurnDisplay = document.getElementById('current-turn');
        const gameMessage = document.getElementById('game-message');
        
        let gameBoard = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(0));
        let gameEnded = false;
        let isPlayerTurn = true;
        
        function drawBoard() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // 畫線
            for (let i = 0; i < BOARD_SIZE; i++) {
                // 垂直線
                ctx.beginPath();
                ctx.moveTo(PADDING + i * CELL_SIZE, PADDING);
                ctx.lineTo(PADDING + i * CELL_SIZE, canvas.height - PADDING);
                ctx.stroke();
                
                // 水平線
                ctx.beginPath();
                ctx.moveTo(PADDING, PADDING + i * CELL_SIZE);
                ctx.lineTo(canvas.width - PADDING, PADDING + i * CELL_SIZE);
                ctx.stroke();
            }
            
            // 畫棋子
            for (let i = 0; i < BOARD_SIZE; i++) {
                for (let j = 0; j < BOARD_SIZE; j++) {
                    if (gameBoard[i][j] !== 0) {
                        const x = PADDING + j * CELL_SIZE;
                        const y = PADDING + i * CELL_SIZE;
                        
                        ctx.beginPath();
                        ctx.arc(x, y, CELL_SIZE * 0.4, 0, Math.PI * 2);
                        ctx.fillStyle = gameBoard[i][j] === 1 ? '#000' : '#fff';
                        ctx.fill();
                        ctx.strokeStyle = '#000';
                        ctx.stroke();
                    }
                }
            }
        }
        
        function makeMove(row, col, player) {
            gameBoard[row][col] = player;
            drawBoard();
            
            if (checkWin(row, col, player)) {
                gameEnded = true;
                gameMessage.textContent = player === 1 ? '玩家獲勝！' : 'AI獲勝！';
                currentTurnDisplay.textContent = '遊戲結束';
                return true;
            }
            
            if (checkDraw()) {
                gameEnded = true;
                gameMessage.textContent = '平局！';
                currentTurnDisplay.textContent = '遊戲結束';
                return true;
            }
            
            return false;
        }
        
        function checkWin(row, col, player) {
            const directions = [
                [[0, 1], [0, -1]], // 水平
                [[1, 0], [-1, 0]], // 垂直
                [[1, 1], [-1, -1]], // 對角線
                [[1, -1], [-1, 1]] // 反對角線
            ];
            
            for (const direction of directions) {
                let count = 1;
                for (const [dx, dy] of direction) {
                    let r = row + dx;
                    let c = col + dy;
                    while (r >= 0 && r < BOARD_SIZE && c >= 0 && c < BOARD_SIZE && gameBoard[r][c] === player) {
                        count++;
                        r += dx;
                        c += dy;
                    }
                }
                if (count >= 5) return true;
            }
            return false;
        }
        
        function checkDraw() {
            return gameBoard.every(row => row.every(cell => cell !== 0));
        }
        
        // 評估位置分數
        function evaluatePosition(row, col, player) {
            const opponent = player === 1 ? 2 : 1;
            let score = 0;
            const directions = [
                [[0, 1], [0, -1]], // 水平
                [[1, 0], [-1, 0]], // 垂直
                [[1, 1], [-1, -1]], // 對角線
                [[1, -1], [-1, 1]] // 反對角線
            ];

            for (const direction of directions) {
                let count = 1;
                let blocked = 0;
                let space = 0;

                for (const [dx, dy] of direction) {
                    let r = row + dx;
                    let c = col + dy;
                    let consecutive = 0;
                    let hasSpace = false;

                    while (r >= 0 && r < BOARD_SIZE && c >= 0 && c < BOARD_SIZE) {
                        if (gameBoard[r][c] === player) {
                            consecutive++;
                        } else if (gameBoard[r][c] === 0) {
                            hasSpace = true;
                            break;
                        } else {
                            blocked++;
                            break;
                        }
                        r += dx;
                        c += dy;
                    }
                    count += consecutive;
                    if (hasSpace) space++;
                }

                // 評分規則
                if (count >= 5) score += 100000;  // 獲勝
                else if (count === 4 && blocked === 0) score += 10000;  // 活四
                else if (count === 4 && blocked === 1) score += 1000;   // 死四
                else if (count === 3 && blocked === 0) score += 1000;   // 活三
                else if (count === 3 && blocked === 1) score += 100;    // 死三
                else if (count === 2 && blocked === 0) score += 100;    // 活二
                else if (count === 2 && blocked === 1) score += 10;     // 死二
            }
            return score;
        }

        // 改進的 AI 移動邏輯
        function aiMove() {
            if (gameEnded) return;
            
            currentTurnDisplay.textContent = 'AI思考中...';
            
            setTimeout(() => {
                let bestScore = -Infinity;
                let bestMove = null;
                
                // 遍歷所有可能的位置
                for (let i = 0; i < BOARD_SIZE; i++) {
                    for (let j = 0; j < BOARD_SIZE; j++) {
                        if (gameBoard[i][j] === 0) {
                            // 評估AI（白子）的得分
                            let aiScore = evaluatePosition(i, j, 2);
                            // 評估玩家（黑子）的得分
                            let playerScore = evaluatePosition(i, j, 1);
                            
                            // 綜合評分：���要進攻也要防守
                            let totalScore = aiScore + playerScore * 1.1;  // 略微偏重防守
                            
                            if (totalScore > bestScore) {
                                bestScore = totalScore;
                                bestMove = {row: i, col: j};
                            }
                        }
                    }
                }
                
                if (bestMove) {
                    if (!makeMove(bestMove.row, bestMove.col, 2)) {
                        isPlayerTurn = true;
                        currentTurnDisplay.textContent = '當前回合：黑子';
                    }
                }
            }, 500);
        }
        
        function resetGame() {
            gameBoard = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(0));
            gameEnded = false;
            isPlayerTurn = true;
            gameMessage.textContent = '';
            currentTurnDisplay.textContent = '當前回合：黑子';
            drawBoard();
        }
        
        canvas.addEventListener('click', function(e) {
            if (!isPlayerTurn || gameEnded) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left - PADDING;
            const y = e.clientY - rect.top - PADDING;
            
            const col = Math.round(x / CELL_SIZE);
            const row = Math.round(y / CELL_SIZE);
            
            if (row >= 0 && row < BOARD_SIZE && col >= 0 && col < BOARD_SIZE && gameBoard[row][col] === 0) {
                if (!makeMove(row, col, 1)) {
                    isPlayerTurn = false;
                    currentTurnDisplay.textContent = '當前回合：白子';
                    aiMove();
                }
            }
        });
        
        // 初始化遊戲
        drawBoard();
    </script>
</body>
</html> 