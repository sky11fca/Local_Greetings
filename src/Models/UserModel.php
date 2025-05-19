<?php
class UserModel{
    private $db;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function register($username, $email, $password){
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO Users (username, email, password_hash) VALUES (:username, :email, :password_hash)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $hashedPassword);
        return $stmt->execute();
    }

    public function login($username, $password){
        $query = "SELECT * FROM Users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($user && password_verify($password, $user['password_hash'])){
            if(password_verify($password, $user['password_hash'])){
                return $user;
            }
        }
        return false;
    }

    public function getUserById($id){
        $query = "SELECT * FROM Users where user_id= :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}