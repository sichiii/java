<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['code']) || !isset($data['winner_id'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

require_once '../../app/models/Room.php';

try {
    $room = new Room();
    $roomData = $room->getRoomByCode($data['code']);
    
    if (!$roomData) {
        throw new Exception('房間不存在');
    }

    // 確定敗者ID
    $loserId = ($data['winner_id'] == $roomData['creator_id']) 
        ? $roomData['player2_id'] 
        : $roomData['creator_id'];
    
    // 更新遊戲結果
    $result = $room->endGame($data['code'], $data['winner_id'], $loserId);
    
    if ($result['success']) {
        // 返回完整的遊戲結束信息
        echo json_encode([
            'success' => true,
            'status' => 'finished',
            'winner_id' => $data['winner_id'],
            'winner_name' => $result['winner_name'],
            'winner_rating' => $result['winner_rating'],
            'loser_rating' => $result['loser_rating'],
            'creator_id' => $roomData['creator_id'],
            'player2_id' => $roomData['player2_id']
        ]);
    } else {
        echo json_encode($result);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 