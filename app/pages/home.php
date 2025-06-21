<?php
// Set page-specific variables
$pageTitle = "Local Greeting - Discover Sports in Iași";
$currentPage = "home";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/home.css",
    "/local_greeter/public/css/map.css",
    "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
];

// Inline CSS for map
$inlineCSS = "
    #map { 
        height: 500px; 
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
";

// Additional scripts for this page
$additionalScripts = [
    "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js",
    "/local_greeter/public/js/map.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../helpers/TemplateHelper.php';
?>

<main>
    <section id="hero">
        <div class="container">
            <h1>DISCOVER. ENGAGE. PLAY.</h1>
            <p>Your ultimate platform for connecting with sports enthusiasts and exploring vibrant sports fields across Iași. Let's make every game an unforgettable experience!</p>
            <div class="hero-buttons">
                <!-- Sign Up button (shown when not logged in) -->
                <a href="/local_greeter/register" class="btn btn-primary" id="signup-btn">Sign Up Today!</a>
                <!-- Explore Events button (always shown) -->
                <a href="/local_greeter/events" class="btn btn-secondary">Explore Events</a>
            </div>
        </div>
    </section>

    <section id="map-section">
        <div class="container">
            <h2>Find Sports Fields Near You</h2>
            <div id="map"></div>
            <a href="/local_greeter/fields" class="btn btn-primary">Browse All Sports Fields</a>
        </div>
    </section>
</main>

<script>
// Check authentication status and update homepage UI
function updateHomepageUI() {
    const token = sessionStorage.getItem('jwt_token');
    const userData = sessionStorage.getItem('user');
    const signupBtn = document.getElementById('signup-btn');
    
    if (token && userData) {
        // User is logged in - hide signup button
        if (signupBtn) {
            signupBtn.style.display = 'none';
        }
    } else {
        // User is not logged in - show signup button
        if (signupBtn) {
            signupBtn.style.display = 'inline-block';
        }
    }
}

// Update UI on page load
document.addEventListener('DOMContentLoaded', updateHomepageUI);

// Update UI when storage changes (for multi-tab support)
window.addEventListener('storage', (e) => {
    if (e.key === 'jwt_token' || e.key === 'user') {
        updateHomepageUI();
    }
});
</script>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 