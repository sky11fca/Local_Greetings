# 🔧 Local Greeter Admin Panel

## 📋 Overview
The Local Greeter Admin Panel is a comprehensive web-based administration module for managing the sports event management system. It provides secure access to user management, event management, sports fields management, and system settings.

## 🚀 Quick Start

### 1. Access Admin Login
Navigate to: `http://localhost/local_greeter/admin-login`

### 2. Default Admin Credentials
- **Email:** `admin@localgreeter.com`
- **Password:** `admin123`

### 3. Admin Panel Features
- **Dashboard:** Real-time statistics and system health
- **User Management:** Create, edit, delete users and manage roles
- **Event Management:** Manage sports events and participants
- **Sports Fields:** Add, edit, and manage sports facilities
- **System Settings:** Database status, backups, and system information

## 🔐 Security Features

### JWT Authentication
- Secure token-based authentication
- Automatic session management
- Admin-only access control
- Automatic logout on token expiration

### Admin Privileges
- Only users with `is_admin = 1` can access the admin panel
- All admin actions are logged in the `AdminLogs` table
- Session timeout after 30 minutes of inactivity

## 📊 Dashboard Features

### Statistics Overview
- Total Users count
- Total Events count
- Total Sports Fields count
- Active Events count

### Recent Activity
- Real-time admin action logs
- User registration events
- Event creation/modification
- System maintenance actions

### System Health
- Database connection status
- Email service status
- Disk space monitoring
- Memory usage tracking

## 👥 User Management

### User Operations
- **View Users:** List all registered users with search and pagination
- **Add User:** Create new user accounts with role assignment
- **Edit User:** Modify user information and privileges
- **Delete User:** Remove user accounts (with confirmation)
- **Role Management:** Assign admin privileges to users

### User Roles
- **User:** Standard user with event participation rights
- **Admin:** Full administrative access to the system

## 🏟️ Event Management

### Event Operations
- **View Events:** List all events with filtering by status
- **Add Event:** Create new sports events
- **Edit Event:** Modify event details and settings
- **Delete Event:** Remove events (with confirmation)
- **Status Management:** Track event status (active, completed, cancelled)

### Event Details
- Event title and description
- Sports field assignment
- Sport type classification
- Date and time scheduling
- Participant limits

## 🏟️ Sports Fields Management

### Field Operations
- **View Fields:** List all sports fields with type filtering
- **Add Field:** Register new sports facilities
- **Edit Field:** Update field information and amenities
- **Delete Field:** Remove fields (with confirmation)
- **Public/Private:** Manage field accessibility

### Field Information
- Field name and address
- Sport type classification
- Amenities and facilities
- Opening hours
- Public/private status

## ⚙️ System Settings

### Database Management
- Database connection status
- Last backup information
- Connection testing
- Backup creation

### System Information
- PHP version
- Server information
- Memory usage monitoring
- System performance metrics

### Security Settings
- JWT secret status
- Session timeout configuration
- JWT secret regeneration

## 🔧 Technical Implementation

### File Structure
```
local_greeter/
├── app/pages/
│   ├── admin.php              # Main admin dashboard
│   ├── admin-login.php        # Admin login page
│   └── admin-modals.php       # Modal dialogs
├── api/admin/
│   ├── AdminController.php    # Base admin controller
│   ├── stats.php             # Dashboard statistics
│   ├── users.php             # User management
│   ├── events.php            # Event management
│   ├── fields.php            # Field management
│   ├── activity.php          # Activity logs
│   ├── health.php            # System health
│   ├── backup.php            # Backup operations
│   └── check-admin.php       # Admin verification
├── public/
│   ├── css/admin.css         # Admin styles
│   └── js/admin.js           # Admin functionality
└── config/
    └── Database.php          # Database configuration
```

### Database Tables
- **Users:** User accounts with admin privileges
- **AdminLogs:** Admin action logging
- **Events:** Sports events management
- **SportsFields:** Sports facilities
- **EventParticipants:** Event participation tracking

## 🛠️ Setup Instructions

### 1. Database Migration
Run the migration script to create admin tables:
```sql
-- Run admin_logs_migration.sql
```

### 2. Populate Admin Logs
Execute the population script:
```sql
-- Run populate_admin_logs.sql
```

### 3. Verify Setup
Access the verification script:
```
http://localhost/local_greeter/verify_admin.php
```

### 4. Access Admin Panel
Navigate to admin login:
```
http://localhost/local_greeter/admin-login
```

## 🔍 Troubleshooting

### Common Issues

#### 1. "Access Denied" Error
- Ensure the user has `is_admin = 1` in the database
- Check JWT token validity
- Verify admin credentials

#### 2. Empty Admin Logs
- Run the verification script to populate initial logs
- Check database connection
- Verify AdminLogs table exists

#### 3. Login Issues
- Verify admin user exists in database
- Check password hash in database
- Ensure JWT configuration is correct

#### 4. API Errors
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check server error logs

### Debug Steps
1. Check browser console for JavaScript errors
2. Verify database connection
3. Test API endpoints directly
4. Check server error logs
5. Verify file permissions

## 📝 API Endpoints

### Authentication
- `POST /api/auth.php` - User login
- `POST /api/admin/check-admin.php` - Verify admin status

### Admin Operations
- `GET /api/admin/stats.php` - Dashboard statistics
- `GET /api/admin/users.php` - User management
- `GET /api/admin/events.php` - Event management
- `GET /api/admin/fields.php` - Field management
- `GET /api/admin/activity.php` - Activity logs
- `GET /api/admin/health.php` - System health
- `POST /api/admin/backup.php` - Backup operations

## 🔒 Security Best Practices

### Password Security
- Change default admin password immediately
- Use strong, unique passwords
- Enable password complexity requirements
- Regular password rotation

### Access Control
- Limit admin access to trusted users only
- Monitor admin activity logs
- Implement IP-based access restrictions
- Regular security audits

### Data Protection
- Regular database backups
- Encrypt sensitive data
- Implement data retention policies
- Monitor system access logs

## 📞 Support

For technical support or questions about the admin panel:
1. Check this documentation
2. Review error logs
3. Test with verification scripts
4. Contact system administrator

---

**⚠️ Important:** Always change the default admin credentials after first login for security purposes. 