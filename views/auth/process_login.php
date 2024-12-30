<?php
session_start();
require_once '../../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = '請填寫所有欄位';
        header('Location: /java/views/auth/login.php');
        exit;
    }
    
    $user = new User();
    $result = $user->login($username, $password);
    
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['username'] = $result['user']['username'];
        header('Location: /java/views/game/home.php');
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: /java/views/auth/login.php');
    }
    exit;
}

header('Location: /java/views/auth/login.php');
exit; 