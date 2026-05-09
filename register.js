// ── SHOW / HIDE PASSWORD ──
document.getElementById('showPassword')
    .addEventListener('change', function () {
        document.getElementById('password').type =
            this.checked ? 'text' : 'password';
    });

// ── BACK TO LOGIN ──
document.getElementById('backBtn')
    .addEventListener('click', function () {
        window.location.href = 'index.html';
    });

// ── REGISTER FORM ──
document.getElementById('registerForm')
    .addEventListener('submit', function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const errorMsg   = document.getElementById('errorMsg');
        const successMsg = document.getElementById('successMsg');

        errorMsg.style.display   = 'none';
        successMsg.style.display = 'none';

        if (!username || !email || !password) {
            errorMsg.innerText     = '😿 Please fill in all fields!';
            errorMsg.style.display = 'block';
            return;
        }

        if (password.length < 6) {
            errorMsg.innerText     = '😿 Password must be at least 6 characters!';
            errorMsg.style.display = 'block';
            return;
        }

        // Check if username already exists
        const users = JSON.parse(
            localStorage.getItem('pawdiary_users') || '[]'
        );

        const exists = users.find(u => u.username === username);
        if (exists) {
            errorMsg.innerText     = '😿 Username already exists!';
            errorMsg.style.display = 'block';
            return;
        }

        // Save new user
        users.push({ username, email, password });
        localStorage.setItem('pawdiary_users', JSON.stringify(users));

        // Show success
        successMsg.style.display = 'block';

        // Clear form
        document.getElementById('username').value = '';
        document.getElementById('email').value    = '';
        document.getElementById('password').value = '';

        // Redirect to login after 2 seconds
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 2000);
    });