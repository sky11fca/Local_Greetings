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
        const adminToken = sessionStorage.getItem('jwt_token');
        return {
            'Content-Type': 'application/json',
            'Authorization': adminToken ? `Bearer ${adminToken}` : ''
        };
    }

    checkAuth() {
        const adminToken = sessionStorage.getItem('jwt_token');
        if (!adminToken) {
            window.location.href = '/local_greeter/app/pages/login.php';
            return false;
        }
        return true;
    }

    async makeAuthenticatedRequest(url, options = {}) {
        if (!this.checkAuth()) {
            return null;
        }

        const defaultOptions = {
            headers: this.getAuthHeaders(),
            ...options
        };

        try {
            const response = await fetch(url, defaultOptions);
            
            // If unauthorized, redirect to login
            if (response.status === 401) {
                sessionStorage.removeItem('jwt_token');
                sessionStorage.removeItem('user');
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

        // Quick Actions
        document.getElementById('create-backup-btn')?.addEventListener('click', () => this.createBackup());
        document.getElementById('send-system-email-btn')?.addEventListener('click', () => this.sendSystemEmail());
        document.getElementById('clear-cache-btn')?.addEventListener('click', () => this.clearCache());
        document.getElementById('add-user-btn')?.addEventListener('click', () => this.addUser());
        document.getElementById('add-event-btn')?.addEventListener('click', () => this.addEvent());
        document.getElementById('add-field-btn')?.addEventListener('click', () => this.addField());

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
        
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminUsers&page=${page}&search=${encodeURIComponent(search)}`);
            if (!response) return;
            
            const data = await response.json();
            
            if (data.success && data.data) {
                this.renderUsersTable(data.data.users);
                this.renderPagination('users-pagination', data.data.pagination, (page) => this.loadUsers(page));
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    renderUsersTable(users) {
        const tbody = document.getElementById('users-tbody');
        if (users.length === 0) {
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
                        <button class="btn btn-sm btn-primary" onclick="adminDashboard.editUser(${user.user_id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteUser(${user.user_id})">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadEvents(page = 1) {
        const search = document.getElementById('event-search')?.value || '';
        const status = document.getElementById('event-status-filter')?.value || '';
        
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminEvents&page=${page}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
            if (!response) return;
            
            const data = await response.json();
            
            if (data.success) {
                this.renderEventsTable(data.events);
                this.renderPagination('events-pagination', data.pagination, (page) => this.loadEvents(page));
            }
        } catch (error) {
            console.error('Error loading events:', error);
        }
    }

    renderEventsTable(events) {
        const tbody = document.getElementById('events-tbody');
        if (events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9">No events found</td></tr>';
            return;
        }

        tbody.innerHTML = events.map(event => `
            <tr>
                <td>${event.event_id}</td>
                <td>${this.escapeHtml(event.title)}</td>
                <td>${this.escapeHtml(event.organizer_name)}</td>
                <td>${this.escapeHtml(event.field_name)}</td>
                <td>${this.escapeHtml(event.sport_type)}</td>
                <td>${new Date(event.start_time).toLocaleString()}</td>
                <td>${event.current_participants}/${event.max_participants}</td>
                <td><span class="status-${event.status}">${event.status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="adminDashboard.editEvent(${event.event_id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteEvent(${event.event_id})">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadFields(page = 1) {
        const search = document.getElementById('field-search')?.value || '';
        const type = document.getElementById('field-type-filter')?.value || '';
        
        try {
            const response = await this.makeAuthenticatedRequest(`/local_greeter/api/index.php?action=adminFields&page=${page}&search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}`);
            if (!response) return;
            
            const data = await response.json();
            
            if (data.success) {
                this.renderFieldsTable(data.fields);
                this.renderPagination('fields-pagination', data.pagination, (page) => this.loadFields(page));
            }
        } catch (error) {
            console.error('Error loading fields:', error);
        }
    }

    renderFieldsTable(fields) {
        const tbody = document.getElementById('fields-tbody');
        if (fields.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">No fields found</td></tr>';
            return;
        }

        tbody.innerHTML = fields.map(field => `
            <tr>
                <td>${field.field_id}</td>
                <td>${this.escapeHtml(field.name)}</td>
                <td>${this.escapeHtml(field.address)}</td>
                <td>${this.escapeHtml(field.type)}</td>
                <td><span class="status-${field.is_public ? 'active' : 'inactive'}">${field.is_public ? 'Yes' : 'No'}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-primary" onclick="adminDashboard.editField(${field.field_id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteField(${field.field_id})">Delete</button>
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
            const response = await fetch(`/local_greeter/api/index.php?action=adminUsers&id=${userId}`);
            const data = await response.json();
            
            if (data.success) {
                const user = data.user;
                document.getElementById('user-modal-title').textContent = 'Edit User';
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('role').value = user.is_admin ? 'admin' : 'user';
                document.getElementById('user-form').dataset.userId = userId;
                document.getElementById('user-modal').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading user:', error);
        }
    }

    async saveUser() {
        const formData = new FormData(document.getElementById('user-form'));
        const userId = document.getElementById('user-form').dataset.userId;
        
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminUsers', {
                method: userId ? 'PUT' : 'POST',
                body: formData
            });
            
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
            this.showNotification('Error saving user', 'error');
        }
    }

    async deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;
        
        try {
            const response = await fetch(`/local_greeter/api/index.php?action=adminUsers&id=${userId}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            if (data.success) {
                this.loadUsers();
                this.showNotification('User deleted successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error deleting user', 'error');
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
            const response = await fetch('/local_greeter/api/fields.php');
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
            const response = await fetch(`/local_greeter/api/index.php?action=adminEvents&id=${eventId}`);
            const data = await response.json();
            
            if (data.success) {
                const event = data.event;
                document.getElementById('event-modal-title').textContent = 'Edit Event';
                document.getElementById('event-title').value = event.title;
                document.getElementById('event-description').value = event.description;
                document.getElementById('event-field').value = event.field_id;
                document.getElementById('event-sport').value = event.sport_type;
                document.getElementById('event-start').value = event.start_time.replace(' ', 'T');
                document.getElementById('event-end').value = event.end_time.replace(' ', 'T');
                document.getElementById('event-max').value = event.max_participants;
                document.getElementById('event-form').dataset.eventId = eventId;
                
                this.loadFieldsForSelect();
                document.getElementById('event-modal').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading event:', error);
        }
    }

    async saveEvent() {
        const formData = new FormData(document.getElementById('event-form'));
        const eventId = document.getElementById('event-form').dataset.eventId;
        
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminEvents', {
                method: eventId ? 'PUT' : 'POST',
                body: formData
            });
            
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
            this.showNotification('Error saving event', 'error');
        }
    }

    async deleteEvent(eventId) {
        if (!confirm('Are you sure you want to delete this event?')) return;
        
        try {
            const response = await fetch(`/local_greeter/api/index.php?action=adminEvents&id=${eventId}`, {
                method: 'DELETE'
            });
            
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

    addField() {
        document.getElementById('field-modal-title').textContent = 'Add Sports Field';
        document.getElementById('field-form').reset();
        document.getElementById('field-modal').style.display = 'block';
    }

    async editField(fieldId) {
        try {
            const response = await fetch(`/local_greeter/api/index.php?action=adminFields&id=${fieldId}`);
            const data = await response.json();
            
            if (data.success) {
                const field = data.field;
                document.getElementById('field-modal-title').textContent = 'Edit Sports Field';
                document.getElementById('field-name').value = field.name;
                document.getElementById('field-address').value = field.address;
                document.getElementById('field-type').value = field.type;
                document.getElementById('field-amenities').value = field.amenities || '';
                document.getElementById('field-hours').value = field.opening_hours || '';
                document.getElementById('field-public').checked = field.is_public == 1;
                document.getElementById('field-form').dataset.fieldId = fieldId;
                document.getElementById('field-modal').style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading field:', error);
        }
    }

    async saveField() {
        const formData = new FormData(document.getElementById('field-form'));
        const fieldId = document.getElementById('field-form').dataset.fieldId;
        
        try {
            const response = await fetch('/local_greeter/api/index.php?action=adminFields', {
                method: fieldId ? 'PUT' : 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.closeAllModals();
                this.loadFields();
                this.showNotification('Field saved successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error saving field', 'error');
            }
        } catch (error) {
            console.error('Error saving field:', error);
            this.showNotification('Error saving field', 'error');
        }
    }

    async deleteField(fieldId) {
        if (!confirm('Are you sure you want to delete this field?')) return;
        
        try {
            const response = await fetch(`/local_greeter/api/index.php?action=adminFields&id=${fieldId}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            if (data.success) {
                this.loadFields();
                this.showNotification('Field deleted successfully', 'success');
            } else {
                this.showNotification(data.message || 'Error deleting field', 'error');
            }
        } catch (error) {
            console.error('Error deleting field:', error);
            this.showNotification('Error deleting field', 'error');
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
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        
        // Set background color based on type
        switch (type) {
            case 'success':
                notification.style.backgroundColor = '#28a745';
                break;
            case 'error':
                notification.style.backgroundColor = '#dc3545';
                break;
            case 'warning':
                notification.style.backgroundColor = '#ffc107';
                notification.style.color = '#212529';
                break;
            default:
                notification.style.backgroundColor = '#007bff';
        }
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
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
