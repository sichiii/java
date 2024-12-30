<?php
require 'vendor/autoload.php';
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;

class GameServer implements MessageComponentInterface {
    protected $clients;
    protected $games;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->games = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        
        switch ($data->type) {
            case 'join':
                $this->handleJoin($from, $data);
                break;
            case 'move':
                $this->handleMove($from, $data);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function handleJoin($conn, $data) {
        if (!isset($this->games[$data->gameCode])) {
            $this->games[$data->gameCode] = [
                'players' => [$conn->resourceId => $data->playerId],
                'connections' => [$conn->resourceId => $conn]
            ];
        } else {
            $this->games[$data->gameCode]['players'][$conn->resourceId] = $data->playerId;
            $this->games[$data->gameCode]['connections'][$conn->resourceId] = $conn;
            
            // 通知雙方遊戲開始
            foreach ($this->games[$data->gameCode]['connections'] as $client) {
                $client->send(json_encode([
                    'type' => 'gameStart',
                    'players' => array_values($this->games[$data->gameCode]['players'])
                ]));
            }
        }
    }

    protected function handleMove($from, $data) {
        if (isset($this->games[$data->gameCode])) {
            foreach ($this->games[$data->gameCode]['connections'] as $client) {
                if ($from !== $client) {
                    $client->send(json_encode([
                        'type' => 'move',
                        'row' => $data->row,
                        'col' => $data->col,
                        'player' => $data->player
                    ]));
                }
            }
        }
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new GameServer()
        )
    ),
    8080
);

$server->run(); 