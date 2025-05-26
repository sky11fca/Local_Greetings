<?php
class UserModel{
    private $db;
    public function __construct()
    {
        try{
            $this->db = new PDO(
                'mysql:host=127.0.0.1;dbname=local_greeter',
                'bobby',
                'bobbydb3002',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        catch(PDOException $e){
            die("Database connection failed: " . $e->getMessage() . "");
        }
    }

    public function login($username, $password){
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password_hash'])){
            return $user;
        }
        return false;
    }

    public function register($username, $email, $password){
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashedPassword]);
    }
}