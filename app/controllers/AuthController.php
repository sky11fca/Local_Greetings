<?php
class AuthController{
    private $userModel;

    public function __construct(){
        $this->userModel = new UserModel();
    }

    public function showLogin(){
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(){
        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = $this->userModel->login($username, $password);

        if($user){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ?action=home');
            exit();
        }
        else{
            $error= 'Invalid username or password';
            require __DIR__ . '/../views/auth/login.php';
        }
    }

    public function showRegister(){
        require __DIR__ . '/../views/auth/register.php';
    }

    public function register(){
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if($this->userModel->register($username, $email, $password)){
            header('Location: ?action=login');
        }
        else{
            $error = 'Registration failed';
            require __DIR__ . '/../views/auth/register.php';
        }
    }
    public function logout(){
        session_destroy();
        header('Location: ?action=landing');
    }
}