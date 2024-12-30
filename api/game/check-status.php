<?php
session_start();
require_once '../../app/models/Room.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

if (!isset($_GET['code'])) {
    echo json_encode(['success' => false, 'message' => '缺少房間代碼']);
    exit;
}

try {
    $room = new Room();
    $roomData = $room->getRoomByCode($_GET['code']);
    
    if (!$roomData) {
        echo json_encode(['success' => false, 'message' => '房間不存在']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'status' => $roomData['status'],
        'game_status' => $roomData['game_status'],
        'black_player' => $roomData['black_player_name'],
        'white_player' => $roomData['white_player_name'],
        'creator' => $roomData['creator_name'],
        'player2' => $roomData['player2_name']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
