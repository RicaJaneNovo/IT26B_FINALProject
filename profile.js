// ── CHECK LOGIN ──
const username = localStorage.getItem('pawdiary_user');
if (!username) window.location.href = 'index.html';

const PETS_KEY    = 'pawdiary_pets_'    + username;
const PROFILE_KEY = 'pawdiary_profile_' + username;
const POSTS_KEY   = 'pawdiary_posts_'   + username;

function getPets()    { return JSON.parse(localStorage.getItem(PETS_KEY)    || '[]'); }
function getProfile() { return JSON.parse(localStorage.getItem(PROFILE_KEY) || '{}'); }
function getPosts()   { return JSON.parse(localStorage.getItem(POSTS_KEY)   || '[]'); }

function showAlert(msg, type='success') {
    const box = document.getElementById('alertBox');
    box.innerHTML = `<div class="alert-${type}">${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 3000);
}

// ── LOAD PROFILE ──
function loadProfile() {
    const profile = getProfile();
    document.getElementById('displayName').textContent =
        profile.fullName || username;
    document.getElementById('displayAt').textContent =
        '@' + username;
    document.getElementById('displayBio').textContent =
        profile.bio || 'No bio yet. Tell us about yourself! 🐾';
    document.getElementById('fullName').value = profile.fullName || '';
    document.getElementById('bio').value      = profile.bio      || '';

    if (profile.profilePic) {
        document.getElementById('profilePicDisplay').src = profile.profilePic;
    }
}

// ── POPULATE PET DROPDOWN ──
function populatePets() {
    const pets = getPets();
    const opts = pets.map(p =>
        `<option value="${p.name}">${p.name}</option>`
    ).join('');
    document.getElementById('postPet').innerHTML =
        '<option value="">-- Choose a pet --</option>' + opts;
}

// ── SAVE PROFILE ──
document.getElementById('saveProfileBtn').addEventListener('click', () => {
    const fullName = document.getElementById('fullName').value.trim();
    const bio      = document.getElementById('bio').value.trim();
    const fileInput= document.getElementById('profilePic');
    const profile  = getProfile();

    profile.fullName = fullName;
    profile.bio      = bio;

    if (fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            profile.profilePic = e.target.result;
            localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));
            document.getElementById('profilePicDisplay').src = e.target.result;
            showAlert('✅ Profile updated successfully!');
            loadProfile();
        };
        reader.readAsDataURL(fileInput.files[0]);
    } else {
        localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));
        showAlert('✅ Profile updated successfully!');
        loadProfile();
    }
});

// ── POST PET PHOTO ──
document.getElementById('postPhotoBtn').addEventListener('click', () => {
    const petName  = document.getElementById('postPet').value;
    const caption  = document.getElementById('postCaption').value.trim();
    const fileInput= document.getElementById('postPhoto');

    if (!petName) { showAlert('😿 Please select a pet!', 'error'); return; }
    if (!fileInput.files[0]) { showAlert('😿 Please choose a photo!', 'error'); return; }

    const reader = new FileReader();
    reader.onload = function(e) {
        const posts = getPosts();
        posts.unshift({
            petName, caption,
            image: e.target.result,
            date: new Date().toLocaleDateString('en-US', {
                month:'short', day:'numeric', year:'numeric'
            })
        });
        localStorage.setItem(POSTS_KEY, JSON.stringify(posts));
        showAlert('✅ Pet photo posted! 🐾');
        document.getElementById('postCaption').value = '';
        fileInput.value = '';
        renderGallery();
    };
    reader.readAsDataURL(fileInput.files[0]);
});

// ── RENDER GALLERY ──
function renderGallery() {
    const posts  = getPosts();
    const gallery= document.getElementById('gallery');
    const noDiv  = document.getElementById('noGallery');

    if (posts.length === 0) {
        gallery.innerHTML    = '';
        noDiv.style.display  = 'block';
        return;
    }

    noDiv.style.display  = 'none';
    gallery.innerHTML = posts.map(post => `
        <div class="gallery-card">
            <img src="${post.image}" alt="Pet Photo">
            <div class="card-info">
                <span class="pet-tag">🐶 ${post.petName}</span>
                <p class="caption">${post.caption || ''}</p>
                <p class="date">📅 ${post.date}</p>
            </div>
        </div>`).join('');
}

// ── LOGOUT ──
document.getElementById('logoutBtn').addEventListener('click', () => {
    localStorage.removeItem('pawdiary_user');
    window.location.href = 'index.html';
});

// ── INIT ──
loadProfile();
populatePets();
renderGallery();