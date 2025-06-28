<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /local_greeter/login');
    exit();
}
// Set page-specific variables
$pageTitle = "Create Event - Iași Sports Network";
$currentPage = "create-event";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/create-event.css",
    "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
];

// Additional scripts for this page
$additionalScripts = [
    "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js",
    "/local_greeter/public/js/create-event.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <section id="create-event-section">
        <div class="container">
            <h2>Create New Event</h2>
            <form id="createEventForm">
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Football Match" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your event..." rows="5" required></textarea>
                </div>
                
                <!-- Map Section -->
                <div class="form-group">
                    <label>Select Sports Field from Map</label>
                    <div id="field-map" style="height: 400px; width: 100%; border-radius: 5px; margin-bottom: 10px;"></div>
                    <div id="selected-field-info" class="selected-field-info" style="display: none;">
                        <strong>Selected Field:</strong> <span id="selected-field-name"></span>
                        <button type="button" id="clear-selection" class="btn btn-secondary btn-sm">Clear Selection</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sports_field_id">Sports Field (or select from map above)</label>
                    <select id="sports_field_id" name="sports_field_id" required>
                        <option value="">Loading fields...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sport_type">Sport Type</label>
                    <select id="sport_type" name="sport_type" required>
                        <option value="">Select a Sport Type</option>
                        <option value="football">Football</option>
                        <option value="basketball">Basketball</option>
                        <option value="tennis">Tennis</option>
                        <option value="volleyball">Volleyball</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="datetime-local" id="start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="datetime-local" id="end_time" name="end_time" required>
                </div>
                <div class="form-group">
                    <label for="max_participants">Max Participants</label>
                    <input type="number" id="max_participants" name="max_participants" min="1" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Event</button>
            </form>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 