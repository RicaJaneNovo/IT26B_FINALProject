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

// ── LOGIN FORM ──
document.getElementById('loginForm')
    .addEventListener('submit', function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const errorMsg = document.getElementById('errorMsg');

        // Hide error
        errorMsg.style.display = 'none';

        // Check empty fields
        if (!username || !password) {
            errorMsg.innerText = '😿 Please fill in all fields!';
            errorMsg.style.display = 'block';
            return;
        }

        // Save login locally
        localStorage.setItem('pawdiary_user', username);

        // Redirect to dashboard
        window.location.href = 'dashboard.html';
    });