<?php
require_once __DIR__ . '/../config/database.php';

class Room {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createRoom($data) {
        try {
            $sql = "INSERT INTO rooms (code, creator_id, status, game_status) 
                    VALUES (:code, :creator_id, 'waiting', 'waiting')";
            
            return $this->db->insert($sql, [
                ':code' => $data['code'],
                ':creator_id' => $data['creator_id']
            ]);
        } catch (Exception $e) {
            error_log('Create room error: ' . $e->getMessage());
            throw new Exception('創建房間失敗');
        }
    }
    
    public function joinRoom($code, $player2_id) {
        try {
            $sql = "UPDATE rooms 
                    SET player2_id = :player2_id, 
                        status = 'selecting',
                        game_status = 'selecting'
                    WHERE code = :code 
                    AND status = 'waiting'";
            
            return $this->db->execute($sql, [
                ':code' => $code,
                ':player2_id' => $player2_id
            ]);
        } catch (Exception $e) {
            error_log('Join room error: ' . $e->getMessage());
            throw new Exception('加入房間失敗');
        }
    }
    
    public function setPlayerColor($code, $userId, $color) {
        try {
            $column = $color === 'black' ? 'black_player_id' : 'white_player_id';
            $sql = "UPDATE rooms SET $column = :user_id WHERE code = :code";
            return $this->db->execute($sql, [
                ':code' => $code,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            error_log('Set player color error: ' . $e->getMessage());
            throw new Exception('設置玩家顏色失敗');
        }
    }
    
    public function startGame($code) {
        try {
            $sql = "UPDATE rooms 
                    SET status = 'playing', 
                        game_status = 'playing',
                        last_move_time = NOW()
                    WHERE code = :code";
            return $this->db->execute($sql, [':code' => $code]);
        } catch (Exception $e) {
            error_log('Start game error: ' . $e->getMessage());
            throw new Exception('開始遊戲失敗');
        }
    }
    
    public function endGame($code, $winnerId) {
        try {
            $this->db->beginTransaction();
            
            // 更新房間狀態
            $sql = "UPDATE rooms 
                    SET status = 'ended', 
                        game_status = 'ended' 
                    WHERE code = :code";
            $this->db->execute($sql, [':code' => $code]);
            
            // 創建遊戲記錄
            $roomData = $this->getRoomByCode($code);
            $sql = "INSERT INTO games 
                    (room_id, black_player_id, white_player_id, winner_id, ended_at)
                    VALUES (:room_id, :black_id, :white_id, :winner_id, NOW())";
            $this->db->insert($sql, [
                ':room_id' => $roomData['id'],
                ':black_id' => $roomData['black_player_id'],
                ':white_id' => $roomData['white_player_id'],
                ':winner_id' => $winnerId
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('End game error: ' . $e->getMessage());
            throw new Exception('結束遊戲失敗');
        }
    }
    
    public function handlePlayerLeft($code, $userId) {
        try {
            $roomData = $this->getRoomByCode($code);
            if (!$roomData) return false;
            
            if ($roomData['status'] === 'playing') {
                // 如果遊戲正在進行，判定留下的玩家獲勝
                $winnerId = $roomData['creator_id'] == $userId ? 
                    $roomData['player2_id'] : $roomData['creator_id'];
                $this->endGame($code, $winnerId);
            } else {
                // 如果遊戲還沒開始，重置房間狀態
                $sql = "UPDATE rooms SET 
                        player2_id = CASE 
                            WHEN player2_id = :user_id THEN NULL 
                            ELSE player2_id 
                        END,
                        black_player_id = CASE 
                            WHEN black_player_id = :user_id THEN NULL 
                            ELSE black_player_id 
                        END,
                        white_player_id = CASE 
                            WHEN white_player_id = :user_id THEN NULL 
                            ELSE white_player_id 
                        END,
                        status = CASE 
                            WHEN creator_id = :user_id THEN 'ended'
                            ELSE 'waiting'
                        END,
                        game_status = CASE 
                            WHEN creator_id = :user_id THEN 'ended'
                            ELSE 'waiting'
                        END
                        WHERE code = :code";
                $this->db->execute($sql, [
                    ':code' => $code,
                    ':user_id' => $userId
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log('Handle player left error: ' . $e->getMessage());
            throw new Exception('處理玩家離開失敗');
        }
    }
    
    public function getRoomByCode($code) {
        try {
            $sql = "SELECT r.*, 
                    u1.username as creator_name,
                    u2.username as player2_name,
                    u3.username as black_player_name,
                    u4.username as white_player_name
                    FROM rooms r
                    LEFT JOIN users u1 ON r.creator_id = u1.id
                    LEFT JOIN users u2 ON r.player2_id = u2.id
                    LEFT JOIN users u3 ON r.black_player_id = u3.id
                    LEFT JOIN users u4 ON r.white_player_id = u4.id
                    WHERE r.code = :code";
            $result = $this->db->query($sql, [':code' => $code]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log('Get room error: ' . $e->getMessage());
            return null;
        }
    }
    
    public function checkRoomExists($code) {
        try {
            $sql = "SELECT id FROM rooms WHERE code = :code";
            $result = $this->db->query($sql, [':code' => $code]);
            return !empty($result);
        } catch (Exception $e) {
            error_log('Check room error: ' . $e->getMessage());
            return true; // 如果發生錯誤，假設房間已存在
        }
    }
}
