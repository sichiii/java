<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

require_once '../../app/models/Room.php';

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';

if (!$code) {
    echo json_encode(['success' => false, 'message' => '參數錯誤']);
    exit;
}

$room = new Room();
$result = $room->leaveRoom($code, $_SESSION['user_id']);
echo json_encode($result); 