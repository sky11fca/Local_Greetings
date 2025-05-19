<?php
class Database{
    private $host = '127.0.0.1';
    private $dbName = 'local_greeter';
    private $user = 'bobby';
    private $pass = 'bobbydb3002';
    private $conn;

    public function connect()
    {
        $this->conn = null;

        try{
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbName", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e){
            echo 'Connection failed: '.$e->getMessage();
        }
    }
}