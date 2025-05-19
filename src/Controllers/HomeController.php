<?php
    class HomeController{
        private $userModel;

        public function __construct($userModel){
            $this->userModel = $userModel;
        }

        public function index(){
            if(!isset($_COOKIE['user_id'])){
                header('Location: /login');
                exit();
            }
            $user = $this->userModel->getUserById($_COOKIE['user_id']);
            if(!$user)
            {
                setcookie('user_id', '', time() - 3600, "/");
                setcookie('username', '', time() - 3600, "/");
                header('Location: /login');
                exit();
            }

            require_once 'src/View/home/index.php';
        }
    }