// ============================================
// CHECK LOGIN — redirect if not logged in
// ============================================
const username = localStorage.getItem('pawdiary_user');
if (!username) {
    window.location.href = 'index.html';
}

// ── SHOW USERNAME ──
document.getElementById('welcomeUser').textContent = username;

// ============================================
// SAMPLE DATA (GitHub Pages Preview)
// ============================================
const samplePets = [
    { pet_name: 'Buddy',   species: 'Dog',    breed: 'Shih Tzu',  age: 2 },
    { pet_name: 'Kitty',   species: 'Cat',    breed: 'Persian',   age: 3 },
    { pet_name: 'Hoppy',   species: 'Rabbit', breed: 'Lop',       age: 1 },
];

const sampleActivities = [
    { pet_name: 'Buddy', species: 'Dog', activity_type: 'Feeding',  description: 'Fed in the morning',   activity_date: '2026-05-06' },
    { pet_name: 'Buddy', species: 'Dog', activity_type: 'Walking',  description: 'Morning walk',          activity_date: '2026-05-06' },
    { pet_name: 'Buddy', species: 'Dog', activity_type: 'Grooming', description: 'Bath time',             activity_date: '2026-05-05' },
    { pet_name: 'Buddy', species: 'Dog', activity_type: 'Playing',  description: 'Played with ball',      activity_date: '2026-05-05' },
    { pet_name: 'Kitty', species: 'Cat', activity_type: 'Feeding',  description: 'Fed wet food',          activity_date: '2026-05-06' },
    { pet_name: 'Kitty', species: 'Cat', activity_type: 'Playing',  description: 'Played with yarn',      activity_date: '2026-05-04' },
    { pet_name: 'Hoppy', species: 'Rabbit', activity_type: 'Feeding', description: 'Hay and carrots',    activity_date: '2026-05-06' },
];

const sampleHealth = [
    { pet_name: 'Buddy', species: 'Dog',    record_type: 'Vaccination', notes: 'Rabies vaccine',  record_date: '2026-04-10' },
    { pet_name: 'Kitty', species: 'Cat',    record_type: 'Vet Visit',   notes: 'Regular checkup', record_date: '2026-04-15' },
    { pet_name: 'Hoppy', species: 'Rabbit', record_type: 'Deworming',   notes: 'Deworming done',  record_date: '2026-04-20' },
];

// ============================================
// SUMMARY CARDS
// ============================================
document.getElementById('totalPets').textContent       = samplePets.length;
document.getElementById('totalActivities').textContent = sampleActivities.length;
document.getElementById('totalHealth').textContent     = sampleHealth.length;

// ============================================
// CHART DATA
// ============================================
const petActivityCount = {};
samplePets.forEach(p => petActivityCount[p.pet_name] = 0);
sampleActivities.forEach(a => {
    if (petActivityCount[a.pet_name] !== undefined) {
        petActivityCount[a.pet_name]++;
    }
});

const chartLabels  = Object.keys(petActivityCount);
const chartValues  = Object.values(petActivityCount);
const chartColors  = chartValues.map(v =>
    v === 0 ? 'rgba(231,76,60,0.7)' :
    v <= 3  ? 'rgba(241,196,15,0.7)' :
              'rgba(39,174,96,0.7)');
const chartBorders = chartValues.map(v =>
    v === 0 ? 'rgba(231,76,60,1)' :
    v <= 3  ? 'rgba(241,196,15,1)' :
              'rgba(39,174,96,1)');

const ctx = document.getElementById('activityChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Activities',
            data: chartValues,
            backgroundColor: chartColors,
            borderColor: chartBorders,
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    afterLabel: function(context) {
                        const val = context.parsed.y;
                        if (val == 0)  return '⚠️ No activities — needs attention!';
                        if (val <= 3)  return '🟡 Low activity — keep going!';
                        return '🟢 Great job! Very active & healthy!';
                    }
                }
            }
        },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// ============================================
// PET STATUS CARDS
// ============================================
const icons = {
    Dog: '🐶', Cat: '🐱', Rabbit: '🐰',
    Hamster: '🐹', Bird: '🐦', Fish: '🐠', Other: '🐾'
};

const statusCards = document.getElementById('statusCards');
samplePets.forEach(pet => {
    const total = petActivityCount[pet.pet_name] || 0;
    const cls    = total === 0 ? 'red' : total <= 3 ? 'yellow' : 'green';
    const status = total === 0 ? '⚠️ Needs attention!' :
                   total <= 3  ? '🟡 Low activity' : '💚 Healthy & Active!';
    const icon   = icons[pet.species] || '🐾';

    statusCards.innerHTML += `
        <div class="status-card ${cls}">
            <div class="s-icon">${icon}</div>
            <div class="s-name">${pet.pet_name}</div>
            <div class="s-count">${total} activities</div>
            <div style="margin-top:5px;font-size:11px;">${status}</div>
        </div>`;
});

// ============================================
// RECENT ACTIVITIES TABLE
// ============================================
const activityBody = document.getElementById('activityTableBody');
const recent = sampleActivities.slice(0, 5);

