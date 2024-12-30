<?php require_once BASE_PATH . '/views/layout/main.php'; ?>

<div class="container">
    <h2>雙人對戰</h2>
    <div class="game-info">
        <p>玩家1：<?php echo htmlspecialchars($_SESSION['username']); ?> (黑子)</p>
        <p>玩家2：對手 (白子)</p>
    </div>
    
    <div class="game-status">
        <p id="current-player">當前回合：黑子</p>
        <p id="game-message"></p>
    </div>

    <div id="game-board"></div>

    <div class="game-controls">
        <button class="game-btn" onclick="game.resetGame()">重新開始</button>
        <button class="game-btn" onclick="location.href='index.php?controller=game&action=home'">返回選單</button>
    </div>
</div>

<script src="/java/public/js/game.js"></script>
<script>
    // 初始化遊戲
    const game = new GomokuGame(15, 'two');
</script> 