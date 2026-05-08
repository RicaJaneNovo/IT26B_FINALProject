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

// ── REGISTER BUTTON ──
const registerBtn = document.getElementById('registerBtn');

registerBtn.addEventListener('click', function () {
    window.location.href = 'register.html';
});

// ── SHOW ERROR IF LOGIN FAILED ──
// This checks the URL for an error parameter
// e.g. index.php?error=1
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('error') === '1') {
    const errorMsg = document.getElementById('errorMsg');
    errorMsg.style.display = 'block';
}