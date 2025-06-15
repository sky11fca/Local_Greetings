<?php
class UserModel{
    private $conn;
    private $table = 'Users';

    public $user_id;
    public $username;
    public $email;
    public $password_hash;
    public $reputation_score;
    public $is_admin;
    public function __construct($db)
    {
        $this->conn = $db;
    }

//    public function login($username, $password){
//        $stmt = $this->db->prepare("SELECT * FROM Users WHERE username = ?");
//        $stmt->execute([$username]);
//
//        $user = $stmt->fetch();
//
//        if($user && password_verify($password, $user['password_hash'])){
//            return $user;
//        }
//        return false;
//    }

    public function getUserByEmail($email)
    {
        $query = "SELECT * FORM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }

    public function getUserByUsername($username)
    {
        $query = "SELECT * FORM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt;
    }

    public function addUser($username, $email, $hashedPassword){
        //$stmt = $this->conn->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
        //return $stmt->execute([$username, $email, $hashedPassword]);

        $query = "INSERT INTO {$this->table} (username, email, password_hash) VALUES (:username, :email, :password_hash)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $hashedPassword);
        return $stmt->execute();
    }

    public function updateProfile($userId, $data) {
        $setClauses = [];
        $params = [];

        if (isset($data['username'])) {
            $setClauses[] = 'username = ?';
            $params[] = $data['username'];
        }
        if (isset($data['email'])) {
            $setClauses[] = 'email = ?';
            $params[] = $data['email'];
        }
        if (isset($data['password'])) {
            $setClauses[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($setClauses)) {
            return false; // No data to update
        }

        $sql = "UPDATE Users SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
        $params[] = $userId;

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}