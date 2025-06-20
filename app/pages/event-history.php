<?php
// Set page-specific variables
$pageTitle = "My Event History - Iași Sports Network";
$currentPage = "event-history";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/events.css" // Reuse the events page styling
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/event-history.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <section id="event-history-section">
        <div class="container">
            <h2>My Event History</h2>
            <p>Here are the past events you have participated in.</p>
            <div id="event-history-grid" class="event-grid">
                <!-- Past event cards will be loaded here by JavaScript -->
            </div>
            <div id="history-pagination" class="pagination">
                <!-- Pagination controls will go here -->
            </div>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 