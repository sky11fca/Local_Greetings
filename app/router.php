<?php
class Router {
    private $routes = [];
    
    public function __construct() {
        // Define routes and their corresponding page files
        $this->routes = [
            '' => 'home',
            'home' => 'home',
            'events' => 'events',
            'fields' => 'fields',
            'login' => 'login',
            'register' => 'register',
            'account' => 'account',
            'profile' => 'profile',
            'create-event' => 'create-event',
            'edit-event' => 'edit-event',
            'event-history' => 'event-history',
            'admin' => 'admin',
            'admin-login' => 'admin-login'
        ];
    }
    
    public function route($page) {
        $page = strtolower($page);
        
        if (isset($this->routes[$page])) {
            $pageFile = $this->routes[$page];
            $this->loadPage($pageFile);
        } else {
            $this->load404();
        }
    }
    
    private function loadPage($pageName) {
        $pageFile = __DIR__ . "/pages/{$pageName}.php";
        
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            $this->load404();
        }
    }
    
    private function load404() {
        http_response_code(404);
        //$pageTitle = "404 - Page Not Found";
        //$currentPage = "404";
        require __DIR__ . "/pages/not-found.php";
    }
}
?> 