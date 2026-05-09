// ── CHECK LOGIN ──
const username = localStorage.getItem('pawdiary_user');
if (!username) window.location.href = 'index.html';

const PETS_KEY = 'pawdiary_pets_' + username;
const ACTS_KEY = 'pawdiary_acts_' + username;

function getPets() { return JSON.parse(localStorage.getItem(PETS_KEY) || '[]'); }
function getActs()  { return JSON.parse(localStorage.getItem(ACTS_KEY) || '[]'); }
function saveActs(a){ localStorage.setItem(ACTS_KEY, JSON.stringify(a)); }

function showAlert(msg, type='success') {
    const box = document.getElementById('alertBox');
    box.innerHTML = `<div class="alert-${type}">${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 3000);
}

// ── POPULATE PET DROPDOWNS ──
function populatePetDropdowns() {
    const pets = getPets();
    const opts = pets.map(p => `<option value="${p.name}">${p.name}</option>`).join('');
    document.getElementById('actPet').innerHTML     = '<option value="">-- Choose a pet --</option>' + opts;
    document.getElementById('editActPet').innerHTML = opts;
}

// ── SET TODAY'S DATE ──
document.getElementById('actDate').value = new Date().toISOString().split('T')[0];

// ── RENDER ACTIVITIES ──
function renderActivities(filter='') {
    const acts  = getActs();
    const tbody = document.getElementById('actTableBody');
    const noDiv = document.getElementById('noActs');
    const table = document.getElementById('actTable');

    const filtered = acts.filter(a =>
        a.petName.toLowerCase().includes(filter.toLowerCase()) ||
        a.type.toLowerCase().includes(filter.toLowerCase()) ||
        (a.desc || '').toLowerCase().includes(filter.toLowerCase())
    );

    if (filtered.length === 0) {
        tbody.innerHTML = '';
        noDiv.style.display = 'block';
        table.style.display = 'none';
        return;
    }

    noDiv.style.display  = 'none';
    table.style.display  = 'table';

    tbody.innerHTML = filtered.map((a, i) => `
        <tr>
            <td>🐾 ${a.petName}</td>
            <td><span class="badge">${a.type}</span></td>
            <td>${a.desc || ''}</td>
            <td>${a.date}</td>
            <td style="display:flex;gap:6px;">
                <button class="btn-secondary" onclick="openEdit(${acts.indexOf(a)})">✏️ Edit</button>
                <button class="btn-danger"    onclick="deleteAct(${acts.indexOf(a)})">🗑️ Delete</button>
            </td>
        </tr>`).join('');
}

// ── LOG ACTIVITY ──
document.getElementById('logActBtn').addEventListener('click', () => {
    const petName = document.getElementById('actPet').value;
    const type    = document.getElementById('actType').value;
    const date    = document.getElementById('actDate').value;
    const desc    = document.getElementById('actDesc').value.trim();

    if (!petName) { showAlert('😿 Please select a pet!', 'error'); return; }
    if (!date)    { showAlert('😿 Please select a date!', 'error'); return; }

    const acts = getActs();
    acts.unshift({ petName, type, date, desc });
    saveActs(acts);
    showAlert('✅ Activity logged! 📋');
    document.getElementById('actDesc').value = '';
    renderActivities();
});

// ── DELETE ──
function deleteAct(index) {
    if (!confirm('Delete this activity?')) return;
    const acts = getActs();
    acts.splice(index, 1);
    saveActs(acts);
    showAlert('✅ Activity deleted.');
    renderActivities();
}

// ── EDIT ──
let editIndex = -1;

function openEdit(index) {
    const acts = getActs();
    const act  = acts[index];
    editIndex  = index;
    document.getElementById('editActPet').value  = act.petName;
    document.getElementById('editActType').value = act.type;
    document.getElementById('editActDate').value = act.date;
    document.getElementById('editActDesc').value = act.desc;
    document.getElementById('editModal').classList.add('show');
}

document.getElementById('saveEditBtn').addEventListener('click', () => {
    const acts = getActs();
    acts[editIndex] = {
        petName: document.getElementById('editActPet').value,
        type:    document.getElementById('editActType').value,
        date:    document.getElementById('editActDate').value,
        desc:    document.getElementById('editActDesc').value,
    };
    saveActs(acts);
    document.getElementById('editModal').classList.remove('show');
    showAlert('✅ Activity updated! 📋');
    renderActivities();
});

document.getElementById('closeEditBtn').addEventListener('click', () => {
    document.getElementById('editModal').classList.remove('show');
});

// ── SEARCH ──
function filterActivities() {
    renderActivities(document.getElementById('actSearch').value);
}

// ── SORT ──
function sortTable(tableId, colIndex) {
    const table   = document.getElementById(tableId);
    const tbody   = table.querySelector('tbody');
    const rows    = Array.from(tbody.querySelectorAll('tr'));
    const currDir = table.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
    table.setAttribute('data-sort-dir', currDir);
    rows.sort((a, b) => {
        const aT = a.cells[colIndex]?.innerText.trim().toLowerCase() || '';
        const bT = b.cells[colIndex]?.innerText.trim().toLowerCase() || '';
        if (aT < bT) return currDir === 'asc' ? -1 : 1;
        if (aT > bT) return currDir === 'asc' ? 1 : -1;
        return 0;
    });
    table.querySelectorAll('th').forEach(th => {
        th.innerText = th.innerText.replace(' ▲','').replace(' ▼','').replace(' ⬍','');
        if (th.getAttribute('onclick')) th.innerText += ' ⬍';
    });
    const activeTh = table.querySelectorAll('th')[colIndex];
    if (activeTh) {
        activeTh.innerText = activeTh.innerText.replace(' ⬍','');
        activeTh.innerText += currDir === 'asc' ? ' ▲' : ' ▼';
    }
    rows.forEach(row => tbody.appendChild(row));
}

// ── LOGOUT ──
document.getElementById('logoutBtn').addEventListener('click', () => {
    localStorage.removeItem('pawdiary_user');
    window.location.href = 'index.html';
});

// ── INIT ──
populatePetDropdowns();
renderActivities();