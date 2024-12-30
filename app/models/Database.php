<?php
class Database {
    private $connection;
    
    public function __construct() {
        $host = 'localhost';
        $dbname = 'gomoku';
        $username = 'root';
        $password = '';
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("連接資料庫失敗：" . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }

    public function insert($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log('Database insert error: ' . $e->getMessage());
            throw new Exception('資料庫插入錯誤');
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database query error: ' . $e->getMessage());
            throw new Exception('資料庫查詢錯誤');
        }
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Database execute error: ' . $e->getMessage());
            throw new Exception('資料庫執行錯誤');
        }
    }
} 