<?php
// Set page-specific variables
$pageTitle = "Login - Local Greeting";
$currentPage = "login";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/auth.css"
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/auth.js"
];

// Inline script for user authentication check
$inlineScripts = "
    document.addEventListener('DOMContentLoaded', () => {
        function getCookie(name) {
            const value = `; \${document.cookie}`;
            const parts = value.split(`; \${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        const userData = getCookie('userData');
        if(userData){
            window.location.href = '/local_greeter/account';
        }
    });
";

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <div class="container mt-5">
        <div class="auth-container">
            <div class="auth-box">
                <h2>Log In</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" placeholder="Email address" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                </form>
                <p class="auth-switch">Don't have an account? <a href="/local_greeter/register">Register</a></p>
            </div>
        </div>
    </div>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 