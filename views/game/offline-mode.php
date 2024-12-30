<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /java/views/auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>五子棋 - 單機雙人模式</title>
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
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        #gameBoard {
            border: 1px solid #000;
            background-color: #DEB887;
        }
        .game-info {
            margin-bottom: 20px;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="game-info">
            <h2>單機雙人模式</h2>
            <div id="current-player">當前回合：黑子</div>
            <div id="game-message"></div>
        </div>
        
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
    const canvas = document.getElementById('gameBoard');
    const ctx = canvas.getContext('2d');
    const BOARD_SIZE = 15;
    const CELL_SIZE = canvas.width / BOARD_SIZE;
    const PADDING = CELL_SIZE / 2;
    
    let gameBoard = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(0));
    let gameEnded = false;
    let currentPlayer = 1; // 1 = 黑子, 2 = 白子

    // 添加 Canvas 點擊事件監聽
    canvas.addEventListener('click', function(e) {
        if (gameEnded) return;

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left - PADDING;
        const y = e.clientY - rect.top - PADDING;
        
        // 計算點擊的格子位置
        const col = Math.round(x / CELL_SIZE);
        const row = Math.round(y / CELL_SIZE);
        
        // 確保點擊在有效範圍內
        if (row >= 0 && row < BOARD_SIZE && col >= 0 && col < BOARD_SIZE && gameBoard[row][col] === 0) {
            makeMove(row, col);
        }
    });

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

    function makeMove(row, col) {
        gameBoard[row][col] = currentPlayer;
        drawBoard();
        
        if (checkWin(row, col)) {
            endGame(`${currentPlayer === 1 ? '黑子' : '白子'}獲勝！`);
            return;
        }
        
        currentPlayer = currentPlayer === 1 ? 2 : 1;
        document.getElementById('current-player').textContent = 
            `當前回合：${currentPlayer === 1 ? '黑子' : '白子'}`;
    }

    function checkWin(row, col) {
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
                while (
                    r >= 0 && r < BOARD_SIZE && 
                    c >= 0 && c < BOARD_SIZE && 
                    gameBoard[r][c] === currentPlayer
                ) {
                    count++;
                    r += dx;
                    c += dy;
                }
            }
            if (count >= 5) return true;
        }
        return false;
    }

    function endGame(message) {
        gameEnded = true;
        document.getElementById('game-message').textContent = message;
        document.getElementById('current-player').textContent = '遊戲結束';
    }

    function resetGame() {
        gameBoard = Array(BOARD_SIZE).fill().map(() => Array(BOARD_SIZE).fill(0));
        gameEnded = false;
        currentPlayer = 1;
        document.getElementById('game-message').textContent = '';
        document.getElementById('current-player').textContent = '當前回合：黑子';
        drawBoard();
    }

    // 初始化棋盤
    drawBoard();
    </script>
</body>
</html> 