document.addEventListener('DOMContentLoaded', () => {
    const profileEditForm = document.getElementById('profileEditForm');
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function getUserData()
    {
        const userData = getCookie('userData');
        if(!userData) return null;

        try{
            return JSON.parse(userData);
        }catch(e)
        {
            console.error(e);
            return null;
        }
    }



    if (profileEditForm) {
        profileEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const {username, email, currentPassword, newPassword, confirmNewPassword} = profileEditForm;

            if (newPassword.value !== confirmNewPassword.value) {
                alert('Passwords do not match');
                return;
            }
            userData = getUserData();

            try {
                const response = await fetch("/local_greeter/api/index.php?action=updateProfile", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        'user_id': userData.id,
                        'username': username.value || '',
                        'email': email.value || '',
                        'password': newPassword.value || ''
                    })
                });
                if (!response.ok) {
                    throw new Error('Failed to update profile');
                }
                setTimeout(() => {
                    window.location.href = '/local_greeter/app/views/account.html';
                }, 1500)
            } catch (error) {
                alert(error.message);
                return;
            }

        });
    }
});