<?php
require 'vendor/autoload.php';
require_once __DIR__ . '/app/models/Room.php';
require_once __DIR__ . '/app/models/User.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class GameServer implements MessageComponentInterface {
    protected $clients;
    protected $rooms;
    protected $roomModel;
    protected $userModel;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms = [];
        $this->roomModel = new Room();
        $this->userModel = new User();
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $conn->playerData = new \stdClass();
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->playerData->roomCode)) {
            $this->handlePlayerDisconnect($conn);
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        echo "Received message: " . $msg . "\n";
        
        switch($data->type) {
            case 'join':
                $this->handleJoin($from, $data);
                break;
            case 'select_color':
                $this->handleColorSelection($from, $data);
                break;
            case 'move':
                $this->handleMove($from, $data);
                break;
            case 'rematch':
                $this->handleRematch($from, $data);
                break;
        }
    }
    
    protected function handleJoin($conn, $data) {
        $conn->playerData->roomCode = $data->roomCode;
        $conn->playerData->userId = $data->userId;
        
        if (!isset($this->rooms[$data->roomCode])) {
            $this->rooms[$data->roomCode] = [
                'players' => new \SplObjectStorage,
                'gameState' => [
                    'board' => array_fill(0, 15, array_fill(0, 15, 0)),
                    'currentTurn' => null,
                    'status' => 'waiting',
                    'blackPlayer' => null,
                    'whitePlayer' => null,
                    'colorSelections' => []
                ]
            ];
        }
        
        $this->rooms[$data->roomCode]['players']->attach($conn);
        
        // 如果房間已有兩個玩家，開始選擇顏色
        if ($this->rooms[$data->roomCode]['players']->count() == 2) {
            $this->broadcastToRoom($data->roomCode, [
                'type' => 'player_joined',
                'message' => '對手已加入，請選擇顏色'
            ]);
        }
    }
    
    protected function handleColorSelection($conn, $data) {
        $room = &$this->rooms[$data->roomCode];
        $room['gameState']['colorSelections'][$data->userId] = $data->color;
        
        // 檢查是否有衝突
        $selections = $room['gameState']['colorSelections'];
        if (count($selections) == 2) {
            $players = array_keys($selections);
            if ($selections[$players[0]] === $selections[$players[1]]) {
                // 如果選擇相同，隨機分配
                shuffle($players);
                $room['gameState']['blackPlayer'] = $players[0];
                $room['gameState']['whitePlayer'] = $players[1];
            } else {
                // 根據選擇分配顏色
                foreach ($players as $playerId) {
                    if ($selections[$playerId] === 'black') {
                        $room['gameState']['blackPlayer'] = $playerId;
                    } else {
                        $room['gameState']['whitePlayer'] = $playerId;
                    }
                }
            }
            
            // 更新資料庫
            $this->roomModel->setPlayerColor($data->roomCode, 
                $room['gameState']['blackPlayer'], 'black');
            $this->roomModel->setPlayerColor($data->roomCode, 
                $room['gameState']['whitePlayer'], 'white');
            
            // 開始遊戲
            $room['gameState']['status'] = 'playing';
            $room['gameState']['currentTurn'] = 'black';
            $this->roomModel->startGame($data->roomCode);
            
            // 通知玩家遊戲開始
            $this->broadcastToRoom($data->roomCode, [
                'type' => 'game_start',
                'blackPlayer' => $room['gameState']['blackPlayer'],
                'whitePlayer' => $room['gameState']['whitePlayer']
            ]);
        }
    }
    
    protected function handleMove($conn, $data) {
        $room = &$this->rooms[$data->roomCode];
        if ($room['gameState']['status'] !== 'playing') return;
        
        $isBlackTurn = $room['gameState']['currentTurn'] === 'black';
        $currentPlayerId = $isBlackTurn ? 
            $room['gameState']['blackPlayer'] : 
            $room['gameState']['whitePlayer'];
        
        if ($data->userId !== $currentPlayerId) return;
        
        // 更新棋盤
        $room['gameState']['board'][$data->row][$data->col] = 
            $isBlackTurn ? 1 : 2;
        
        // 檢查勝利
        $winner = $this->checkWinner(
            $room['gameState']['board'], 
            $data->row, 
            $data->col
        );
        
        $this->broadcastToRoom($data->roomCode, [
            'type' => 'move',
            'row' => $data->row,
            'col' => $data->col,
            'color' => $isBlackTurn ? 'black' : 'white',
            'nextTurn' => $isBlackTurn ? 'white' : 'black'
        ]);
        
        if ($winner) {
            $winnerId = $isBlackTurn ? 
                $room['gameState']['blackPlayer'] : 
                $room['gameState']['whitePlayer'];
            
            $room['gameState']['status'] = 'ended';
            $this->roomModel->endGame($data->roomCode, $winnerId);
            
            $this->broadcastToRoom($data->roomCode, [
                'type' => 'game_end',
                'winner' => $winnerId,
                'message' => '遊戲結束！'
            ]);
        } else {
            $room['gameState']['currentTurn'] = 
                $isBlackTurn ? 'white' : 'black';
        }
    }
    
    protected function handleRematch($conn, $data) {
        $room = &$this->rooms[$data->roomCode];
        
        // 重置遊戲狀態
        $room['gameState'] = [
            'board' => array_fill(0, 15, array_fill(0, 15, 0)),
            'currentTurn' => null,
            'status' => 'waiting',
            'blackPlayer' => null,
            'whitePlayer' => null,
            'colorSelections' => []
        ];
        
        $this->broadcastToRoom($data->roomCode, [
            'type' => 'rematch_start',
            'message' => '新的對局開始，請選擇顏色'
        ]);
    }
    
    protected function handlePlayerDisconnect($conn) {
        $roomCode = $conn->playerData->roomCode;
        if (!isset($this->rooms[$roomCode])) return;
        
        $room = &$this->rooms[$roomCode];
        $room['players']->detach($conn);
        
        if ($room['gameState']['status'] === 'playing') {
            // 找出留下的玩家
            $remainingPlayer = null;
            foreach ($room['players'] as $player) {
                $remainingPlayer = $player;
                break;
            }
            
            if ($remainingPlayer) {
                $winnerId = $remainingPlayer->playerData->userId;
                $this->roomModel->endGame($roomCode, $winnerId);
                
                $this->broadcastToRoom($roomCode, [
                    'type' => 'player_left',
                    'userId' => $conn->playerData->userId,
                    'winner' => $winnerId,
                    'message' => '對手離開了遊戲，你獲勝了！'
                ]);
            }
        }
        
        // 更新資料庫中的玩家狀態
        $this->roomModel->handlePlayerLeft($roomCode, $conn->playerData->userId);
        
        // 如果房間空了，清理房間
        if ($room['players']->count() === 0) {
            unset($this->rooms[$roomCode]);
        }
    }
    
    protected function checkWinner($board, $row, $col) {
        $directions = [[1,0], [0,1], [1,1], [1,-1]];
        $currentPlayer = $board[$row][$col];
        
        foreach ($directions as $dir) {
            $count = 1;
            
            // 檢查兩個方向
            for ($i = -1; $i <= 1; $i += 2) {
                $r = $row + $dir[0] * $i;
                $c = $col + $dir[1] * $i;
                while ($r >= 0 && $r < 15 && $c >= 0 && $c < 15 && 
                       $board[$r][$c] === $currentPlayer) {
                    $count++;
                    $r += $dir[0] * $i;
                    $c += $dir[1] * $i;
                }
            }
            
            if ($count >= 5) return true;
        }
        return false;
    }
    
    protected function broadcastToRoom($roomCode, $message) {
        if (!isset($this->rooms[$roomCode])) return;
        
        foreach ($this->rooms[$roomCode]['players'] as $client) {
            $client->send(json_encode($message));
        }
    }
}

// 啟動 WebSocket 服務器
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new GameServer()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
$server->run(); 