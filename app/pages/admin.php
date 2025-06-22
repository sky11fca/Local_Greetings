<?php
// Admin page - requires JWT authentication and admin privileges
session_start();

$pageTitle = "Admin Dashboard";
$currentPage = "admin";
$additionalCSS = ["/local_greeter/public/css/admin.css"];

include __DIR__ . "/../templates/header.php";
?>

<script>
// Check if user is authenticated as admin
function checkAdminAuth() {
    const adminToken = sessionStorage.getItem('jwt_token');
    const adminUser = sessionStorage.getItem('user');
    
    if (!adminToken || !adminUser) {
        window.location.href = '/local_greeter/app/pages/login.php';
        return false;
    }
    
    try {
        const user = JSON.parse(adminUser);
        if (!user.user_id || !user.is_admin) {
            window.location.href = '/local_greeter/app/pages/login.php';
            return false;
        }
    } catch (e) {
        window.location.href = '/local_greeter/app/pages/login.php';
        return false;
    }
    
    return true;
}

// Check authentication on page load
if (!checkAdminAuth()) {
    // Redirect will happen in checkAdminAuth
} else {
    // Set admin user info
    const adminUser = JSON.parse(sessionStorage.getItem('user'));
    document.addEventListener('DOMContentLoaded', function() {
        // Update header with admin info
        const adminInfo = document.createElement('div');
        adminInfo.className = 'admin-user-info';
        adminInfo.innerHTML = `
            <span>Welcome, ${adminUser.username}</span>
            <button onclick="logout()" class="btn btn-sm btn-outline">Logout</button>
        `;
        
        const adminHeader = document.querySelector('.admin-header');
        if (adminHeader) {
            adminHeader.appendChild(adminInfo);
        }
    });
}

function logout() {
    sessionStorage.removeItem('jwt_token');
    sessionStorage.removeItem('user');
    window.location.href = '/local_greeter/admin-login';
}

setTimeout(() => {
    const user = result.data;
    if (user.is_admin) {
        window.location.href = '/local_greeter/admin';
    } else {
        window.location.href = '/local_greeter/home';
    }
}, 1500)
</script>

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
            <button class="nav-tab" data-tab="fields">Sports Fields</button>
            <button class="nav-tab" data-tab="system">System Settings</button>
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
                <div class="dashboard-card">
                    <h3>Quick Actions</h3>
                    <div class="quick-actions">
                        <button class="btn btn-primary" id="create-backup-btn">Create Backup</button>
                        <button class="btn btn-secondary" id="send-system-email-btn">Send System Email</button>
                        <button class="btn btn-warning" id="clear-cache-btn">Clear Cache</button>
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
                    <button class="btn btn-primary" id="add-user-btn">Add User</button>
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
                <div class="header-actions">
                    <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                    <select id="event-status-filter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button class="btn btn-primary" id="add-event-btn">Add Event</button>
                </div>
            </div>
            <div class="table-container">
                <table class="admin-table" id="events-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Organizer</th>
                            <th>Field</th>
                            <th>Sport Type</th>
                            <th>Date/Time</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="events-tbody">
                        <tr><td colspan="9">Loading events...</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="events-pagination"></div>
            </div>
        </div>

        <!-- Fields Tab -->
        <div class="admin-content" id="fields-tab">
            <div class="content-header">
                <h2>Sports Fields Management</h2>
                <div class="header-actions">
                    <input type="text" id="field-search" placeholder="Search fields..." class="search-input">
                    <select id="field-type-filter" class="filter-select">
                        <option value="">All Types</option>
                        <option value="football">Football</option>
                        <option value="basketball">Basketball</option>
                        <option value="tennis">Tennis</option>
                        <option value="volleyball">Volleyball</option>
                        <option value="other">Other</option>
                    </select>
                    <button class="btn btn-primary" id="add-field-btn">Add Field</button>
                </div>
            </div>
            <div class="table-container">
                <table class="admin-table" id="fields-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Type</th>
                            <th>Public</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fields-tbody">
                        <tr><td colspan="6">Loading fields...</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="fields-pagination"></div>
            </div>
        </div>

        <!-- System Tab -->
        <div class="admin-content" id="system-tab">
            <div class="content-header">
                <h2>System Settings</h2>
            </div>
            <div class="settings-grid">
                <div class="settings-card">
                    <h3>Database Settings</h3>
                    <div class="setting-item">
                        <label>Database Status:</label>
                        <span id="db-status">Checking...</span>
                    </div>
                    <div class="setting-item">
                        <label>Last Backup:</label>
                        <span id="last-backup">Unknown</span>
                    </div>
                    <button class="btn btn-primary" onclick="adminDashboard.testDatabase()">Test Connection</button>
                </div>

                <div class="settings-card">
                    <h3>System Information</h3>
                    <div class="setting-item">
                        <label>PHP Version:</label>
                        <span><?php echo phpversion(); ?></span>
                    </div>
                    <div class="setting-item">
                        <label>Server:</label>
                        <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                    </div>
                    <div class="setting-item">
                        <label>Memory Usage:</label>
                        <span id="memory-usage">Checking...</span>
                    </div>
                </div>

                <div class="settings-card">
                    <h3>Security Settings</h3>
                    <div class="setting-item">
                        <label>JWT Secret:</label>
                        <span id="jwt-status">Checking...</span>
                    </div>
                    <div class="setting-item">
                        <label>Session Timeout:</label>
                        <span>30 minutes</span>
                    </div>
                    <button class="btn btn-warning" onclick="adminDashboard.regenerateJWT()">Regenerate JWT Secret</button>
                </div>
            </div>
            <div id="system-logs"></div>
        </div>
    </div>
</main>

<?php include __DIR__ . "/../templates/footer.php"; ?>
 