<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['code'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

require_once '../../app/models/Room.php';

try {
    $room = new Room();
    $result = $room->requestRematch($data['code'], $_SESSION['user_id']);
    
    // 如果雙方都同意，返回特殊狀態
    if ($result['status'] === 'restarted') {
        echo json_encode([
            'success' => true,
            'status' => 'restarted',
            'message' => '遊戲重新開始'
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