<?php
// Set page-specific variables
$pageTitle = "Edit Profile - Local Greeting";
$currentPage = "profile";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/auth.css"
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/profile-edit.js"
];

// Add inline styles to override any conflicting CSS
$inlineCSS = "
    .form-group label {
        display: block !important;
        visibility: visible !important;
    }
";

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main class="profile-page">
    <section id="profile-edit-section">
        <div class="container mt-5" style="margin-top: 60px; max-width: 480px;">
            <div class="auth-container">
                <div class="auth-box">
                    <h2>Edit Your Profile</h2>
                    <form id="profileEditForm">
                        <div class="form-group">
                            <label for="username" class="force-visible-label">Username</label>
                            <input type="text" id="username" name="username" placeholder="Your Username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <label for="old_password">Current Password (optional)</label>
                            <input type="password" id="old_password" name="old_password" placeholder="Current Password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password (optional)</label>
                            <input type="password" id="new_password" name="new_password" placeholder="New Password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password">Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm New Password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 