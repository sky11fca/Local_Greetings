# Admin Page Functionality

## Overview
The admin page now includes fully functional user and event management with edit and delete capabilities.

## Features Implemented

### User Management
- **View Users**: Display all users in a paginated table
- **Add User**: Create new users with username, email, password, and role
- **Edit User**: Modify existing user information
- **Delete User**: Remove users (with validation to prevent deletion of users with events)
- **Search Users**: Filter users by username or email

### Event Management
- **View Events**: Display all events in a paginated table
- **Add Event**: Create new events with all required fields
- **Edit Event**: Modify existing event information
- **Delete Event**: Remove events
- **Search Events**: Filter events by title or description

### UI Components
- **Modals**: Popup forms for adding/editing users and events
- **Action Buttons**: Edit and delete buttons in table rows
- **Notifications**: Success/error messages with auto-dismiss
- **Responsive Design**: Works on desktop and mobile devices

## Technical Implementation

### Frontend (JavaScript)
- `AdminDashboard` class in `public/js/admin.js`
- Modal management for forms
- API communication with authentication
- Real-time table updates
- Form validation and error handling

### Backend (PHP)
- `AdminController` base class for authentication
- `AdminUsersController` for user management
- `AdminEventsController` for event management
- Session-based authentication support
- Database operations with proper error handling

### API Endpoints
- `GET /api/index.php?action=adminUsers` - List users
- `POST /api/index.php?action=adminUsers` - Create user
- `PUT /api/index.php?action=adminUsers` - Update user
- `DELETE /api/index.php?action=adminUsers` - Delete user
- `GET /api/index.php?action=adminEvents` - List events
- `POST /api/index.php?action=adminEvents` - Create event
- `PUT /api/index.php?action=adminEvents` - Update event
- `DELETE /api/index.php?action=adminEvents` - Delete event

### Database Tables
- `Users` - User information
- `Events` - Event information
- `AdminLogs` - Admin activity logging

## Usage

### Accessing Admin Page
1. Log in as an admin user
2. Navigate to `/local_greeter/app/pages/admin.php`
3. Use the tab navigation to switch between different sections

### Adding a User
1. Click "Add User" button in User Management tab
2. Fill in the form fields
3. Click "Save" to create the user

### Editing a User
1. Click the edit button (pencil icon) next to a user
2. Modify the form fields
3. Click "Save" to update the user

### Deleting a User
1. Click the delete button (trash icon) next to a user
2. Confirm the deletion in the popup dialog

### Managing Events
Follow the same process as users for adding, editing, and deleting events.

## Security Features
- Admin-only access control
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention
- CSRF protection through session validation

## Error Handling
- User-friendly error messages
- Console logging for debugging
- Graceful fallbacks for failed operations
- Validation feedback for form inputs

## Styling
- Modern, responsive design
- Consistent with the main application theme
- Hover effects and animations
- Mobile-friendly interface 