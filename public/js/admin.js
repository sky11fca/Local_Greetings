// Admin Dashboard JavaScript
class AdminDashboard {
    constructor() {
        this.currentTab = 'dashboard';
        this.currentPage = {
            users: 1,
            events: 1,
            fields: 1
        };
        this.itemsPerPage = 10;
        this.init();
    }

    // JWT Authentication helpers
    getAuthHeaders() {
        const adminToken = localStorage.getItem('jwt_token');
        return {
            'Content-Type': 'application/json',
            'Authorization': adminToken ? `Bearer ${adminToken}` : ''
        };
    }

    checkAuth() {
        const adminToken = localStorage.getItem('jwt_token');
        if (!adminToken) {
            window.location.href = '/local_greeter/app/pages/login.php';
            return false;
        }
        return true;
    }

    async makeAuthenticatedRequest(url, options = {}) {
        if (!this.checkAuth()) {
            console.error('Authentication check failed');
            return null;
        }

        const defaultOptions = {
            headers: this.getAuthHeaders(),
            ...options
        };

        try {
            console.log('Making request to:', url, 'with options:', defaultOptions);
            const response = await fetch(url, defaultOptions);
            console.log('Response status:', response.status);
            
            // If unauthorized, redirect to login
            if (response.status === 401) {
                console.error('Unauthorized - redirecting to login');
                localStorage.removeItem('jwt_token');
                localStorage.removeItem('user');
                window.location.href = '/local_greeter/app/pages/login.php';
                return null;
            }
            
            return response;
        } catch (error) {
            console.error('Request failed:', error);
            return null;
        }
    }

    init() {
        // Check authentication first
        if (!this.checkAuth()) {
            return;
        }
        
        this.setupEventListeners();
        this.loadDashboardStats();
        this.loadCurrentTab();
    }

