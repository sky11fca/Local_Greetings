<?php
class HomeController{
    public function index(){
        require __DIR__ . '/../views/landing/index.php';
    }

    public function home(){
        if(!isset($_SESSION['username'])){
            header('Location: ?action=login');
            exit();
        }

        require __DIR__ . '/../views/home/index.php';
    }
}