<?php
session_start();
require_once '../../app/models/Room.php';
require_once '../../app/models/Game.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

$code = $_GET['code'] ?? '';
if (empty($code)) {
    echo json_encode(['success' => false, 'message' => '缺少房間代碼']);
    exit;
}

try {
    $room = new Room();
    $game = new Game();
    
    $roomData = $room->getRoomByCode($code);
    $gameData = $game->getGameByRoomId($roomData['id']);
    
    echo json_encode([
        'success' => true,
        'status' => $roomData['status'],
        'gameState' => json_decode($gameData['board_state'] ?? '[]'),
        'currentTurn' => $gameData['current_turn']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 