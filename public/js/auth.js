// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginMessage = document.getElementById('loginMessage');
    const registerMessage = document.getElementById('registerMessage');

    const showMessage = (element, message, type = 'error') => {
        if(!element) return;

        element.textContent = message;
        element.className = `message-box ${type}`;
        element.style.display = 'block';
        setTimeout(() => {
            element.style.display = 'none';
        }, 3000);
    };

    const handleApiResponse = async (response, messageElement) => {
        try{
            const responseData = await response.json();

            if(!responseData.trim()){
                throw new Error('Empty response');
            }

            try{
                const data = JSON.parse(responseData);

                if(data && data.error){
                    throw new Error(data.error);
                }

                return data;
            }catch(error){
                console.error('Error:', responseData);
                throw new Error(responseData);
            }
        }catch(error){
            console.error('Error:', error);
            showMessage(messageElement, 'An error occurred. Please try again.');
            return null;
        }
    }

    const makeApiCall = async (url, method, body, messageElement) => {
        try{

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });


            if(!response){
                console.error('Error:', response);
                throw new Error('Network error', response);
            }


            const data = await handleApiResponse(response, messageElement);

            if(!response.ok){
                console.error('Error:', data);
                throw new Error(data.message);
            }


            return data;
        }catch(error){
            console.error('Error:', error);
            showMessage(messageElement, 'An error occurred. Please try again.');
            return null;
        }
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = loginForm.email.value;
            const password = loginForm.password.value;

            if(!email || !password){
                showMessage(loginMessage, 'Please enter your email and password', 'error');
                return;
            }

           const data = await makeApiCall('/local_greeter/api/auth/login', 'POST', {email, password}, loginMessage);

            if(data.status === "success" && data){
                showMessage(loginMessage, data.message, 'success');
                sessionStorage.setItem('user', JSON.stringify(data.data));
                setTimeout(()=>{
                    window.location.href = '/local_greeter/account';
                },1500);
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = registerForm.username.value;
            const email = registerForm.email.value;
            const password = registerForm.password.value;
            const confirmPassword = registerForm['confirm-password'].value;

            console.log(username, email, password, confirmPassword); //DELETE LATER


            if(!username || !email || !password || !confirmPassword){
                showMessage(registerMessage, 'Please enter your username, email, password and confirm password', 'error');
                return;
            }

            if (password !== confirmPassword) {
                showMessage(registerMessage, 'Passwords do not match', 'error');
                return;
            }

            if(password.length < 6){
                showMessage(registerMessage, 'Password must be at least 6 characters', 'error');
                return;
            }

            const data = await makeApiCall('/local_greeter/api/auth/register', 'POST', {username, email, password}, registerMessage);

            if(data.status === "success" && data){
                showMessage(registerMessage, data.message, 'success');
                setTimeout(()=>{
                    window.location.href = '/local_greeter/login';
                },1500);
            }
        });
    }
}); 