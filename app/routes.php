<?php
return [
    // 首頁路由
    'GET|' => ['HomeController', 'index'],
    
    // 認證路由
    'GET|auth/login' => ['AuthController', 'showLogin'],
    'POST|auth/do-login' => ['AuthController', 'login'],
    'GET|auth/register' => ['AuthController', 'showRegister'],
    'POST|auth/do-register' => ['AuthController', 'register'],
    'GET|auth/logout' => ['AuthController', 'logout'],
    
    // 遊戲路由
    'GET|game/home' => ['GameController', 'home']
]; 