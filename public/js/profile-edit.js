document.addEventListener('DOMContentLoaded', () => {
    const profileEditForm = document.getElementById('profileEditForm');

    function populateForm() {
        const token = localStorage.getItem('jwt_token');
        if (!token) {
            window.location.href = '/local_greeter/login';
            return;
        }

        // Helper to decode user info from JWT
        function getUserFromJWT() {
            if (!token) return null;
            try {
                const payload = JSON.parse(atob(token.split('.')[1]));
                return payload.data || null;
            } catch (e) {
                return null;
            }
        }

        // Replace userData usage with getUserFromJWT
        const userData = getUserFromJWT();
        if (!userData || !userData.username) {
            // Handle not logged in or missing user info
            window.location.href = '/local_greeter/login';
        }
        document.getElementById('username').value = userData.username || '';
        document.getElementById('email').value = userData.email || '';
    }

    populateForm();

    if (profileEditForm) {
        profileEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const token = localStorage.getItem('jwt_token');
            if (!token) {
                alert('Your session has expired. Please log in again.');
                window.location.href = '/local_greeter/login';
                return;
            }

            const formData = {
                username: profileEditForm.username.value.trim(),
                email: profileEditForm.email.value.trim(),
                currentPassword: profileEditForm.old_password.value.trim(),
                newPassword: profileEditForm.new_password.value.trim(),
                confirmNewPassword: profileEditForm.confirm_new_password.value.trim()
            };

            // Validate password confirmation
            if (formData.newPassword && formData.newPassword !== formData.confirmNewPassword) {
                alert('New passwords do not match');
                return;
            }

            // Validate that at least one field is being updated
            if (!formData.username && !formData.email && !formData.newPassword) {
                alert('Please provide at least one field to update');
                return;
            }

            try {
                const response = await fetch("/local_greeter/api/index.php?action=updateProfile", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        username: formData.username || undefined,
                        email: formData.email || undefined,
                        password: formData.newPassword || undefined
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to update profile');
                }

                // Update localStorage with new token and user data
                if (result.token) {
                    localStorage.setItem('jwt_token', result.token);
                }
                
                if (result.data) {
                    localStorage.setItem('user', JSON.stringify(result.data));
                }

                alert('Profile updated successfully!');
                window.location.href = '/local_greeter/account';

            } catch (error) {
                console.error('Error updating profile:', error);
                alert(error.message || 'An error occurred while updating your profile');
            }
        });
    }
});