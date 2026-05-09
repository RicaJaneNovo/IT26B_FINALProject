// ── CHECK LOGIN ──
const username = localStorage.getItem('pawdiary_user');
if (!username) window.location.href = 'index.html';

// ── SHOW USERNAME ──
document.getElementById('welcomeUser').textContent = username;

// ── STORAGE KEYS ──
const PETS_KEY   = 'pawdiary_pets_'   + username;
const ACTS_KEY   = 'pawdiary_acts_'   + username;
const HEALTH_KEY = 'pawdiary_health_' + username;

// ── GET DATA ──
const pets   = JSON.parse(localStorage.getItem(PETS_KEY)   || '[]');
const acts   = JSON.parse(localStorage.getItem(ACTS_KEY)   || '[]');
const health = JSON.parse(localStorage.getItem(HEALTH_KEY) || '[]');

// ── SUMMARY COUNTS ──
document.getElementById('totalPets').textContent       = pets.length;
document.getElementById('totalActivities').textContent = acts.length;
document.getElementById('totalHealth').textContent     = health.length;

// ── PET ICONS ──
const icons = {
    Dog:'🐶', Cat:'🐱', Rabbit:'🐰',
    Hamster:'🐹', Bird:'🐦', Fish:'🐠', Other:'🐾'
};

// ── ACTIVITY COUNT PER PET ──
const petCount = {};
pets.forEach(p => petCount[p.name] = 0);
acts.forEach(a => {
    if (petCount[a.petName] !== undefined) petCount[a.petName]++;
});

