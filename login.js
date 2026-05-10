document.getElementById('showPassword')
    .addEventListener('change', function () {
        document.getElementById('password').type =
            this.checked ? 'text' : 'password';
    });

document.getElementById('registerBtn')
    .addEventListener('click', function () {
        window.location.href = 'register.html';
    });

document.getElementById('loginForm')
    .addEventListener('submit', function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const errorMsg = document.getElementById('errorMsg');

        errorMsg.style.display = 'none';

        if (!username || !password) {
            errorMsg.innerText = '😿 Please fill in all fields!';
            errorMsg.style.display = 'block';
            return;
        }

        // SIMPLE LOGIN (NO BACKEND)
        localStorage.setItem('pawdiary_user', username);

        // GO TO DASHBOARD
        window.location.href = 'dashboard.html';
    });