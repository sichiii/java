<?php
session_start();
require_once '../../app/models/Room.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['code']) || !isset($data['role'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

try {
    $room = new Room();
    $result = $room->selectRole($data['code'], $_SESSION['user_id'], $data['role']);
    
    echo json_encode([
        'success' => true,
        'role' => $data['role']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 