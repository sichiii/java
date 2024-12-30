<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>五子棋遊戲</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
</head>
<body>
    <?php include BASE_PATH . '/views/game/' . $view . '.php'; ?>
    
    <?php if ($view === 'single_player' || $view === 'two_player'): ?>
        <script src="/java/public/js/ai.js"></script>
        <script src="/java/public/js/game.js"></script>
        <script>
            // 初始化遊戲
            const game = new GomokuGame(15, '<?php echo $view === 'single_player' ? 'single' : 'two' ?>');
        </script>
    <?php endif; ?>
</body>
</html> 