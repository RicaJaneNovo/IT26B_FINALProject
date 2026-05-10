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

        const username   = document.getElementById('username').value.trim();
        const password   = document.getElementById('password').value.trim();
        const errorMsg   = document.getElementById('errorMsg');
        const successMsg = document.getElementById('successMsg');
        const loginBtn   = document.getElementById('loginBtn');

        // Hide messages
        errorMsg.style.display   = 'none';
        successMsg.style.display = 'none';

        // Validate
        if (!username || !password) {
            errorMsg.innerText     = '😿 Please fill in all fields!';
            errorMsg.style.display = 'block';
            return;
        }

        // Disable button while loading
        loginBtn.disabled    = true;
        loginBtn.textContent = 'Logging in...';

        // ── FETCH TO PHP ──
        fetch('http://localhost/pawdiary/login_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Save to localStorage
                localStorage.setItem('pawdiary_user',    data.username);
                localStorage.setItem('pawdiary_user_id', data.user_id);

                // Show success
                successMsg.innerText     = '✅ Login successful! Redirecting...';
                successMsg.style.display = 'block';

                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            } else {
                // Show error
                errorMsg.innerText     = '😿 ' + data.message;
                errorMsg.style.display = 'block';

                // Re-enable button
                loginBtn.disabled    = false;
                loginBtn.textContent = 'Login';
            }
        })
        .catch(err => {
            errorMsg.innerText     = '😿 Cannot connect to server! Make sure XAMPP is running.';
            errorMsg.style.display = 'block';
            loginBtn.disabled      = false;
            loginBtn.textContent   = 'Login';
        });
    });