// ── CHART ──
if (pets.length > 0) {
    document.getElementById('noChart').style.display = 'none';

    const labels  = pets.map(p => p.name);
    const values  = pets.map(p => petCount[p.name] || 0);
    const colors  = values.map(v =>
        v === 0 ? 'rgba(231,76,60,0.7)' :
        v <= 3  ? 'rgba(241,196,15,0.7)' :
                  'rgba(39,174,96,0.7)');
    const borders = values.map(v =>
        v === 0 ? 'rgba(231,76,60,1)' :
        v <= 3  ? 'rgba(241,196,15,1)' :
                  'rgba(39,174,96,1)');

    new Chart(
        document.getElementById('activityChart').getContext('2d'),
        {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Activities',
                    data: values,
                    backgroundColor: colors,
                    borderColor: borders,
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
                            afterLabel: ctx => {
                                const v = ctx.parsed.y;
                                if (v === 0)  return '⚠️ Needs attention!';
                                if (v <= 3)   return '🟡 Low activity!';
                                return '🟢 Active & Healthy!';
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        }
    );

    // ── STATUS CARDS ──
    const statusCards = document.getElementById('statusCards');
    pets.forEach(pet => {
        const total  = petCount[pet.name] || 0;
        const cls    = total === 0 ? 'red' : total <= 3 ? 'yellow' : 'green';
        const status = total === 0
            ? '⚠️ Needs attention!'
            : total <= 3 ? '🟡 Low activity' : '💚 Healthy & Active!';
        statusCards.innerHTML += `
            <div class="status-card ${cls}">
                <div class="s-icon">${icons[pet.species] || '🐾'}</div>
                <div class="s-name">${pet.name}</div>
                <div class="s-count">${total} activities</div>
                <div class="s-status">${status}</div>
            </div>`;
    });

} else {
    document.getElementById('chartArea').style.display = 'none';
    document.getElementById('noChart').style.display   = 'block';
}

// ── RECENT ACTIVITIES TABLE ──
const actBody = document.getElementById('actTableBody');
const actTable= document.getElementById('actTable');
const noAct   = document.getElementById('noAct');
const recent  = acts.slice(0, 5);

if (recent.length > 0) {
    noAct.style.display    = 'none';
    actTable.style.display = 'table';
    recent.forEach(a => {
        actBody.innerHTML += `
            <tr>
                <td>🐾 ${a.petName}</td>
                <td><span class="badge">${a.type}</span></td>
                <td>${a.date}</td>
            </tr>`;
    });
}

// ── INNER JOIN ──
const innerBody = document.getElementById('innerBody');
const innerTable= document.getElementById('innerTable');
const noInner   = document.getElementById('noInner');

const innerData = acts.filter(a =>
    pets.find(p => p.name === a.petName)
);

if (innerData.length > 0) {
    noInner.style.display     = 'none';
    innerTable.style.display  = 'table';
    innerData.forEach(a => {
        innerBody.innerHTML += `
            <tr>
                <td>🐾 ${a.petName}</td>
                <td><span class="badge">${a.type}</span></td>
                <td>${a.desc || ''}</td>
                <td>${a.date}</td>
            </tr>`;
    });
}

// ── LEFT JOIN ──
const leftBody = document.getElementById('leftBody');
const leftTable= document.getElementById('leftTable');
const noLeft   = document.getElementById('noLeft');

if (pets.length > 0) {
    noLeft.style.display    = 'none';
    leftTable.style.display = 'table';
    pets.forEach(pet => {
        const total  = petCount[pet.name] || 0;
        const status = total === 0
            ? '🔴 No Activities'
            : total <= 3 ? '🟡 Low Activity' : '🟢 Active & Healthy';
        leftBody.innerHTML += `
            <tr>
                <td>🐾 ${pet.name}</td>
                <td>${pet.species}</td>
                <td>${pet.breed || 'Unknown'}</td>
                <td>${total}</td>
                <td>${status}</td>
            </tr>`;
    });
}

// ── RIGHT JOIN ──
const rightBody = document.getElementById('rightBody');
const rightTable= document.getElementById('rightTable');
const noRight   = document.getElementById('noRight');

if (acts.length > 0) {
    noRight.style.display    = 'none';
    rightTable.style.display = 'table';
    acts.forEach(a => {
        const pet = pets.find(p => p.name === a.petName);
        rightBody.innerHTML += `
            <tr>
                <td>🐾 ${pet ? a.petName : '<em style="color:#ccc">Deleted Pet</em>'}</td>
                <td><span class="badge">${a.type}</span></td>
                <td>${a.date}</td>
            </tr>`;
    });
}

// ── FULL OUTER JOIN ──
const outerBody = document.getElementById('outerBody');
const outerTable= document.getElementById('outerTable');
const noOuter   = document.getElementById('noOuter');
const outerData = [];

pets.forEach(pet => {
    const matched = health.filter(h => h.petName === pet.name);
    if (matched.length > 0) {
        matched.forEach(h => outerData.push({
            petName: pet.name, species: pet.species,
            type: h.type, notes: h.notes, date: h.date
        }));
    } else {
        outerData.push({
            petName: pet.name, species: pet.species,
            type: null, notes: null, date: null
        });
    }
});

health.forEach(h => {
    if (!pets.find(p => p.name === h.petName)) {
        outerData.push({
            petName: null, species: null,
            type: h.type, notes: h.notes, date: h.date
        });
    }
});

if (outerData.length > 0) {
    noOuter.style.display    = 'none';
    outerTable.style.display = 'table';
    outerData.forEach(row => {
        outerBody.innerHTML += `
            <tr>
                <td>🐾 ${row.petName || '<em style="color:#ccc">No Pet</em>'}</td>
                <td>${row.species || 'N/A'}</td>
                <td>${row.type
                    ? `<span class="badge">${row.type}</span>`
                    : '<em style="color:#ccc">No Record</em>'}</td>
                <td>${row.notes || 'N/A'}</td>
                <td>${row.date  || 'N/A'}</td>
            </tr>`;
    });
}

// ── JOIN TABS ──
function showJoin(type, btn) {
    document.querySelectorAll('.join-content')
        .forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.join-tab')
        .forEach(t => t.classList.remove('active'));
    document.getElementById('join-' + type).classList.add('active');
    btn.classList.add('active');
}

// ── SEARCH ──
function searchTable(tableId, value) {
    const input = value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display =
            row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}

// ── SORT ──
function sortTable(tableId, colIndex) {
    const table   = document.getElementById(tableId);
    const tbody   = table.querySelector('tbody');
    const rows    = Array.from(tbody.querySelectorAll('tr'));
    const currDir = table.getAttribute('data-sort-dir') === 'asc'
        ? 'desc' : 'asc';
    table.setAttribute('data-sort-dir', currDir);

    rows.sort((a, b) => {
        const aT = a.cells[colIndex]?.innerText.trim().toLowerCase() || '';
        const bT = b.cells[colIndex]?.innerText.trim().toLowerCase() || '';
        const aD = new Date(aT), bD = new Date(bT);
        if (!isNaN(aD) && !isNaN(bD))
            return currDir === 'asc' ? aD - bD : bD - aD;
        if (aT < bT) return currDir === 'asc' ? -1 : 1;
        if (aT > bT) return currDir === 'asc' ?  1 : -1;
        return 0;
    });

    table.querySelectorAll('th').forEach(th => {
        th.innerText = th.innerText
            .replace(' ▲','').replace(' ▼','').replace(' ⬍','');
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