// ── CHECK LOGIN ──
const username = localStorage.getItem('pawdiary_user');
if (!username) window.location.href = 'index.html';

// ── STORAGE KEY ──
const PETS_KEY = 'pawdiary_pets_' + username;
const ACTS_KEY = 'pawdiary_acts_' + username;

// ── GET/SAVE PETS ──
function getPets() {
    return JSON.parse(localStorage.getItem(PETS_KEY) || '[]');
}

function savePets(pets) {
    localStorage.setItem(PETS_KEY, JSON.stringify(pets));
}

// ── SPECIES ICONS ──
const icons = {
    Dog:'🐶', Cat:'🐱', Rabbit:'🐰',
    Hamster:'🐹', Bird:'🐦', Fish:'🐠', Other:'🐾'
};

// ── SHOW ALERT ──
function showAlert(msg, type='success') {
    const box = document.getElementById('alertBox');
    box.innerHTML = `<div class="alert-${type}">${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 3000);
}

// ── RENDER PETS ──
function renderPets(filter='') {
    const pets = getPets();
    const acts = JSON.parse(localStorage.getItem(ACTS_KEY) || '[]');
    const grid  = document.getElementById('petGrid');
    const noDiv = document.getElementById('noPets');
    const filtered = pets.filter(p =>
        p.name.toLowerCase().includes(filter.toLowerCase())
    );

    if (filtered.length === 0) {
        grid.innerHTML = '';
        noDiv.style.display = 'block';
        return;
    }

    noDiv.style.display = 'none';
    grid.innerHTML = filtered.map((pet, i) => {
        const total = acts.filter(a => a.petName === pet.name).length;
        const icon  = icons[pet.species] || '🐾';
        return `
        <div class="pet-card">
            <div class="pet-icon">${icon}</div>
            <h4>${pet.name}</h4>
            <p class="pet-species">${pet.species}</p>
            <p class="pet-breed">${pet.breed || 'Unknown breed'}</p>
            <span class="pet-badge">Age: ${pet.age || '?'} yrs | ${total} activities</span>
            <div class="pet-actions">
                <button class="btn-secondary" onclick="openEdit(${i})">✏️ Edit</button>
                <button class="btn-danger"    onclick="deletePet(${i})">🗑️ Delete</button>
            </div>
        </div>`;
    }).join('');
}

// ── ADD PET ──
document.getElementById('addPetBtn').addEventListener('click', () => {
    const name    = document.getElementById('petName').value.trim();
    const species = document.getElementById('petSpecies').value;
    const breed   = document.getElementById('petBreed').value.trim();
    const age     = document.getElementById('petAge').value;

    if (!name) { showAlert('😿 Please enter a pet name!', 'error'); return; }

    const pets = getPets();
    pets.unshift({ name, species, breed, age });
    savePets(pets);
    showAlert('✅ Pet added successfully! 🐾');
    document.getElementById('petName').value  = '';
    document.getElementById('petBreed').value = '';
    document.getElementById('petAge').value   = '';
    renderPets();
});

// ── DELETE PET ──
function deletePet(index) {
    if (!confirm('Are you sure you want to remove this pet? 🐾')) return;
    const pets = getPets();
    pets.splice(index, 1);
    savePets(pets);
    showAlert('✅ Pet removed.');
    renderPets();
}

// ── EDIT PET ──
let editIndex = -1;

function openEdit(index) {
    const pets = getPets();
    const pet  = pets[index];
    editIndex  = index;
    document.getElementById('editPetName').value    = pet.name;
    document.getElementById('editPetSpecies').value = pet.species;
    document.getElementById('editPetBreed').value   = pet.breed;
    document.getElementById('editPetAge').value     = pet.age;
    document.getElementById('editModal').classList.add('show');
}

document.getElementById('saveEditBtn').addEventListener('click', () => {
    const pets = getPets();
    pets[editIndex] = {
        name:    document.getElementById('editPetName').value.trim(),
        species: document.getElementById('editPetSpecies').value,
        breed:   document.getElementById('editPetBreed').value.trim(),
        age:     document.getElementById('editPetAge').value,
    };
    savePets(pets);
    document.getElementById('editModal').classList.remove('show');
    showAlert('✅ Pet updated! 🐾');
    renderPets();
});

document.getElementById('closeEditBtn').addEventListener('click', () => {
    document.getElementById('editModal').classList.remove('show');
});

// ── SEARCH ──
function filterPets() {
    renderPets(document.getElementById('petSearch').value);
}

// ── LOGOUT ──
document.getElementById('logoutBtn').addEventListener('click', () => {
    localStorage.removeItem('pawdiary_user');
    window.location.href = 'index.html';
});

// ── INIT ──
renderPets();