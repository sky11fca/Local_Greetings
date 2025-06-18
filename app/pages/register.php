<?php
// Set page-specific variables
$pageTitle = "Register - Local Greeting";
$currentPage = "register";

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
                <h2>Register</h2>
                <form id="registerForm">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" placeholder="Email address" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                <p class="auth-switch">Already have an account? <a href="/local_greeter/login">Log In</a></p>
            </div>
        </div>
    </div>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 