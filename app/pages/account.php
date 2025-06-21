<?php
// Set page-specific variables
$pageTitle = "My Account - Local Greeting";
$currentPage = "account";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/auth.css"
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/profile.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <section id="account-details-section">
        <div class="container mt-5">
            <div class="auth-container">
                <div id="logged-in-account-content" class="auth-box hidden">
                    <h2>My Account Details</h2>
                    <div class="account-info">
                        <p><strong>Name:</strong> <span id="account-name"></span></p>
                        <p><strong>Email:</strong> <span id="account-email"></span></p>
                        <!-- You can add more details here, e.g., joined events, created events -->
                    </div>
                    <a href="/local_greeter/profile" class="btn btn-primary btn-block mt-3">Edit Profile</a>
                    <a href="/local_greeter/event-history" class="btn btn-info btn-block mt-3">View My Event History</a>
                    <button id="logoutButton" class="btn btn-secondary btn-block mt-3">Log Out</button>
                </div>

                <div id="logged-out-account-content" class="auth-box hidden">
                    <h2>Welcome!</h2>
                    <p>Please log in or sign up to view your account details.</p>
                    <a href="/local_greeter/login" class="btn btn-primary btn-block mb-3">Log In</a>
                    <a href="/local_greeter/register" class="btn btn-secondary btn-block">Sign Up</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 