// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginMessage = document.getElementById('loginMessage');
    const registerMessage = document.getElementById('registerMessage');

    const setCookie = (name, value, days=1) => {
        const date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/`;
    }
    
    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
    
    const showMessage = (element, message, type = 'error') => {
        if (!element) return;

        element.textContent = message;
        element.className = `message-box ${type}`;
        element.classList.remove('hidden');
        setTimeout(() => {
            element.classList.add('hidden');
        }, 3000);
    };

    const submitForm = async (url, formData, messageElement) =>{
        try{
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();

            if(!response.ok || !data.success){
                throw new Error(data.message || 'An error occurred');
            }
            return data;
        } catch(error){
            showMessage(messageElement, error.message);
            return null;
        }
    };

    // Helper to decode user info from JWT
    function getUserFromJWT() {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) return null;
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            return payload.data || null;
        } catch (e) {
            return null;
        }
    }

    const storeAuthData = (token, userData) =>{
        // Store JWT token in sessionStorage for authentication
        sessionStorage.setItem('jwt_token', token);
        sessionStorage.setItem('user', JSON.stringify(userData));
    };

    // Helper to check if JWT is valid and not expired
    function isJWTValid() {
        const token = sessionStorage.getItem('jwt_token');
        if (!token) return false;
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            // Check for expiration
            if (!payload.exp || Date.now() >= payload.exp * 1000) {
                sessionStorage.removeItem('jwt_token');
                return false;
            }
            return true;
        } catch (e) {
            sessionStorage.removeItem('jwt_token');
            return false;
        }
    }

    const checkAuthStatus = () => {
        if (isJWTValid()) {
            // User is logged in, redirect to home if on login/register page
            const currentPath = window.location.pathname;
            if (currentPath.includes('/login') || currentPath.includes('/register')) {
                window.location.href = '/local_greeter/home';
            }
        }
    };

    // Run auth check on page load
    checkAuthStatus();

    if(loginForm){
        loginForm.addEventListener('submit', async (e) =>{
            e.preventDefault();

            const{email, password} = loginForm;

            if(!email.value  || !password.value){
                showMessage(loginMessage, "Please enter email and password");
                return;
            }
            
            const result = await submitForm(
                "/local_greeter/api/index.php?action=login",
                {
                    email: email.value,
                    password: password.value
                },
                loginMessage,
            );

            if(result && result.success){
                showMessage(loginMessage, "Login successful", "success");

                // Store the full user object, including is_admin
                storeAuthData(result.token, result.data);
                
                setTimeout(() => {
                    const user = result.data;
                    if (user.is_admin) {
                        window.location.href = '/local_greeter/admin';
                    } else {
                        window.location.href = '/local_greeter/home';
                    }
                }, 1500)
            }
        })
    }
    
    if(registerForm){
        registerForm.addEventListener('submit', async (e) =>{
            e.preventDefault();
            const {username, email, password, 'confirm-password' : confirmPassword} = registerForm;
            if(!username.value || !email.value  || !password.value || !confirmPassword.value){
                showMessage(registerMessage, "Please fill in all fields");
                return;
            }
            if(password.value !== confirmPassword.value){
                showMessage(registerMessage, "Passwords do not match");
                return;
            }

            if(password.value.length < 8){
                showMessage(registerMessage, "Password must be at least 8 characters long");
                return;
            }

            const result = await submitForm(
                "/local_greeter/api/index.php?action=register",
                {
                    username: username.value,
                    email: email.value,
                    password: password.value
                },
                registerMessage,
            );

            if(result?.success){
                showMessage(registerMessage, "Registration successful", "success");
                setTimeout(() => {
                    window.location.href = '/local_greeter/login';
                }, 1500)
            }
        })
    }
});

