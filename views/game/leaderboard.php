<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /java/views/auth/login.php');
    exit;
}

require_once '../../app/models/User.php';
$user = new User();
$leaderboard = $user->getLeaderboard();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>五子棋 - 排行榜</title>
    <link rel="stylesheet" href="/java/public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #00c6ff, #92fe9d);  /* 修改背景為漸層 */
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 25px;  /* 增加圓角 */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);  /* 調整陰影 */
        }
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .leaderboard-table th, 
        .leaderboard-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .leaderboard-table th {
            background-color: #4CAF50;
            color: white;
        }
        .leaderboard-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .leaderboard-table tr:hover {
            background-color: #ddd;
        }
        .back-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .back-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>排行榜</h2>
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>排名</th>
                    <th>玩家</th>
                    <th>積分</th>
                    <th>勝</th>
                    <th>敗</th>
                    <th>總場數</th>
                    <th>勝率</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $index => $player): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($player['username']); ?></td>
                    <td><?php echo $player['rating']; ?></td>
                    <td><?php echo $player['wins']; ?></td>
                    <td><?php echo $player['losses']; ?></td>
                    <td><?php echo $player['total_games']; ?></td>
                    <td><?php echo $player['win_rate']; ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <button onclick="window.location.href='/java/views/game/home.php'" class="back-btn">
            返回大廳
        </button>
    </div>
</body>
</html> 