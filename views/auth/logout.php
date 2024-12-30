<?php
session_start();

// 保存訪客遊戲資訊
$temp_game_code = $_SESSION['temp_game_code'] ?? null;
$is_guest = $_SESSION['is_guest'] ?? null;

// 只清除登入相關的 session 變數
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// 如果有訪客遊戲資訊，則保留
if ($temp_game_code) {
    $_SESSION['temp_game_code'] = $temp_game_code;
    $_SESSION['is_guest'] = $is_guest;
}

header('Location: /java/views/auth/login.php');
exit;