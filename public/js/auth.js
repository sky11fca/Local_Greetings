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
    const showMessage = (element, message, type = 'error') => {
        if (!element) return;

        element.textContent = message;
        element.className = `message-box ${type}`;
        element.style.display = 'block';
        setTimeout(() => {
            element.style.display = 'none';
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
                throw new Error(data.message);
            }
            return data;
        } catch(error){
            showMessage(messageElement, error.message);
            return null;
        }
    };

    const storeAuthData = (token, userData) =>{
        const rememberMe = document.getElementById('remember-me');
        // Store JWT token in sessionStorage for authentication
        sessionStorage.setItem('jwt_token', token);
        sessionStorage.setItem('user', JSON.stringify(userData));
        setCookie('userData', token);
        if(rememberMe.checked){
            setCookie('userDataPersist', JSON.stringify(userData), 30);
        }

    };

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

            if(result.success){
                showMessage(loginMessage, "Login successful");

                storeAuthData(result.token, {
                    id: result.data.user_id,
                    username: result.data.username,
                    email: result.data.email
                });
                setTimeout(() => {
                    window.location.href = '/local_greeter/home';
                }, 1500)

            }
        })
    }
    if(registerForm){
        registerForm.addEventListener('submit', async (e) =>{
            e.preventDefault();
            const {username, email, password, 'confirm-password' : confirmPassword} = registerForm;
            if(!email.value  || !password.value || !confirmPassword.value){
                showMessage(registerMessage, "Please enter email, password and confirm password");
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
                showMessage(registerMessage, "Registration successful");
                setTimeout(() => {
                    window.location.href = '/local_greeter/login';
                }, 1500)
            }
        })
    }
});

