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
$row = $data['row'] ?? null;
$col = $data['col'] ?? null;

if (!$code || $row === null || $col === null) {
    echo json_encode(['success' => false, 'message' => '參數錯誤']);
    exit;
}

$room = new Room();
$result = $room->updateGame($code, $_SESSION['user_id'], $row, $col);
echo json_encode($result); 