    setupEventListeners() {
        // Tab navigation
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Search functionality
        document.getElementById('user-search')?.addEventListener('input', this.debounce(() => {
            this.loadUsers();
        }, 500));

        document.getElementById('event-search')?.addEventListener('input', this.debounce(() => {
            this.loadEvents();
        }, 500));

        document.getElementById('field-search')?.addEventListener('input', this.debounce(() => {
            this.loadFields();
        }, 500));

        // Filter functionality
        document.getElementById('event-status-filter')?.addEventListener('change', () => {
            this.loadEvents();
        });

        document.getElementById('field-type-filter')?.addEventListener('change', () => {
            this.loadFields();
        });

        // Import/Export functionality
        document.getElementById('export-btn')?.addEventListener('click', () => {
            this.exportData();
        });

        document.getElementById('import-btn')?.addEventListener('click', () => {
            this.importData();
        });

        // Template download buttons
        document.querySelectorAll('.template-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.target.dataset.type;
                const format = e.target.dataset.format;
                this.downloadTemplate(type, format);
            });
        });

        // Modal functionality
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                this.closeAllModals();
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeAllModals();
            }
        });

        // Form submissions
        document.getElementById('user-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveUser();
        });

        document.getElementById('event-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveEvent();
        });

        document.getElementById('field-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveField();
        });
    }

    switchTab(tabName) {
        // Update active tab
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update active content
        document.querySelectorAll('.admin-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');

        this.currentTab = tabName;
        this.loadCurrentTab();
    }

    loadCurrentTab() {
        switch (this.currentTab) {
            case 'dashboard':
                this.loadDashboardData();
                break;
            case 'users':
                this.loadUsers();
                break;
            case 'events':
                this.loadEvents();
                break;
            case 'fields':
                this.loadFields();
                break;
            case 'data':
                // Data management tab doesn't need initial loading
                break;
            case 'system':
                this.loadSystemData();
                break;
        }
    }

    async loadDashboardStats() {
        try {
            const response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminStats');
            if (!response) return;
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('total-users').textContent = data.stats.totalUsers;
                document.getElementById('total-events').textContent = data.stats.totalEvents;
                document.getElementById('total-fields').textContent = data.stats.totalFields;
                document.getElementById('active-events').textContent = data.stats.activeEvents;
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    async loadDashboardData() {
        await this.loadRecentActivity();
        await this.loadSystemHealth();
    }

    async loadRecentActivity() {
        try {
            const response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminActivity');
            if (!response) return;
            
            const data = await response.json();
            
            const activityContainer = document.getElementById('recent-activity');
            if (data.success && data.activities.length > 0) {
                activityContainer.innerHTML = data.activities.map(activity => `
                    <div class="activity-item">
                        <div class="activity-time">${activity.timestamp}</div>
                        <div class="activity-text">${activity.description}</div>
                    </div>
                `).join('');
            } else {
                activityContainer.innerHTML = '<p>No recent activity</p>';
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
        }
    }

    async loadSystemHealth() {
        try {
            const response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminHealth');
            if (!response) return;
            
            const data = await response.json();
            
            const healthContainer = document.getElementById('system-health');
            if (data.success) {
                healthContainer.innerHTML = `
                    <div class="health-item">
                        <span class="health-label">Database:</span>
                        <span class="health-status ${data.health.database ? 'status-active' : 'status-inactive'}">
                            ${data.health.database ? 'Connected' : 'Disconnected'}
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">Email Service:</span>
                        <span class="health-status ${data.health.email ? 'status-active' : 'status-inactive'}">
                            ${data.health.email ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                    <div class="health-item">
                        <span class="health-label">Disk Space:</span>
                        <span class="health-status ${data.health.diskSpace > 10 ? 'status-active' : 'status-warning'}">
                            ${data.health.diskSpace}% used
                        </span>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading system health:', error);
        }
    }

    async loadUsers(page = 1) {
        const search = document.getElementById('user-search')?.value || '';
        const tbody = document.getElementById('users-tbody');
        
        try {
            console.log('Loading users...');
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminUsers&page=${page}&search=${encodeURIComponent(search)}`);
            if (!response) {
                console.error('No response from server');
                tbody.innerHTML = '<tr><td colspan="7">Failed to load users. Please try again later.</td></tr>';
                return;
            }
            
            const data = await response.json();
            console.log('Users response:', data);
            
            if (data.success) {
                this.renderUsersTable(data.data.users);
                this.renderPagination('users-pagination', data.data.pagination, (page) => this.loadUsers(page));
            } else {
                console.error('Failed to load users:', data.message);
                tbody.innerHTML = `<tr><td colspan="7">${data.message || 'Failed to load users.'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading users:', error);
            tbody.innerHTML = '<tr><td colspan="7">Failed to load users. Please try again later.</td></tr>';
        }
    }

    renderUsersTable(users) {
        const tbody = document.getElementById('users-tbody');
        if (!Array.isArray(users) || users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr>
                <td>${user.user_id}</td>
                <td>${this.escapeHtml(user.username)}</td>
                <td>${this.escapeHtml(user.email)}</td>
                <td><span class="badge ${user.is_admin ? 'badge-admin' : 'badge-user'}">${user.is_admin ? 'Admin' : 'User'}</span></td>
                <td><span class="status-active">Active</span></td>
                <td>${new Date(user.created_at).toLocaleDateString()}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="adminDashboard.editUser(${user.user_id})" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteUser(${user.user_id})" title="Delete User">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadEvents(page = 1) {
        const search = document.getElementById('event-search')?.value || '';
        const status = document.getElementById('event-status-filter')?.value || '';
        const tbody = document.getElementById('events-tbody');
        
        try {
            console.log('Loading events...');
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminEvents&page=${page}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
            if (!response) {
                console.error('No response from server');
                tbody.innerHTML = '<tr><td colspan="9">Failed to load events. Please try again later.</td></tr>';
                return;
            }
            
            const data = await response.json();
            console.log('Events response:', data);
            
            if (data.success) {
                this.renderEventsTable(data.data.events);
                this.renderPagination('events-pagination', data.data.pagination, (page) => this.loadEvents(page));
            } else {
                console.error('Failed to load events:', data.message);
                tbody.innerHTML = `<tr><td colspan="9">${data.message || 'Failed to load events.'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading events:', error);
            tbody.innerHTML = '<tr><td colspan="9">Failed to load events. Please try again later.</td></tr>';
        }
    }

    renderEventsTable(events) {
        const tbody = document.getElementById('events-tbody');
        if (!Array.isArray(events) || events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9">No events found</td></tr>';
            return;
        }

        tbody.innerHTML = events.map(event => `
            <tr>
                <td>${event.event_id}</td>
                <td>${this.escapeHtml(event.title)}</td>
                <td>${this.escapeHtml(event.field_name)}</td>
                <td>${this.escapeHtml(event.sport_type)}</td>
                <td>${new Date(event.start_time).toLocaleString()}</td>
                <td>${new Date(event.end_time).toLocaleString()}</td>
                <td>${event.current_participants}/${event.max_participants}</td>
                <td><span class="status-${event.status}">${event.status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="adminDashboard.editEvent(${event.event_id})" title="Edit Event">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteEvent(${event.event_id})" title="Delete Event">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadSystemData() {
        await this.loadDatabaseStatus();
        await this.loadEmailStatus();
        await this.loadSystemLogs();
    }

    async loadDatabaseStatus() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminDatabaseStatus');
            const data = await response.json();
            
            document.getElementById('db-status').textContent = data.connected ? 'Connected' : 'Disconnected';
            document.getElementById('db-status').className = data.connected ? 'status-active' : 'status-inactive';
            document.getElementById('last-backup').textContent = data.lastBackup || 'Never';
        } catch (error) {
            console.error('Error loading database status:', error);
        }
    }

    async loadEmailStatus() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminEmailStatus');
            const data = await response.json();
            
            document.getElementById('smtp-status').textContent = data.connected ? 'Connected' : 'Disconnected';
            document.getElementById('smtp-status').className = data.connected ? 'status-active' : 'status-inactive';
        } catch (error) {
            console.error('Error loading email status:', error);
        }
    }

    async loadSystemLogs() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminLogs');
            const data = await response.json();
            
            const logsContainer = document.getElementById('system-logs');
            if (data.success && data.logs.length > 0) {
                logsContainer.innerHTML = data.logs.map(log => `
                    <div class="log-entry">
                        <span class="log-time">[${log.timestamp}]</span>
                        <span class="log-level ${log.level}">${log.level.toUpperCase()}</span>
                        <span class="log-message">${this.escapeHtml(log.message)}</span>
                    </div>
                `).join('');
            } else {
                logsContainer.innerHTML = '<p>No logs available</p>';
            }
        } catch (error) {
            console.error('Error loading system logs:', error);
        }
    }

    renderPagination(containerId, pagination, onPageChange) {
        const container = document.getElementById(containerId);
        if (!pagination || pagination.totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        
        // Previous button
        html += `<button ${pagination.currentPage <= 1 ? 'disabled' : ''} onclick="(${onPageChange.toString()})(${pagination.currentPage - 1})">Previous</button>`;
        
        // Page numbers
        for (let i = Math.max(1, pagination.currentPage - 2); i <= Math.min(pagination.totalPages, pagination.currentPage + 2); i++) {
            html += `<button class="${i === pagination.currentPage ? 'active' : ''}" onclick="(${onPageChange.toString()})(${i})">${i}</button>`;
        }
        
        // Next button
        html += `<button ${pagination.currentPage >= pagination.totalPages ? 'disabled' : ''} onclick="(${onPageChange.toString()})(${pagination.currentPage + 1})">Next</button>`;
        
        container.innerHTML = html;
    }

    // CRUD Operations
    addUser() {
        document.getElementById('user-modal-title').textContent = 'Add User';
        document.getElementById('user-form').reset();
        document.getElementById('user-modal').style.display = 'block';
    }

    async editUser(userId) {
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminUsers&id=${userId}`);
            if (!response) return;
            
            const data = await response.json();
            
            if (data.success) {
                const user = data.data.user;
                document.getElementById('user-modal-title').textContent = 'Edit User';
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('role').value = user.is_admin ? 'admin' : 'user';
                document.getElementById('user-form').dataset.userId = userId;
                document.getElementById('user-modal').style.display = 'block';
            } else {
                this.showNotification(data.message || 'Error loading user', 'error');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            this.showNotification('Error loading user', 'error');
        }
    }

    async saveUser() {
        const form = document.getElementById('user-form');
        const userId = form.dataset.userId;
        const username = form.username.value.trim();
        const email = form.email.value.trim();
        const password = form.password.value.trim();
        const role = form.role.value;
        if (!username || !email || (!userId && !password)) {
            this.showNotification('Username, email, and password are required', 'error');
            return;
        }
        try {
            let response;
            if (userId) {
                // PUT request - convert FormData to URL-encoded string
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                data.id = userId; // Add the user ID
                const urlEncodedData = new URLSearchParams(data).toString();
                response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminUsers', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: urlEncodedData
                });
            } else {
                // POST request - use FormData
                const formData = new FormData(form);
                response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminUsers', {
                    method: 'POST',
                    body: formData
                });
            }
            if (!response) return;
            const data = await response.json();
            if (data.success) {
                this.closeAllModals();
                this.loadUsers();
                this.showNotification('User saved successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error saving user', 'error');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            this.showNotification('Error saving user: ' + (error.message || error), 'error');
        }
    }

    async deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;
        
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminUsers&id=${userId}`, {
                method: 'DELETE'
            });
            
            if (!response) return;
            
            const data = await response.json();
            console.log('Delete user response:', data); // Debug log
            if (data.success) {
                this.loadUsers();
                this.showNotification('User deleted successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error deleting user', 'error');
                console.error('Delete user error:', data);
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            this.showNotification('Error deleting user', 'error');
        }
    }

    addEvent() {
        document.getElementById('event-modal-title').textContent = 'Add Event';
        document.getElementById('event-form').reset();
        this.loadFieldsForSelect();
        document.getElementById('event-modal').style.display = 'block';
    }

    async loadFieldsForSelect() {
        try {
            const response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=getSportsFields');
            if (!response) return;
            
            const data = await response.json();
            
            const select = document.getElementById('event-field');
            select.innerHTML = '<option value="">Select Field</option>';
            
            if (data.success) {
                data.fields.forEach(field => {
                    select.innerHTML += `<option value="${field.field_id}">${field.name}</option>`;
                });
            }
        } catch (error) {
            console.error('Error loading fields:', error);
        }
    }

    async editEvent(eventId) {
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminEvents&id=${eventId}`);
            if (!response) return;
            
            const data = await response.json();
            
            if (data.success) {
                const event = data.data.event;
                document.getElementById('event-modal-title').textContent = 'Edit Event';
                document.getElementById('event-title').value = event.title;
                document.getElementById('event-description').value = event.description;
                document.getElementById('event-sport').value = event.sport_type;
                document.getElementById('event-start').value = event.start_time.replace(' ', 'T');
                document.getElementById('event-end').value = event.end_time.replace(' ', 'T');
                document.getElementById('event-max').value = event.max_participants;
                document.getElementById('event-form').dataset.eventId = eventId;

                // Load fields, then set the value
                await this.loadFieldsForSelect();
                document.getElementById('event-field').value = event.field_id;

                document.getElementById('event-modal').style.display = 'block';
            } else {
                this.showNotification(data.message || 'Error loading event', 'error');
            }
        } catch (error) {
            console.error('Error loading event:', error);
            this.showNotification('Error loading event', 'error');
        }
    }

    async saveEvent() {
        const form = document.getElementById('event-form');
        const eventId = form.dataset.eventId;
        const title = form['event-title'].value.trim();
        const description = form['event-description'].value.trim();
        const fieldId = form['event-field'].value.trim();
        const sportType = form['event-sport'].value.trim();
        const startTime = form['event-start'].value.trim();
        const endTime = form['event-end'].value.trim();
        const maxParticipants = form['event-max'].value.trim();
        if (!title || !description || !fieldId || !sportType || !startTime || !endTime || !maxParticipants) {
            this.showNotification('All fields are required', 'error');
            return;
        }
        try {
            let response;
            if (eventId) {
                // PUT request - convert FormData to URL-encoded string
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                data.id = eventId; // Add the event ID
                const urlEncodedData = new URLSearchParams(data).toString();
                response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminEvents', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: urlEncodedData
                });
            } else {
                // POST request - use FormData
                const formData = new FormData(form);
                response = await this.makeAuthenticatedRequest('/local_greeter/api/index.php?action=adminEvents', {
                    method: 'POST',
                    body: formData
                });
            }
            if (!response) return;
            const data = await response.json();
            if (data.success) {
                this.closeAllModals();
                this.loadEvents();
                this.showNotification('Event saved successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error saving event', 'error');
            }
        } catch (error) {
            console.error('Error saving event:', error);
            this.showNotification('Error saving event: ' + (error.message || error), 'error');
        }
    }

    async deleteEvent(eventId) {
        if (!confirm('Are you sure you want to delete this event?')) return;
        
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminEvents&id=${eventId}`, {
                method: 'DELETE'
            });
            
            if (!response) return;
            
            const data = await response.json();
            if (data.success) {
                this.loadEvents();
                this.showNotification('Event deleted successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error deleting event', 'error');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            this.showNotification('Error deleting event', 'error');
        }
    }

    // System Management
    async createBackup() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminBackup', {
                method: 'POST'
            });
            
            const data = await response.json();
            if (data.success) {
                this.showNotification('Backup created successfully', 'success');
                this.loadSystemData();
            } else {
                this.showNotification(data.message || 'Error creating backup', 'error');
            }
        } catch (error) {
            console.error('Error creating backup:', error);
            this.showNotification('Error creating backup', 'error');
        }
    }

    async sendSystemEmail() {
        const email = prompt('Enter email address:');
        if (!email) return;
        
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminTestEmail', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            });
            
            const data = await response.json();
            if (data.success) {
                this.showNotification('Test email sent successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error sending email', 'error');
            }
        } catch (error) {
            console.error('Error sending email:', error);
            this.showNotification('Error sending email', 'error');
        }
    }

    async clearCache() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminClearCache', {
                method: 'POST'
            });
            
            const data = await response.json();
            if (data.success) {
                this.showNotification('Cache cleared successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error clearing cache', 'error');
            }
        } catch (error) {
            console.error('Error clearing cache:', error);
            this.showNotification('Error clearing cache', 'error');
        }
    }

    async testDatabase() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminTestDatabase');
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Database connection successful', 'success');
            } else {
                this.showNotification(data.message || 'Database connection failed', 'error');
            }
        } catch (error) {
            console.error('Error testing database:', error);
            this.showNotification('Error testing database', 'error');
        }
    }

    async testEmail() {
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminTestEmail');
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Email service test successful', 'success');
            } else {
                this.showNotification(data.message || 'Email service test failed', 'error');
            }
        } catch (error) {
            console.error('Error testing email:', error);
            this.showNotification('Error testing email', 'error');
        }
    }

    // Utility functions
    closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        // Create content
        const content = document.createElement('div');
        content.className = 'notification-content';
        content.textContent = message;
        
        // Create close button
        const closeBtn = document.createElement('button');
        closeBtn.className = 'notification-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = () => this.removeNotification(notification);
        
        // Assemble notification
        notification.appendChild(content);
        notification.appendChild(closeBtn);
        container.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            this.removeNotification(notification);
        }, 5000);
    }

    removeNotification(notification) {
        if (notification && notification.parentNode) {
            notification.classList.add('removing');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Import/Export Methods
    async exportData() {
        const dataType = document.getElementById('export-type').value;
        const format = document.getElementById('export-format').value;
        const exportBtn = document.getElementById('export-btn');
        
        try {
            exportBtn.classList.add('loading');
            exportBtn.disabled = true;
            
            const url = `/local_greeter/api/index.php?action=adminImportExport&operation=export&type=${dataType}&format=${format}`;
            
            // Check authentication first
            if (!this.checkAuth()) {
                throw new Error('Authentication failed');
            }
            
            // Make the request with proper headers
            const adminToken = localStorage.getItem('jwt_token');
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${adminToken}`,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            }
            
            // Parse the response
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Export failed');
            }
            
            // Get the file content from the response
            const fileContent = result.content;
            const filename = result.filename || `${dataType}_export_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.${format}`;
            
            // Create blob and download
            const blob = new Blob([fileContent], { 
                type: result.contentType || (format === 'json' ? 'application/json' : 'text/csv')
            });
            const downloadUrl = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up the URL object
            URL.revokeObjectURL(downloadUrl);
            
            this.showNotification(`Data exported successfully in ${format.toUpperCase()} format`, 'success');
            
        } catch (error) {
            console.error('Export failed:', error);
            this.showNotification('Export failed: ' + error.message, 'error');
        } finally {
            exportBtn.classList.remove('loading');
            exportBtn.disabled = false;
        }
    }

    async importData() {
        const dataType = document.getElementById('import-type').value;
        const format = document.getElementById('import-format').value;
        const fileInput = document.getElementById('import-file');
        const importBtn = document.getElementById('import-btn');
        
        if (!fileInput.files || fileInput.files.length === 0) {
            this.showNotification('Please select a file to import', 'warning');
            return;
        }
        
        const file = fileInput.files[0];
        
        try {
            importBtn.classList.add('loading');
            importBtn.disabled = true;
            
            const fileContent = await this.readFileContent(file);
            const url = `/local_greeter/api/index.php?action=adminImportExport&operation=import&type=${dataType}&format=${format}`;
            
            const response = await this.makeAuthenticatedRequest(url, {
                method: 'POST',
                body: fileContent
            });
            
            if (!response) {
                throw new Error('Request failed');
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showImportResults(result.data);
                this.showNotification('Import completed successfully', 'success');
                
                // Refresh relevant data if on users or events tab
                if (dataType === 'users' && this.currentTab === 'users') {
                    this.loadUsers();
                } else if (dataType === 'events' && this.currentTab === 'events') {
                    this.loadEvents();
                }
            } else {
                throw new Error(result.message || 'Import failed');
            }
            
        } catch (error) {
            console.error('Import failed:', error);
            this.showNotification('Import failed: ' + error.message, 'error');
        } finally {
            importBtn.classList.remove('loading');
            importBtn.disabled = false;
            fileInput.value = ''; // Clear file input
        }
    }

    readFileContent(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                resolve(e.target.result);
            };
            
            reader.onerror = (e) => {
                reject(new Error('Failed to read file'));
            };
            
            reader.readAsText(file);
        });
    }

    showImportResults(results) {
        const resultsContainer = document.getElementById('import-results');
        const resultsContent = document.getElementById('import-results-content');
        
        let html = '';
        
        if (results.imported > 0) {
            html += `<div class="import-success">✓ Successfully imported: ${results.imported} items</div>`;
        }
        
        if (results.skipped > 0) {
            html += `<div class="import-warning">⚠ Skipped: ${results.skipped} items (already exist)</div>`;
        }
        
        if (results.errors && results.errors.length > 0) {
            html += `<div class="import-error">✗ Errors: ${results.errors.length} items failed</div>`;
            html += '<div style="margin-top: 10px;">';
            results.errors.forEach(error => {
                html += `<div style="color: #dc3545; margin: 5px 0;">• ${this.escapeHtml(error)}</div>`;
            });
            html += '</div>';
        }
        
        resultsContent.innerHTML = html;
        resultsContainer.style.display = 'block';
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
            resultsContainer.style.display = 'none';
        }, 10000);
    }

    downloadTemplate(type, format) {
        const templates = {
            users: {
                csv: 'username,email,is_admin,reputation_score\njohn_doe,john@example.com,0,0\njane_admin,jane@example.com,1,100',
                json: JSON.stringify([
                    {
                        "username": "john_doe",
                        "email": "john@example.com",
                        "is_admin": 0,
                        "reputation_score": 0
                    },
                    {
                        "username": "jane_admin",
                        "email": "jane@example.com",
                        "is_admin": 1,
                        "reputation_score": 100
                    }
                ], null, 2)
            },
            events: {
                csv: 'title,description,sport_type,start_time,end_time,max_participants,current_participants,status,field_name,creator_name\nBasketball Game,Weekly basketball game,basketball,2024-01-15 18:00:00,2024-01-15 20:00:00,10,0,upcoming,Main Court,john_doe',
                json: JSON.stringify([
                    {
                        "title": "Basketball Game",
                        "description": "Weekly basketball game",
                        "sport_type": "basketball",
                        "start_time": "2024-01-15 18:00:00",
                        "end_time": "2024-01-15 20:00:00",
                        "max_participants": 10,
                        "current_participants": 0,
                        "status": "upcoming",
                        "field_name": "Main Court",
                        "creator_name": "john_doe"
                    }
                ], null, 2)
            },
            fields: {
                csv: 'name,sport_type,location,longitude,latitude,is_public\nMain Court,basketball,Central Park,40.7589,-73.9851,1\nTennis Court,tennis,Sports Complex,40.7589,-73.9851,1',
                json: JSON.stringify([
                    {
                        "name": "Main Court",
                        "sport_type": "basketball",
                        "location": "Central Park",
                        "longitude": 40.7589,
                        "latitude": -73.9851,
                        "is_public": 1
                    },
                    {
                        "name": "Tennis Court",
                        "sport_type": "tennis",
                        "location": "Sports Complex",
                        "longitude": 40.7589,
                        "latitude": -73.9851,
                        "is_public": 1
                    }
                ], null, 2)
            }
        };
        
        const template = templates[type][format];
        const filename = `${type}_template.${format}`;
        
        // Create and download file
        const blob = new Blob([template], { type: format === 'json' ? 'application/json' : 'text/csv' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        this.showNotification(`Template downloaded: ${filename}`, 'success');
    }
}

// Initialize admin dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-admin {
        background: #dc3545;
        color: white;
    }
    
    .badge-user {
        background: #6c757d;
        color: white;
    }
    
    .log-entry {
        margin-bottom: 5px;
        font-family: 'Courier New', monospace;
    }
    
    .log-time {
        color: #95a5a6;
    }
    
    .log-level {
        margin: 0 10px;
        font-weight: bold;
    }
    
    .log-level.error {
        color: #e74c3c;
    }
    
    .log-level.warning {
        color: #f39c12;
    }
    
    .log-level.info {
        color: #3498db;
    }
    
    .health-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .health-item:last-child {
        border-bottom: none;
    }
    
    .health-label {
        font-weight: 500;
    }
    
    .activity-item {
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-time {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .activity-text {
        color: #495057;
    }
`;
document.head.appendChild(style);
