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

        const username    = document.getElementById('username').value.trim();
        const email       = document.getElementById('email').value.trim();
        const password    = document.getElementById('password').value.trim();
        const errorMsg    = document.getElementById('errorMsg');
        const successMsg  = document.getElementById('successMsg');
        const registerBtn = document.getElementById('registerBtn');

        // Hide messages
        errorMsg.style.display   = 'none';
        successMsg.style.display = 'none';

        // Validate fields
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

        // Email format check
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMsg.innerText     = '😿 Please enter a valid email!';
            errorMsg.style.display = 'block';
            return;
        }

        // Disable button while loading
        registerBtn.disabled    = true;
        registerBtn.textContent = 'Creating account...';

        // ── FETCH TO PHP ──
        fetch('http://localhost/pawdiary/register_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Show success
                successMsg.innerText     = '🎉 ' + data.message + ' Redirecting...';
                successMsg.style.display = 'block';

                // Clear form
                document.getElementById('username').value = '';
                document.getElementById('email').value    = '';
                document.getElementById('password').value = '';

                // Redirect to login after 2 seconds
                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 2000);

            } else {
                // Show error
                errorMsg.innerText     = '😿 ' + data.message;
                errorMsg.style.display = 'block';

                // Re-enable button
                registerBtn.disabled    = false;
                registerBtn.textContent = 'Create Account';
            }
        })
        .catch(err => {
            errorMsg.innerText     =
                '😿 Cannot connect to server! Make sure XAMPP is running.';
            errorMsg.style.display = 'block';
            registerBtn.disabled    = false;
            registerBtn.textContent = 'Create Account';
        });
    });