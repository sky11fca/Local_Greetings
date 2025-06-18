document.addEventListener('DOMContentLoaded', () => {
    const profileEditForm = document.getElementById('profileEditForm');
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function setCookie(name, value, days=7) {
        const date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/`;
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

    function populateForm() {
        const userData = getUserData();
        if (!userData) {
            window.location.href = '/local_greeter/login';
        }
        const {username, email} = userData;
        profileEditForm.username.value = userData.username || '';
        profileEditForm.email.value = userData.email || '';
    }

    populateForm();

    if (profileEditForm) {
        profileEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = {
                username: profileEditForm.username.value.trim(),
                email: profileEditForm.email.value.trim(),
                currentPassword: profileEditForm.old_password.value.trim(),
                newPassword: profileEditForm.new_password.value.trim(),
                confirmNewPassword: profileEditForm.confirm_new_password.value.trim()
            };

            if (formData.newPassword !== formData.confirmNewPassword && formData.newPassword) {
                alert('Passwords do not match');
                return;
            }

            const userData = getUserData();

            if(!userData || !userData.id)
            {
                alert('Failed to update profile');
                return;
            }

            try {
                const response = await fetch("/local_greeter/api/index.php?action=updateProfile", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        'user_id': userData.id,
                        'username': formData.username || userData.username,
                        'email': formData.email || userData.email,
                        'password': formData.newPassword || undefined
                    })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error('Failed to update profile');
                }

                const updatedUserData = {
                    id: userData.id,
                    username: formData.username,
                    email: formData.email,
                };
                setCookie('userData', JSON.stringify(updatedUserData));

                window.location.href = '/local_greeter/account';


            } catch (error) {
                alert(error.message);
                return;
            }

        });
    }
});