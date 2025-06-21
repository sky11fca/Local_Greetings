document.addEventListener('DOMContentLoaded', () => {
    const loggedInContent = document.getElementById('logged-in-account-content');
    const loggedOutContent = document.getElementById('logged-out-account-content');
    const logoutButton = document.getElementById('logoutButton');

    function getUserDataFromSession() {
        const userJSON = sessionStorage.getItem('user');
        if (!userJSON) {
            return null;
        }
        try {
            return JSON.parse(userJSON);
        } catch (e) {
            console.error("Error parsing user data from sessionStorage:", e);
            return null;
        }
    }

    function updateAccountView() {
        const token = sessionStorage.getItem('jwt_token');
        const userData = getUserDataFromSession();

        if (token && userData) {
            loggedInContent.classList.remove('hidden');
            loggedInContent.classList.add('visible');
            loggedOutContent.classList.add('hidden');
            loggedOutContent.classList.remove('visible');

            const accountNameEl = document.getElementById('account-name');
            const accountEmailEl = document.getElementById('account-email');

            if(accountNameEl) accountNameEl.textContent = userData.username;
            if(accountEmailEl) accountEmailEl.textContent = userData.email;
        } else {
            loggedInContent.classList.add('hidden');
            loggedInContent.classList.remove('visible');
            loggedOutContent.classList.remove('hidden');
            loggedOutContent.classList.add('visible');
        }
    }

    if (logoutButton) {
        logoutButton.addEventListener('click', () => {
            document.cookie = "userData=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "userDataPersist=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            sessionStorage.clear();

            // Redirect to login page after logout
            window.location.href = '/local_greeter/login';
        });
    }

    // Initial view update on page load
    updateAccountView();
});