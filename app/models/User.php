<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function login($username, $password) {
        try {
            $sql = "SELECT * FROM users WHERE username = :username";
            $result = $this->db->query($sql, [':username' => $username]);
            
            if (empty($result)) {
                return ['success' => false, 'message' => '用戶名或密碼錯誤'];
            }
            
            $user = $result[0];
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => '用戶名或密碼錯誤'];
            }
            
            return [
                'success' => true,
                'message' => '登入成功',
                'user' => $user
            ];
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return ['success' => false, 'message' => '登入失敗'];
        }
    }
    
    public function register($username, $email, $password) {
        try {
            // 檢查用戶名是否已存在
            $sql = "SELECT id FROM users WHERE username = :username";
            $result = $this->db->query($sql, [':username' => $username]);
            
            if (!empty($result)) {
                return ['success' => false, 'message' => '用戶名已存在'];
            }
            
            // 檢查郵箱是否已存在
            $sql = "SELECT id FROM users WHERE email = :email";
            $result = $this->db->query($sql, [':email' => $email]);
            
            if (!empty($result)) {
                return ['success' => false, 'message' => '郵箱已被使用'];
            }
            
            // 創建新用戶
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, rating, created_at) 
                    VALUES (:username, :email, :password, 1500, NOW())";
            
            $this->db->insert($sql, [
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            
            return ['success' => true, 'message' => '註冊成功'];
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => '註冊失敗'];
        }
    }
    
    public function getUserById($id) {
        try {
            $sql = "SELECT id, username, email, rating FROM users WHERE id = :id";
            $result = $this->db->query($sql, [':id' => $id]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log('Get user error: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getLeaderboard() {
        try {
            $sql = "SELECT 
                        username,
                        rating,
                        wins,
                        losses,
                        (wins + losses) as total_games,
                        ROUND(IFNULL(wins * 100.0 / NULLIF(wins + losses, 0), 0), 1) as win_rate
                    FROM users
                    ORDER BY rating DESC
                    LIMIT 100";
            
            return $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Get leaderboard error: ' . $e->getMessage());
            return [];
        }
    }
    
    public function updateRating($userId, $newRating) {
        try {
            $sql = "UPDATE users 
                    SET rating = :rating 
                    WHERE id = :user_id";
            
            return $this->db->execute($sql, [
                ':rating' => $newRating,
                ':user_id' => $userId
            ]);
        } catch (Exception $e) {
            error_log('Update rating error: ' . $e->getMessage());
            return false;
        }
    }
}
