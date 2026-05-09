// ── SHOW / HIDE PASSWORD ──
document.getElementById('showPassword')
    .addEventListener('change', function () {
        document.getElementById('password').type =
            this.checked ? 'text' : 'password';
    });

// ── REGISTER BUTTON ──
document.getElementById('registerBtn')
    .addEventListener('click', function () {
        window.location.href = 'register.html';
    });

// ── LOGIN FORM SUBMIT ──
document.getElementById('loginForm')
    .addEventListener('submit', function (e) {
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!username || !password) {
            document.getElementById('errorMsg').style.display = 'block';
            return;
        }

        // Check if user exists in localStorage
        const users = JSON.parse(localStorage.getItem('pawdiary_users') || '[]');
        const user  = users.find(u =>
            u.username === username && u.password === password
        );

        if (user) {
            localStorage.setItem('pawdiary_user', username);
            window.location.href = 'dashboard.html';
        } else {
            document.getElementById('errorMsg').style.display = 'block';
        }
    });