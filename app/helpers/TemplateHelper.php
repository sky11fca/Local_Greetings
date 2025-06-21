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
        // Check for Authorization header in case of API requests
        $headers = getallheaders();
        if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
            return true;
        }
        
        // For server-side checks, we can't access sessionStorage, so we'll rely on the API
        // The frontend will handle the actual authentication state
        return false;
    }
    
    /**
     * Get user data from JWT token
     * Note: This requires the token to be passed or stored server-side
     */
    public static function getUserData() {
        // For server-side rendering, we can't access the JWT token directly
        // The frontend JavaScript will handle user data from sessionStorage
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