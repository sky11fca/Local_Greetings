<?php
class TemplateHelper {
    
    /**
     * Get the base URL for assets
     */
    public static function asset($path) {
        return "/local_greeter/public/" . ltrim($path, '/');
    }
    
    /**
     * Get the base URL for the application
     */
    public static function url($path = '') {
        return "/local_greeter/" . ltrim($path, '/');
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_COOKIE['userData']);
    }
    
    /**
     * Get user data from cookie
     */
    public static function getUserData() {
        if (self::isLoggedIn()) {
            $userData = $_COOKIE['userData'];
            return json_decode($userData, true);
        }
        return null;
    }
    
    /**
     * Escape HTML output
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date for display
     */
    public static function formatDate($date, $format = 'Y-m-d H:i') {
        return date($format, strtotime($date));
    }
    
    /**
     * Get current page name
     */
    public static function getCurrentPage() {
        return $_GET['page'] ?? 'home';
    }
    
    /**
     * Check if current page matches
     */
    public static function isCurrentPage($page) {
        return self::getCurrentPage() === $page;
    }
    
    /**
     * Generate active class for navigation
     */
    public static function activeClass($page) {
        return self::isCurrentPage($page) ? 'active' : '';
    }
}
?> 