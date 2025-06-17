// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginMessage = document.getElementById('loginMessage');
    const registerMessage = document.getElementById('registerMessage');

    const showMessage = (element, message, type = 'error') => {
        if (!element) return;

        element.textContent = message;
        element.className = `message-box ${type}`;
        element.style.display = 'block';
        setTimeout(() => {
            element.style.display = 'none';
        }, 3000);
    };

    const handleResponse = async (response) => {
        const data = await response.json();
        if(!response.ok) {
            throw new Error(data.error || 'request failed');

        }
        return data;
    }

    const submitForm = async (url, formData, messageElement, successCallback) =>{
        try{
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            const data = await handleResponse(response);

            if(data.success){
                successCallback(data);
            }
            else showMessage(messageElement, data.error || "operation failed");
        } catch(error){
            showMessage(messageElement, error.message);
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
            await submitForm(
                "/local_greeter/api/index.php?action=login",
                {
                    email: email.value,
                    password: password.value
                },
                loginMessage,
                (data) =>{
                    showMessage(loginMessage, "Login successful");
                    sessionStorage.setItem('user',JSON.stringify(data.data));
                    setTimeout(() => {
                        window.location.href = '/local_greeter/app/views/account.html';
                    }, 1500)
                }
            )
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

            await submitForm(
                "/local_greeter/api/index.php?action=register",
                {
                    username: username.value,
                    email: email.value,
                    password: password.value
                },
                registerMessage,
                (data) =>{
                    showMessage(registerMessage, "Registration successful");
                    setTimeout(() => {
                        window.location.href = '/local_greeter/app/views/login.html';
                    }, 1500)
                }
            )
        })
    }
});

