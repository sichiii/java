<?php
session_start();
require_once '../../app/models/Room.php';
require_once '../../app/models/Game.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';
$row = $data['row'] ?? null;
$col = $data['col'] ?? null;

if (empty($code) || $row === null || $col === null) {
    echo json_encode(['success' => false, 'message' => '參數錯誤']);
    exit;
}

try {
    $game = new Game();
    $result = $game->makeMove($code, $_SESSION['user_id'], $row, $col);
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 