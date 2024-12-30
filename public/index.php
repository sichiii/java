<?php
session_start();
define('BASE_PATH', dirname(__DIR__));

// 自動載入類
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 獲取請求的 URI
$uri = $_SERVER['REQUEST_URI'];
$uri = str_replace('/java/', '', $uri);
$method = $_SERVER['REQUEST_METHOD'];

// 路由表
require_once BASE_PATH . '/app/routes.php';

// 路由匹配
$routeKey = $method . '|' . $uri;
if (isset($routes[$routeKey])) {
    [$controller, $action] = $routes[$routeKey];
    $controllerClass = $controller;
    if (file_exists(BASE_PATH . "/app/controllers/{$controllerClass}.php")) {
        require_once BASE_PATH . "/app/controllers/{$controllerClass}.php";
        $controllerInstance = new $controllerClass();
        $controllerInstance->$action();
    } else {
        die("Controller not found: {$controllerClass}");
    }
} else {
    // 404 處理
    header('HTTP/1.0 404 Not Found');
    echo "Route not found: {$uri}";
} 