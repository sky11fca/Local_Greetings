<!-- User Modal -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="user-modal-title">Add User</h2>
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
                <input type="password" id="password" name="password" required>
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
                <button type="button" class="btn btn-secondary" onclick="closeModal('user-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Event Modal -->
<div id="event-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="event-modal-title">Add Event</h2>
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
                <label for="event-field">Sports Field:</label>
                <select id="event-field" name="field_id" required>
                    <option value="">Select Field</option>
                </select>
            </div>
            <div class="form-group">
                <label for="event-sport">Sport Type:</label>
                <select id="event-sport" name="sport_type" required>
                    <option value="football">Football</option>
                    <option value="basketball">Basketball</option>
                    <option value="tennis">Tennis</option>
                    <option value="volleyball">Volleyball</option>
                    <option value="other">Other</option>
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
                <button type="button" class="btn btn-secondary" onclick="closeModal('event-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="modal">
    <div class="modal-content">
        <h2>Confirm Action</h2>
        <p id="confirm-message">Are you sure you want to perform this action?</p>
        <div class="form-actions">
            <button id="confirm-yes" class="btn btn-danger">Yes, Continue</button>
            <button onclick="closeModal('confirm-modal')" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notification-modal" class="modal">
    <div class="modal-content">
        <h2 id="notification-title">Notification</h2>
        <p id="notification-message"></p>
        <div class="form-actions">
            <button onclick="closeModal('notification-modal')" class="btn btn-primary">OK</button>
        </div>
    </div>
</div> 