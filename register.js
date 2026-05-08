// ── SHOW / HIDE PASSWORD ──
const showPasswordCheckbox = document.getElementById('showPassword');
const passwordInput = document.getElementById('password');

showPasswordCheckbox.addEventListener('change', function () {
    if (this.checked) {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
});

// ── BACK TO LOGIN BUTTON ──
const backBtn = document.getElementById('backBtn');

backBtn.addEventListener('click', function () {
    window.location.href = 'index.html';
});

// ── SHOW SUCCESS OR ERROR FROM URL ──
const urlParams = new URLSearchParams(window.location.search);

if (urlParams.get('success') === '1') {
    const successMsg = document.getElementById('successMsg');
    successMsg.style.display = 'block';
}

if (urlParams.get('error') === '1') {
    const errorMsg = document.getElementById('errorMsg');
    errorMsg.style.display = 'block';
}

// ── FORM VALIDATION BEFORE SUBMIT ──
const registerForm = document.getElementById('registerForm');

registerForm.addEventListener('submit', function (e) {
    const username = document.getElementById('username').value.trim();
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!username || !email || !password) {
        e.preventDefault();
        const errorMsg = document.getElementById('errorMsg');
        errorMsg.innerText = '😿 Please fill in all fields!';
        errorMsg.style.display = 'block';
        return;
    }

    if (password.length < 6) {
        e.preventDefault();
        const errorMsg = document.getElementById('errorMsg');
        errorMsg.innerText = '😿 Password must be at least 6 characters!';
        errorMsg.style.display = 'block';
        return;
    }
});