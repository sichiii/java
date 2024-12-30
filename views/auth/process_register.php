<?php
session_start();
require_once '../../app/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = '請填寫所有欄位';
        header('Location: /java/views/auth/register.php');
        exit;
    }
    
    $user = new User();
    $result = $user->register($username, $email, $password);
    
    if ($result['success']) {
        $_SESSION['success'] = '註冊成功，請登入';
        header('Location: /java/views/auth/login.php');
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: /java/views/auth/register.php');
    }
    exit;
}

header('Location: /java/views/auth/register.php');
exit; 