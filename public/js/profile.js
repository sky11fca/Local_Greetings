document.addEventListener('DOMContentLoaded', () => {
    const loggedInContent = document.getElementById('logged-in-account-content');
    const loggedOutContent = document.getElementById('logged-out-account-content');
    const logoutButton = document.getElementById('logoutButton');
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
            const base64Payload = userData.split('.')[1];
            const payload = atob(base64Payload);
            return JSON.parse(payload);
        }catch(e)
        {
            console.error(e);
            return null;
        }
    }


    // Check for a developer override in the URL
    //const urlParams = new URLSearchParams(window.location.search);
    //const isDeveloper = urlParams.get('dev') === 'true';

    function updateAccountView() {
        const userData = getUserData();

        //const isLoggedIn = localStorage.getItem('isLoggedIn');
        if (userData) {
            loggedInContent.style.display = 'block';
            loggedOutContent.style.display = 'none';
            // Populate simulated user data
            document.getElementById('account-name').textContent = userData.username;
            document.getElementById('account-email').textContent = userData.email;
        } else {
            loggedInContent.style.display = 'none';
            loggedOutContent.style.display = 'block';
        }
    }

    if (logoutButton) {
        logoutButton.addEventListener('click', () => {
            //localStorage.removeItem('isLoggedIn');
            //alert('Logged out successfully! (Frontend simulation)');
            document.cookie = "userData=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            sessionStorage.removeItem('username');
            sessionStorage.removeItem('user');



            updateAccountView();
            window.location.href = '/local_greeter/login';
        });
    }

    updateAccountView(); // Initial view update

    const userData = getUserData();
    if(!userData)
    {
        window.location.href = '/local_greeter/login';
    }
});