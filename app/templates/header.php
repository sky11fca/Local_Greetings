<?php
// Include the template helper
require_once __DIR__ . '/../helpers/TemplateHelper.php';

// Get the page title from the calling script, default to "Local Greeting"
$pageTitle = $pageTitle ?? "Local Greeting";
$currentPage = $currentPage ?? "home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo TemplateHelper::escape($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo TemplateHelper::asset('css/style.css'); ?>">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo TemplateHelper::escape($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($inlineCSS)): ?>
        <style><?php echo $inlineCSS; ?></style>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><a href="<?php echo TemplateHelper::url(); ?>">Local Greeting</a></div>
            <button class="hamburger-menu" aria-label="Toggle Navigation">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo TemplateHelper::url(); ?>" class="<?php echo TemplateHelper::activeClass('home'); ?>">Home</a></li>
                    <li><a href="<?php echo TemplateHelper::url('events'); ?>" class="<?php echo TemplateHelper::activeClass('events'); ?>">Events</a></li>
                </ul>
                <div class="auth-buttons">
                    <!-- Login button (shown when not logged in) -->
                    <a href="<?php echo TemplateHelper::url('login'); ?>" class="btn btn-primary profile-link" id="login-btn">Login</a>
                    
                    <!-- User navigation (shown when logged in) -->
                    <div class="user-nav hidden" id="user-nav">
                        <a href="<?php echo TemplateHelper::url('account'); ?>" class="btn btn-primary profile-link">Profile</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <script>
        // Helper to decode user info from JWT
        function getUserFromJWT() {
            const token = localStorage.getItem('jwt_token');
            if (!token) return null;
            try {
                const payload = JSON.parse(atob(token.split('.')[1]));
                return payload.data || null;
            } catch (e) {
                return null;
            }
        }

        // Helper to check if JWT is valid and not expired
        function isJWTValid() {
            const token = localStorage.getItem('jwt_token');
            if (!token) return false;
            try {
                const payload = JSON.parse(atob(token.split('.')[1]));
                // Check for expiration
                if (!payload.exp || Date.now() >= payload.exp * 1000) {
                    localStorage.removeItem('jwt_token');
                    return false;
                }
                return true;
            } catch (e) {
                localStorage.removeItem('jwt_token');
                return false;
            }
        }

        // Check authentication status and update UI
        function updateAuthUI() {
            const token = localStorage.getItem('jwt_token');
            const userData = getUserFromJWT();
            const loginBtn = document.getElementById('login-btn');
            const userNav = document.getElementById('user-nav');
            
            if (token && userData && isJWTValid()) {
                // User is logged in
                if (loginBtn) loginBtn.classList.add('hidden');
                if (userNav) userNav.classList.remove('hidden');
            } else {
                // User is not logged in or token is expired
                if (loginBtn) loginBtn.classList.remove('hidden');
                if (userNav) userNav.classList.add('hidden');
            }
        }
        
        // Update UI on page load
        document.addEventListener('DOMContentLoaded', updateAuthUI);
        
        // Update UI when storage changes (for multi-tab support)
        window.addEventListener('storage', (e) => {
            if (e.key === 'jwt_token') {
                updateAuthUI();
            }
        });
    </script>
</body>
</html> 