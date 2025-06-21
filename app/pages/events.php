<?php
// Set page-specific variables
$pageTitle = "Events List - Iași Sports Network";
$currentPage = "events";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/events.css"
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/events.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <section id="events-list">
        <div class="container">
            <div class="header-actions">
                <h2>All Sports Events</h2>
                <a href="/local_greeter/create-event" class="btn btn-primary">Create Event</a>
            </div>
            <div class="event-tabs">
                <button class="tab-btn active" data-tab="public">Public Events</button>
                <button class="tab-btn" data-tab="joined">My Joined Events</button>
                <button class="tab-btn" data-tab="created">My Created Events</button>
            </div>
            <div class="filters">
                <label for="search-event" class="sr-only">Search events</label>
                <input type="text" id="search-event" placeholder="Search events...">
                <label for="sport-type-filter" class="sr-only">Filter by sport type</label>
                <select id="sport-type-filter">
                    <option value="">All Sport Types</option>
                    <option value="football">Football</option>
                    <option value="basketball">Basketball</option>
                    <option value="tennis">Tennis</option>
                    <option value="volleyball">Volleyball</option>
                    <option value="multi-sport">Multi-Sport</option>
                </select>
                <button class="btn btn-primary" id="apply-filters">Apply Filters</button>
            </div>
            <div class="event-grid">
                <!-- Event cards will be loaded here by JavaScript -->
            </div>
            <div class="pagination">
                <!-- Pagination controls will go here -->
            </div>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 