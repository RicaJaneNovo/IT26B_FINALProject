// ── CHECK LOGIN ──
const username = localStorage.getItem('pawdiary_user');
if (!username) window.location.href = 'index.html';

const PETS_KEY   = 'pawdiary_pets_'   + username;
const HEALTH_KEY = 'pawdiary_health_' + username;

function getPets()   { return JSON.parse(localStorage.getItem(PETS_KEY)   || '[]'); }
function getHealth() { return JSON.parse(localStorage.getItem(HEALTH_KEY) || '[]'); }
function saveHealth(h){ localStorage.setItem(HEALTH_KEY, JSON.stringify(h)); }

function showAlert(msg, type='success') {
    const box = document.getElementById('alertBox');
    box.innerHTML = `<div class="alert-${type}">${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 3000);
}

function getBadgeClass(type) {
    if (type === 'Vaccination') return 'green';
    if (type === 'Vet Visit')   return 'blue';
    if (type === 'Medication')  return 'orange';
    return '';
}

// ── POPULATE DROPDOWNS ──
function populatePetDropdowns() {
    const pets = getPets();
    const opts = pets.map(p => `<option value="${p.name}">${p.name}</option>`).join('');
    document.getElementById('healthPet').innerHTML     = '<option value="">-- Choose a pet --</option>' + opts;
    document.getElementById('editHealthPet').innerHTML = opts;
}

// ── SET TODAY ──
document.getElementById('healthDate').value = new Date().toISOString().split('T')[0];

// ── RENDER ──
function renderHealth(filter='') {
    const records = getHealth();
    const tbody   = document.getElementById('healthTableBody');
    const noDiv   = document.getElementById('noHealth');
    const table   = document.getElementById('healthTable');

    const filtered = records.filter(r =>
        r.petName.toLowerCase().includes(filter.toLowerCase()) ||
        r.type.toLowerCase().includes(filter.toLowerCase()) ||
        (r.notes || '').toLowerCase().includes(filter.toLowerCase())
    );

    if (filtered.length === 0) {
        tbody.innerHTML = '';
        noDiv.style.display = 'block';
        table.style.display = 'none';
        return;
    }

    noDiv.style.display  = 'none';
    table.style.display  = 'table';

    tbody.innerHTML = filtered.map((r, i) => `
        <tr>
            <td>🐾 ${r.petName}</td>
            <td><span class="badge ${getBadgeClass(r.type)}">${r.type}</span></td>
            <td>${r.notes || ''}</td>
            <td>${r.date}</td>
            <td style="display:flex;gap:6px;">
                <button class="btn-secondary" onclick="openEdit(${records.indexOf(r)})">✏️ Edit</button>
                <button class="btn-danger"    onclick="deleteHealth(${records.indexOf(r)})">🗑️ Delete</button>
            </td>
        </tr>`).join('');
}

// ── ADD ──
document.getElementById('addHealthBtn').addEventListener('click', () => {
    const petName = document.getElementById('healthPet').value;
    const type    = document.getElementById('healthType').value;
    const date    = document.getElementById('healthDate').value;
    const notes   = document.getElementById('healthNotes').value.trim();

    if (!petName) { showAlert('😿 Please select a pet!', 'error'); return; }
    if (!date)    { showAlert('😿 Please select a date!', 'error'); return; }

    const records = getHealth();
    records.unshift({ petName, type, date, notes });
    saveHealth(records);
    showAlert('✅ Health record added! 🏥');
    document.getElementById('healthNotes').value = '';
    renderHealth();
});

// ── DELETE ──
function deleteHealth(index) {
    if (!confirm('Delete this health record?')) return;
    const records = getHealth();
    records.splice(index, 1);
    saveHealth(records);
    showAlert('✅ Record deleted.');
    renderHealth();
}

// ── EDIT ──
let editIndex = -1;

function openEdit(index) {
    const records = getHealth();
    const rec     = records[index];
    editIndex     = index;
    document.getElementById('editHealthPet').value  = rec.petName;
    document.getElementById('editHealthType').value = rec.type;
    document.getElementById('editHealthDate').value = rec.date;
    document.getElementById('editHealthNotes').value= rec.notes;
    document.getElementById('editModal').classList.add('show');
}

document.getElementById('saveEditBtn').addEventListener('click', () => {
    const records = getHealth();
    records[editIndex] = {
        petName: document.getElementById('editHealthPet').value,
        type:    document.getElementById('editHealthType').value,
        date:    document.getElementById('editHealthDate').value,
        notes:   document.getElementById('editHealthNotes').value,
    };
    saveHealth(records);
    document.getElementById('editModal').classList.remove('show');
    showAlert('✅ Health record updated! 🏥');
    renderHealth();
});

document.getElementById('closeEditBtn').addEventListener('click', () => {
    document.getElementById('editModal').classList.remove('show');
});

// ── SEARCH ──
function filterHealth() {
    renderHealth(document.getElementById('healthSearch').value);
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
renderHealth();