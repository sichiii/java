<?php
class GameController extends BaseController {
    private $username;
    
    public function __construct() {
        // 檢查用戶是否已登入
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/java/auth/login');
            return;
        }
        // 保存用戶名
        $this->username = $_SESSION['username'] ?? '訪客';
    }
    
    public function home() {
        $data = [
            'username' => $this->username,
            'title' => '選擇遊戲模式'
        ];
        $this->render('game/home', $data);
    }
    
    public function singlePlayer() {
        $data = [
            'username' => $this->username,
            'title' => '單人對戰 AI'
        ];
        $this->render('game/single_player', $data);
    }
    
    public function twoPlayer() {
        $data = [
            'username' => $this->username,
            'title' => '雙人對戰'
        ];
        $this->render('game/two_player', $data);
    }
} 