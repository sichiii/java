<?php
require_once __DIR__ . '/../config/database.php';

class Game {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function createGame($data) {
        $sql = "INSERT INTO games (
                    room_id, 
                    black_player, 
                    status, 
                    board_state,
                    current_turn,
                    created_at
                ) VALUES (
                    :room_id, 
                    :black_player, 
                    :status,
                    :board_state,
                    :current_turn,
                    NOW()
                )";
                
        $emptyBoard = array_fill(0, 15, array_fill(0, 15, 0));
        
        $params = [
            ':room_id' => $data['room_id'],
            ':black_player' => $data['black_player'],
            ':status' => $data['status'],
            ':board_state' => json_encode($emptyBoard),
            ':current_turn' => $data['black_player']  // 黑子先手
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    public function getGameByRoomId($roomId) {
        $sql = "SELECT * FROM games WHERE room_id = :room_id ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($sql, [':room_id' => $roomId]);
        return $result ? $result[0] : null;
    }
    
    public function makeMove($code, $userId, $row, $col) {
        // 獲取遊戲資訊
        $room = new Room();
        $roomData = $room->getRoomByCode($code);
        $gameData = $this->getGameByRoomId($roomData['id']);
        
        // 檢查是否輪到該玩家
        if ($gameData['current_turn'] != $userId) {
            return ['success' => false, 'message' => '還沒輪到您下棋'];
        }
        
        // 獲取並更新棋盤狀態
        $boardState = json_decode($gameData['board_state'], true);
        if ($boardState[$row][$col] !== 0) {
            return ['success' => false, 'message' => '此位置已有棋子'];
        }
        
        // 判斷黑白棋
        $piece = ($userId == $gameData['black_player']) ? 1 : 2;
        $boardState[$row][$col] = $piece;
        
        // 檢查是否獲勝
        if ($this->checkWin($boardState, $row, $col, $piece)) {
            $this->updateGameStatus($gameData['id'], 'finished', $userId);
            return ['success' => true, 'message' => '遊戲結束，您獲勝了！', 'status' => 'finished'];
        }
        
        // 更新遊戲狀態
        $nextTurn = ($userId == $roomData['creator_id']) ? $roomData['joiner_id'] : $roomData['creator_id'];
        $this->updateGameState($gameData['id'], $boardState, $nextTurn);
        
        return ['success' => true, 'message' => '移動成功'];
    }
    
    public function checkWin($boardState, $row, $col, $piece) {
        $directions = [
            [0, 1],   // 水平
            [1, 0],   // 垂直
            [1, 1],   // 對角線
            [1, -1]   // 反對角線
        ];
        
        foreach ($directions as $dir) {
            $count = 1;
            
            // 正向檢查
            for ($i = 1; $i < 5; $i++) {
                $newRow = $row + $dir[0] * $i;
                $newCol = $col + $dir[1] * $i;
                if (!isset($boardState[$newRow][$newCol]) || $boardState[$newRow][$newCol] !== $piece) break;
                $count++;
            }
            
            // 反向檢查
            for ($i = 1; $i < 5; $i++) {
                $newRow = $row - $dir[0] * $i;
                $newCol = $col - $dir[1] * $i;
                if (!isset($boardState[$newRow][$newCol]) || $boardState[$newRow][$newCol] !== $piece) break;
                $count++;
            }
            
            if ($count >= 5) return true;
        }
        return false;
    }
    
    private function updateGameState($gameId, $boardState, $nextTurn) {
        $sql = "UPDATE games 
                SET board_state = :board_state,
                    current_turn = :next_turn,
                    updated_at = NOW()
                WHERE id = :game_id";
                
        return $this->db->execute($sql, [
            ':board_state' => json_encode($boardState),
            ':next_turn' => $nextTurn,
            ':game_id' => $gameId
        ]);
    }
    
    private function updateGameStatus($gameId, $status, $winnerId = null) {
        $sql = "UPDATE games 
                SET status = :status,
                    winner_id = :winner_id,
                    updated_at = NOW()
                WHERE id = :game_id";
                
        return $this->db->execute($sql, [
            ':status' => $status,
            ':winner_id' => $winnerId,
            ':game_id' => $gameId
        ]);
    }
} 