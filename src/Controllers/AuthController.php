<?php
class AuthController
{
    private $userModel;

    public function __construct($userModel){
        $this->userModel = $userModel;
    }

    public function showLogin()
    {
        require_once 'src/View/auth/login.php';
    }

    public function showRegister()
    {
        require_once 'src/View/auth/register.php';
    }


    public function login()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $user = $this->userModel->login($username, $password);

            if($user){
                setcookie('user_id', $user['user_id'], time() + (86400 * 30), "/");
                setcookie('username', $user['username'], time() + (86400 * 30), "/");

                header('Location: /home');
                exit();
            }
            else{
                $error = "Invalid username or password";
                require_once 'src/View/auth/login.php';
            }
        }
    }

    public function register(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirmPassword']);

            if($password === $confirmPassword){
                $error = "Passwords do not match";
                require_once 'src/View/auth/register.php';
                return;
            }

            if($this->userModel->register($username, $email, $password)){
                header('Location: /login');
                exit();
            }
            else {
                $error = "Username already exists";
                require_once 'src/View/auth/register.php';
            }
        }
    }

    public function logout(){
        setcookie('user_id', '', time() - 3600, "/");
        setcookie('username', '', time() - 3600, "/");
        header('Location: /login');
        exit();
    }
}