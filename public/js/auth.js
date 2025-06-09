// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = loginForm.email.value;
            const password = loginForm.password.value;

            // Simulate a successful login for frontend demonstration
            console.log(`Attempting login with Email: ${email}, Password: ${password}`);
            alert('Login successful! Redirecting...');
            // In a real application, you'd receive a token and redirect based on backend response.
            // For demonstration, we just redirect.
            localStorage.setItem('isLoggedIn', 'true'); // Set login status
            setTimeout(() => {
                window.location.href = 'account.html'; // Redirect to account page after login
            }, 500); // Simulate network delay
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = registerForm.username.value;
            const email = registerForm.email.value;
            const password = registerForm.password.value;
            const confirmPassword = registerForm['confirm-password'].value;

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            // Simulate a successful registration for frontend demonstration
            console.log(`Attempting registration with Username: ${username}, Email: ${email}, Password: ${password}`);
            alert('Registration successful! Redirecting...');
            // In a real application, you'd receive a token and redirect based on backend response.
            // For demonstration, we just redirect.
            localStorage.setItem('isLoggedIn', 'true'); // Set login status
            setTimeout(() => {
                window.location.href = 'account.html'; // Redirect to account page after registration
            }, 500); // Simulate network delay
        });
    }
}); 