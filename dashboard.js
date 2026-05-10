// CHECK LOGIN
const user = localStorage.getItem('pawdiary_user');

if (!user) {
    window.location.href = 'login.html';
}

// SHOW USERNAME
const welcomeUser = document.getElementById('welcomeUser');
if (welcomeUser) {
    welcomeUser.textContent = user;
}

// LOGOUT
const logoutBtn = document.getElementById('logoutBtn');

if (logoutBtn) {
    logoutBtn.addEventListener('click', function () {
        localStorage.removeItem('pawdiary_user');
        window.location.href = 'login.html';
    });
}