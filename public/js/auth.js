// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        console.log('Login form found');
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = loginForm.email.value;
            const password = loginForm.password.value;

            console.log('Login attempt:', { email, password });

            // Simulate a successful login for frontend demonstration
            try {
                // Set login status
                localStorage.setItem('isLoggedIn', 'true');
                console.log('Login status set to true');
                
                // Show success message
                alert('Login successful! Redirecting...');
                
                // Redirect to account page
                window.location.href = 'account.html';
            } catch (error) {
                console.error('Login error:', error);
                alert('An error occurred during login. Please try again.');
            }
        });
    } else {
        console.log('Login form not found');
    }

    if (registerForm) {
        console.log('Register form found');
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = registerForm.username.value;
            const email = registerForm.email.value;
            const password = registerForm.password.value;
            const confirmPassword = registerForm['confirm-password'].value;

            console.log('Registration attempt:', { username, email });

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            // Simulate a successful registration for frontend demonstration
            try {
                // Set login status
                localStorage.setItem('isLoggedIn', 'true');
                console.log('Registration successful, login status set to true');
                
                // Show success message
                alert('Registration successful! Redirecting...');
                
                // Redirect to account page
                window.location.href = 'account.html';
            } catch (error) {
                console.error('Registration error:', error);
                alert('An error occurred during registration. Please try again.');
            }
        });
    } else {
        console.log('Register form not found');
    }
}); 