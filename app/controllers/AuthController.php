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
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            setcookie('session_auth', $user['user_id'] . ' ' . $user['username'], time() + 86400, '/');
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
        setcookie('session_auth', '', time() - 3600, '/');
        header('Location: ?action=landing');
    }
}