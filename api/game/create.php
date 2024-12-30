<?php
session_start();
require_once '../../app/models/Room.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

try {
    $room = new Room();
    
    // 生成6位隨機房間代碼
    do {
        $code = '';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
    } while ($room->checkRoomExists($code));
    
    // 創建房間
    $roomData = [
        'code' => $code,
        'creator_id' => $_SESSION['user_id']
    ];
    
    $roomId = $room->createRoom($roomData);
    
    if ($roomId) {
        echo json_encode([
            'success' => true,
            'code' => $code,
            'message' => '房間創建成功'
        ]);
    } else {
        throw new Exception('創建房間失敗');
    }
    
} catch (Exception $e) {
    error_log('Room creation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '創建房間失敗：' . $e->getMessage()
    ]);
}
