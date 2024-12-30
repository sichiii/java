<?php
require_once BASE_PATH . '/app/models/User.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        try {
            $this->userModel = new User();
        } catch (Exception $e) {
            die('系統錯誤：' . $e->getMessage());
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                $user = $this->userModel->login($username, $password);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $this->redirect('/java/game/home');
                }
                
                $this->render('auth/login', ['error' => '用戶名或密碼錯誤']);
            } catch (Exception $e) {
                $this->render('auth/login', ['error' => $e->getMessage()]);
            }
        } else {
            $this->render('auth/login');
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $email = $_POST['email'] ?? '';

                $result = $this->userModel->register($username, $password, $email);
                if ($result['success']) {
                    $this->redirect('/java/auth/login');
                } else {
                    $this->render('auth/register', ['error' => $result['message']]);
                }
            } catch (Exception $e) {
                $this->render('auth/register', ['error' => $e->getMessage()]);
            }
        } else {
            $this->render('auth/register');
        }
    }

    public function showLogin() {
        $this->render('auth/login');
    }

    public function showRegister() {
        $this->render('auth/register');
    }

    public function logout() {
        session_destroy();
        $this->redirect('/java');
    }
} 