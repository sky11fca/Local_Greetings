<?php
// Admin page - requires admin privileges
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: /local_greeter/login');
    exit();
}

$pageTitle = "Admin Dashboard";
$currentPage = "admin";
$additionalCSS = ["/local_greeter/public/css/admin.css"];

include __DIR__ . "/../templates/header.php";
?>

<main class="admin-main">
    <div class="admin-container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1>Administration Dashboard</h1>
            <div class="admin-stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <span id="total-users">Loading...</span>
                </div>
                <div class="stat-card">
                    <h3>Total Events</h3>
                    <span id="total-events">Loading...</span>
                </div>
                <div class="stat-card">
                    <h3>Total Fields</h3>
                    <span id="total-fields">Loading...</span>
                </div>
                <div class="stat-card">
                    <h3>Active Events</h3>
                    <span id="active-events">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Admin Navigation -->
        <div class="admin-nav">
            <button class="nav-tab active" data-tab="dashboard">Dashboard</button>
            <button class="nav-tab" data-tab="users">User Management</button>
            <button class="nav-tab" data-tab="events">Event Management</button>
        </div>

        <!-- Dashboard Tab -->
        <div class="admin-content active" id="dashboard-tab">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Recent Activity</h3>
                    <div id="recent-activity" class="activity-list">
                        <p>Loading recent activity...</p>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h3>System Health</h3>
                    <div id="system-health" class="health-status">
                        <p>Checking system status...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div class="admin-content" id="users-tab">
            <div class="content-header">
                <h2>User Management</h2>
                <div class="header-actions">
                    <input type="text" id="user-search" placeholder="Search users..." class="search-input">
                </div>
            </div>
            <div class="table-container">
                <table class="admin-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <tr><td colspan="7">Loading users...</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="users-pagination"></div>
            </div>
        </div>

        <!-- Events Tab -->
        <div class="admin-content" id="events-tab">
            <div class="content-header">
                <h2>Event Management</h2>
            </div>
            <div class="table-container">
                <table class="admin-table" id="events-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Field</th>
                            <th>Sport Type</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="events-tbody">
                        <tr><td colspan="9">Loading events...</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="events-pagination"></div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . "/../templates/footer.php"; ?>
 