if (recent.length > 0) {
    recent.forEach(a => {
        activityBody.innerHTML += `
            <tr>
                <td>🐾 ${a.pet_name}</td>
                <td><span class="badge">${a.activity_type}</span></td>
                <td>${a.activity_date}</td>
            </tr>`;
    });
} else {
    document.getElementById('noActivity').style.display = 'block';
    document.getElementById('activityTable').style.display = 'none';
}

// ============================================
// INNER JOIN TABLE
// ============================================
const innerBody = document.getElementById('innerTableBody');
if (sampleActivities.length > 0) {
    sampleActivities.forEach(a => {
        innerBody.innerHTML += `
            <tr>
                <td>🐾 ${a.pet_name}</td>
                <td>${a.species}</td>
                <td><span class="badge">${a.activity_type}</span></td>
                <td>${a.description}</td>
                <td>${a.activity_date}</td>
            </tr>`;
    });
} else {
    document.getElementById('noInner').style.display = 'block';
}

// ============================================
// LEFT JOIN TABLE
// ============================================
const leftBody = document.getElementById('leftTableBody');
samplePets.forEach(pet => {
    const total  = petActivityCount[pet.pet_name] || 0;
    const status = total === 0 ? '🔴 No Activities' :
                   total <= 3  ? '🟡 Low Activity' : '🟢 Active & Healthy';
    leftBody.innerHTML += `
        <tr>
            <td>🐾 ${pet.pet_name}</td>
            <td>${pet.species}</td>
            <td>${pet.breed || 'Unknown'}</td>
            <td>${total}</td>
            <td>${status}</td>
        </tr>`;
});

if (samplePets.length === 0) {
    document.getElementById('noLeft').style.display = 'block';
}

// ============================================
// RIGHT JOIN TABLE
// ============================================
const rightBody = document.getElementById('rightTableBody');
if (sampleActivities.length > 0) {
    sampleActivities.forEach(a => {
        rightBody.innerHTML += `
            <tr>
                <td>🐾 ${a.pet_name || '<em style="color:#ccc">Deleted Pet</em>'}</td>
                <td>${a.species || 'N/A'}</td>
                <td><span class="badge">${a.activity_type}</span></td>
                <td>${a.activity_date}</td>
            </tr>`;
    });
} else {
    document.getElementById('noRight').style.display = 'block';
}

// ============================================
// FULL OUTER JOIN TABLE
// ============================================
const outerBody = document.getElementById('outerTableBody');
const allData = [];

samplePets.forEach(pet => {
    const matched = sampleHealth.filter(h => h.pet_name === pet.pet_name);
    if (matched.length > 0) {
        matched.forEach(h => allData.push({
            pet_name: pet.pet_name, species: pet.species,
            record_type: h.record_type, notes: h.notes, record_date: h.record_date
        }));
    } else {
        allData.push({
            pet_name: pet.pet_name, species: pet.species,
            record_type: null, notes: null, record_date: null
        });
    }
});

sampleHealth.forEach(h => {
    const exists = samplePets.find(p => p.pet_name === h.pet_name);
    if (!exists) {
        allData.push({
            pet_name: null, species: null,
            record_type: h.record_type, notes: h.notes, record_date: h.record_date
        });
    }
});

if (allData.length > 0) {
    allData.forEach(row => {
        outerBody.innerHTML += `
            <tr>
                <td>🐾 ${row.pet_name || '<em style="color:#ccc">No Pet</em>'}</td>
                <td>${row.species || 'N/A'}</td>
                <td>${row.record_type
                    ? `<span class="badge">${row.record_type}</span>`
                    : '<em style="color:#ccc">No Record</em>'}</td>
                <td>${row.notes || 'N/A'}</td>
                <td>${row.record_date || 'N/A'}</td>
            </tr>`;
    });
} else {
    document.getElementById('noOuter').style.display = 'block';
}

// ============================================
// JOIN TABS
// ============================================
function showJoin(type, btn) {
    document.querySelectorAll('.join-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.join-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('join-' + type).classList.add('active');
    btn.classList.add('active');
}

// ============================================
// SEARCH
// ============================================
function searchTable(tableId, value) {
    const input = value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}

// ============================================
// SORT
// ============================================
function sortTable(tableId, colIndex) {
    const table   = document.getElementById(tableId);
    const tbody   = table.querySelector('tbody');
    const rows    = Array.from(tbody.querySelectorAll('tr'));
    const currDir = table.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
    table.setAttribute('data-sort-dir', currDir);

    rows.sort((a, b) => {
        const aText = a.cells[colIndex]?.innerText.trim().toLowerCase() || '';
        const bText = b.cells[colIndex]?.innerText.trim().toLowerCase() || '';
        const aDate = new Date(aText);
        const bDate = new Date(bText);
        if (!isNaN(aDate) && !isNaN(bDate)) {
            return currDir === 'asc' ? aDate - bDate : bDate - aDate;
        }
        if (aText < bText) return currDir === 'asc' ? -1 : 1;
        if (aText > bText) return currDir === 'asc' ? 1 : -1;
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

// ============================================
// LOGOUT
// ============================================
document.getElementById('logoutBtn').addEventListener('click', function () {
    localStorage.removeItem('pawdiary_user');
    window.location.href = 'index.html';
});