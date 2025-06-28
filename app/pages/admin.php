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
            <button class="nav-tab" data-tab="data">Data Management</button>
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
                    <button class="btn btn-primary" onclick="adminDashboard.addUser()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
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
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="adminDashboard.addEvent()">
                        <i class="fas fa-plus"></i> Add Event
                    </button>
                </div>
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

        <!-- Data Management Tab -->
        <div class="admin-content" id="data-tab">
            <div class="content-header">
                <h2>Data Import/Export</h2>
                <p class="subtitle">Import and export data in CSV and JSON formats</p>
            </div>
            
            <div class="data-management-grid">
                <!-- Export Section -->
                <div class="data-card">
                    <h3>Export Data</h3>
                    <p>Download data in your preferred format</p>
                    
                    <div class="export-options">
                        <div class="form-group">
                            <label for="export-type">Data Type:</label>
                            <select id="export-type" class="form-control">
                                <option value="users">Users</option>
                                <option value="events">Events</option>
                                <option value="fields">Sports Fields</option>
                                <option value="all">All Data</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="export-format">Format:</label>
                            <select id="export-format" class="form-control">
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <button id="export-btn" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="data-card">
                    <h3>Import Data</h3>
                    <p>Upload data from CSV or JSON files</p>
                    
                    <div class="import-options">
                        <div class="form-group">
                            <label for="import-type">Data Type:</label>
                            <select id="import-type" class="form-control">
                                <option value="users">Users</option>
                                <option value="events">Events</option>
                                <option value="fields">Sports Fields</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="import-format">Format:</label>
                            <select id="import-format" class="form-control">
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="import-file">File:</label>
                            <input type="file" id="import-file" class="form-control" accept=".json,.csv">
                        </div>
                        
                        <button id="import-btn" class="btn btn-success">
                            <i class="fas fa-upload"></i> Import Data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Import Results -->
            <div id="import-results" class="import-results" style="display: none;">
                <h3>Import Results</h3>
                <div id="import-results-content"></div>
            </div>

            <!-- Template Downloads -->
            <div class="template-section">
                <h3>Download Templates</h3>
                <p>Use these templates as a starting point for your data import</p>
                
                <div class="template-buttons">
                    <button class="btn btn-secondary template-btn" data-type="users" data-format="csv">
                        <i class="fas fa-file-csv"></i> Users CSV Template
                    </button>
                    <button class="btn btn-secondary template-btn" data-type="users" data-format="json">
                        <i class="fas fa-file-code"></i> Users JSON Template
                    </button>
                    <button class="btn btn-secondary template-btn" data-type="events" data-format="csv">
                        <i class="fas fa-file-csv"></i> Events CSV Template
                    </button>
                    <button class="btn btn-secondary template-btn" data-type="events" data-format="json">
                        <i class="fas fa-file-code"></i> Events JSON Template
                    </button>
                    <button class="btn btn-secondary template-btn" data-type="fields" data-format="csv">
                        <i class="fas fa-file-csv"></i> Fields CSV Template
                    </button>
                    <button class="btn btn-secondary template-btn" data-type="fields" data-format="json">
                        <i class="fas fa-file-code"></i> Fields JSON Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- User Modal -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="user-modal-title">Add User</h2>
            <span class="close">&times;</span>
        </div>
        <form id="user-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <small>Leave blank to keep existing password when editing</small>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="adminDashboard.closeAllModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Event Modal -->
<div id="event-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="event-modal-title">Add Event</h2>
            <span class="close">&times;</span>
        </div>
        <form id="event-form">
            <div class="form-group">
                <label for="event-title">Title:</label>
                <input type="text" id="event-title" name="title" required>
            </div>
            <div class="form-group">
                <label for="event-description">Description:</label>
                <textarea id="event-description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="event-field">Field:</label>
                <select id="event-field" name="field_id" required>
                    <option value="">Select Field</option>
                </select>
            </div>
            <div class="form-group">
                <label for="event-sport">Sport Type:</label>
                <select id="event-sport" name="sport_type" required>
                    <option value="">Select Sport</option>
                    <option value="Football">Football</option>
                    <option value="Basketball">Basketball</option>
                    <option value="Tennis">Tennis</option>
                    <option value="Volleyball">Volleyball</option>
                    <option value="Yoga">Yoga</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="event-start">Start Time:</label>
                <input type="datetime-local" id="event-start" name="start_time" required>
            </div>
            <div class="form-group">
                <label for="event-end">End Time:</label>
                <input type="datetime-local" id="event-end" name="end_time" required>
            </div>
            <div class="form-group">
                <label for="event-max">Max Participants:</label>
                <input type="number" id="event-max" name="max_participants" min="1" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="adminDashboard.closeAllModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Notification Container -->
<div id="notification-container"></div>

<script src="/local_greeter/public/js/admin.js"></script>
<script>
    // Initialize admin dashboard
    const adminDashboard = new AdminDashboard();
</script>

<?php include __DIR__ . "/../templates/footer.php"; ?>
 