<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - 五子棋</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($title); ?></h2>
        <div class="game-info">
            <p>玩家：<?php echo htmlspecialchars($username); ?> (黑子)</p>
            <p>對手：AI (白子)</p>
        </div>
        
        <div class="game-status">
            <p id="current-player">當前回合：黑子</p>
            <p id="game-message"></p>
        </div>

        <div id="game-board"></div>

        <div class="game-controls">
            <button class="game-btn" onclick="game.resetGame()">重新開始</button>
            <button class="game-btn" onclick="location.href='/java/game/home'">返回選單</button>
        </div>
    </div>

    <script src="/java/public/js/ai.js"></script>
    <script src="/java/public/js/game.js"></script>
    <script>
        const game = new GomokuGame(15, 'single');
    </script>
</body>
</html> 