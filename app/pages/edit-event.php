<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /local_greeter/login');
    exit();
}
// Set page-specific variables
$pageTitle = "Edit Event - Iași Sports Network";
$currentPage = "edit-event";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/create-event.css" // Can reuse the same CSS
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/edit-event.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <section id="edit-event-section">
        <div class="container">
            <h2>Edit Event</h2>
            <form id="editEventForm">
                <input type="hidden" id="event_id" name="event_id">
                
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label for="sports_field_id">Sports Field</label>
                    <select id="sports_field_id" name="sports_field_id" required>
                        <!-- Options will be loaded dynamically -->
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
                    <label for="max_participants">Maximum Participants</label>
                    <input type="number" id="max_participants" name="max_participants" min="1" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Event</button>
                    <button type="button" id="delete-event-btn" class="btn btn-danger">Delete Event</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?> 