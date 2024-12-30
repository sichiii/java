<?php
session_start();
require_once '../../app/models/Room.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

if (!isset($_POST['code'])) {
    echo json_encode(['success' => false, 'message' => '缺少房間代碼']);
    exit;
}

try {
    $room = new Room();
    $roomCode = $_POST['code'];
    
    // 檢查房間是否存在
    $roomData = $room->getRoomByCode($roomCode);
    if (!$roomData) {
        throw new Exception('房間不存在');
    }
    
    // 檢查房間狀態
    if ($roomData['status'] !== 'waiting') {
        throw new Exception('房間已滿或遊戲已開始');
    }
    
    // 檢查是否為房主
    if ($roomData['creator_id'] == $_SESSION['user_id']) {
        throw new Exception('不能加入自己創建的房間');
    }
    
    // 加入房間
    if ($room->joinRoom($roomCode, $_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'message' => '成功加入房間',
            'code' => $roomCode
        ]);
    } else {
        throw new Exception('加入房間失敗');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
