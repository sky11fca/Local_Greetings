<?php
class UserModel{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function login($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password_hash'])){
            return $user;
        }
        return false;
    }

    public function register($username, $email, $password){
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = :email");

        $stmt->execute(['email' => $email]);
        if($stmt->fetch()){
            throw new Exception('Email already exists');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO Users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $stmt->execute(['username' => $username, 'email' => $email, 'password_hash' => $hashedPassword]);
        return $this->db->lastInsertId();
    }
}