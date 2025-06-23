document.addEventListener('DOMContentLoaded', () => {
    const loggedInContent = document.getElementById('logged-in-account-content');
    const loggedOutContent = document.getElementById('logged-out-account-content');
    const logoutButton = document.getElementById('logoutButton');

    // Helper to decode user info from JWT
    function getUserFromJWT() {
        const token = localStorage.getItem('jwt_token');
        if (!token) return null;
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            return payload.data || null;
        } catch (e) {
            return null;
        }
    }

    // Helper to check if JWT is valid and not expired
    function isJWTValid() {
        const token = localStorage.getItem('jwt_token');
        if (!token) return false;
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            // Check for expiration
            if (!payload.exp || Date.now() >= payload.exp * 1000) {
                localStorage.removeItem('jwt_token');
                return false;
            }
            return true;
        } catch (e) {
            localStorage.removeItem('jwt_token');
            return false;
        }
    }

    function updateAccountView() {
        const token = localStorage.getItem('jwt_token');
        const userData = getUserFromJWT();
        if (token && userData && isJWTValid()) {
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
            localStorage.removeItem('jwt_token');
            // Redirect to login page after logout
            window.location.href = '/local_greeter/login';
        });
    }

    // Initial view update on page load
    updateAccountView();
});