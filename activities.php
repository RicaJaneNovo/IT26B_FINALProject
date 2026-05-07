<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$success = "";
$error   = "";

// --- ADD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_activity'])) {
    $pet_id = $_POST['pet_id'];
    $type   = $_POST['activity_type'];
    $desc   = $_POST['description'];
    $date   = $_POST['activity_date'];
    if (mysqli_query($conn,
        "INSERT INTO activities (pet_id, activity_type, description, activity_date)
         VALUES ('$pet_id','$type','$desc','$date')"))
        $success = "Activity logged! 📋";
    else $error = "Something went wrong!";
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM activities WHERE activity_id='".$_GET['delete']."'");
    $success = "Activity deleted.";
}

// --- EDIT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_activity'])) {
    $act_id = $_POST['activity_id'];
    $pet_id = $_POST['pet_id'];
    $type   = $_POST['activity_type'];
    $desc   = $_POST['description'];
    $date   = $_POST['activity_date'];
    mysqli_query($conn,
        "UPDATE activities SET pet_id='$pet_id', activity_type='$type',
         description='$desc', activity_date='$date'
         WHERE activity_id='$act_id'");
    $success = "Activity updated! 📋";
}

$pets = mysqli_query($conn, "SELECT * FROM pets WHERE user_id='$user_id'");

// INNER JOIN for activities
$activities = mysqli_query($conn,
    "SELECT a.*, p.pet_name FROM activities a
     INNER JOIN pets p ON a.pet_id = p.pet_id
     WHERE p.user_id = '$user_id'
     ORDER BY a.activity_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawDiary - Activities</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f9f4ff; min-height:100vh; }
        .sidebar {
            position:fixed; top:0; left:0; width:220px; height:100%;
            background:white; border-right:2px solid #ffd6e7;
            display:flex; flex-direction:column; align-items:center; padding:30px 0; z-index:100;
        }
        .sidebar .logo { font-size:40px; margin-bottom:5px; animation:bounce 2s ease-in-out infinite; }
        @keyframes bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
        .sidebar h2 { color:#d63384; font-size:20px; margin-bottom:5px; }
        .sidebar p  { color:#f48fb1; font-size:12px; margin-bottom:30px; }
        .nav-menu { width:100%; padding:0 15px; }
        .nav-item {
            display:block; padding:12px 15px; margin-bottom:8px; border-radius:12px;
            text-decoration:none; color:#888; font-size:14px; font-weight:500; transition:all 0.3s;
        }
        .nav-item:hover,.nav-item.active { background:#ffd6e7; color:#d63384; }
        .nav-item span { margin-right:8px; font-size:16px; }
        .logout-btn {
            margin-top:auto; margin-bottom:20px; padding:10px 25px;
            background:white; color:#d63384; border:2px solid #d63384;
            border-radius:12px; cursor:pointer; font-size:14px; font-weight:bold; transition:all 0.3s;
        }
        .logout-btn:hover { background:#d63384; color:white; }
        .main { margin-left:220px; padding:30px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
        .topbar h1 { color:#d63384; font-size:24px; }
        .success { background:#e0ffe0; color:#27ae60; padding:12px; border-radius:10px; margin-bottom:20px; font-size:14px; }
        .error-msg { background:#ffe0e0; color:#c0392b; padding:12px; border-radius:10px; margin-bottom:20px; font-size:14px; }
        .panel {
            background:white; border-radius:16px; padding:25px;
            border:2px solid #ffd6e7; box-shadow:0 4px 15px rgba(214,51,132,0.08); margin-bottom:25px;
        }
        .panel h3 { color:#d63384; font-size:16px; margin-bottom:20px; padding-bottom:10px; border-bottom:2px dashed #ffd6e7; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .form-group { display:flex; flex-direction:column; gap:6px; }
        .form-group.full { grid-column:1/-1; }
        label { font-size:13px; color:#888; font-weight:600; }
        input[type="text"],input[type="date"],textarea,select {
            padding:10px 12px; border:2px solid #ffd6e7; border-radius:10px;
            font-size:14px; outline:none; background:#fff8fc;
            font-family:'Segoe UI',sans-serif; transition:border 0.3s;
        }
        input:focus,textarea:focus,select:focus { border-color:#d63384; }
        textarea { resize:vertical; min-height:70px; }
        .btn-save {
            padding:11px 25px; background:linear-gradient(135deg,#ff6b9d,#d63384);
            color:white; border:none; border-radius:12px;
            font-size:14px; font-weight:bold; cursor:pointer; margin-top:15px; transition:all 0.3s;
        }
        .btn-save:hover { transform:translateY(-2px); }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th {
            background:#ffd6e7; color:#d63384; padding:10px 12px;
            text-align:left; font-weight:600; user-select:none; transition:background 0.2s;
        }
        th[onclick] { cursor:pointer; }
        th[onclick]:hover { background:#ffb6d0; }
        td { padding:10px 12px; border-bottom:1px solid #f9f4ff; color:#555; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#fff8fc; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; background:#ffd6e7; color:#d63384; }
        .btn-edit { padding:5px 12px; background:#fff8fc; color:#7b2d8b; border:2px solid #7b2d8b; border-radius:8px; font-size:11px; cursor:pointer; transition:all 0.3s; }
        .btn-edit:hover { background:#7b2d8b; color:white; }
        .btn-delete { padding:5px 12px; background:#fff0f0; color:#e74c3c; border:2px solid #e74c3c; border-radius:8px; font-size:11px; cursor:pointer; transition:all 0.3s; }
        .btn-delete:hover { background:#e74c3c; color:white; }
        .search-sort-bar { display:flex; gap:10px; margin-bottom:15px; align-items:center; }
        .search-bar {
            flex:1; padding:8px 12px; border:2px solid #ffd6e7;
            border-radius:10px; font-size:13px; outline:none;
            background:#fff8fc; transition:border 0.3s;
        }
        .search-bar:focus { border-color:#d63384; }
        .no-data { text-align:center; color:#ccc; padding:30px; font-size:14px; }
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.3); z-index:999; align-items:center; justify-content:center; }
        .modal-overlay.show { display:flex; }
        .modal { background:white; border-radius:20px; padding:30px; width:480px; border:2px solid #ffd6e7; box-shadow:0 10px 40px rgba(214,51,132,0.2); }
        .modal h3 { color:#d63384; margin-bottom:20px; }
        .modal-btns { display:flex; gap:10px; margin-top:15px; }
        .btn-cancel { padding:10px 20px; background:white; color:#888; border:2px solid #ddd; border-radius:10px; cursor:pointer; }
    </style>
</head>
<body>
<div class="sidebar">
      <div class="logo">📖</div>
    <h2>PawDiary</h2>
    <p>Pet Activity Journal</p>
    <nav class="nav-menu">
        <a href="dashboard.php"  class="nav-item"><span>🏠</span> Dashboard</a>
        <a href="pets.php"       class="nav-item"><span>🐶</span> My Pets</a>
        <a href="activities.php" class="nav-item active"><span>📋</span> Activities</a>
        <a href="health.php"     class="nav-item"><span>🏥</span> Health Records</a>
        <a href="profile.php"    class="nav-item"><span>👤</span> My Profile</a>
    </nav>
    <button class="logout-btn" onclick="window.location.href='logout.php'">🚪 Logout</button>
</div>

<div class="main">
    <div class="topbar"><h1>📋 Activities</h1></div>

    <?php if ($success): ?><div class="success">✅ <?php echo $success; ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="error-msg">😿 <?php echo $error; ?></div><?php endif; ?>

    <div class="panel">
        <h3>➕ Log New Activity</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Pet</label>
                    <select name="pet_id" required>
                        <option value="">-- Choose a pet --</option>
                        <?php while ($pet = mysqli_fetch_assoc($pets)): ?>
                        <option value="<?php echo $pet['pet_id']; ?>">
                            <?php echo htmlspecialchars($pet['pet_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Activity Type</label>
                    <select name="activity_type">
                        <option value="Feeding">🍖 Feeding</option>
                        <option value="Walking">🚶 Walking</option>
                        <option value="Grooming">✂️ Grooming</option>
                        <option value="Playing">🎾 Playing</option>
                        <option value="Training">🏋️ Training</option>
                        <option value="Bathing">🛁 Bathing</option>
                        <option value="Other">🐾 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="activity_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe the activity..."></textarea>
                </div>
            </div>
            <button type="submit" name="add_activity" class="btn-save">📋 Log Activity</button>
        </form>
    </div>

    <div class="panel">
        <h3>📋 Activity Log</h3>
        <div class="search-sort-bar">
            <input class="search-bar" type="text"
                placeholder="🔍 Search activity..." onkeyup="searchTable('activityTable', this.value)">
        </div>
        <?php if (mysqli_num_rows($activities) > 0): ?>
        <table id="activityTable">
            <thead>
                <tr>
                    <th onclick="sortTable('activityTable', 0)">Pet ⬍</th>
                    <th onclick="sortTable('activityTable', 1)">Activity ⬍</th>
                    <th>Description</th>
                    <th onclick="sortTable('activityTable', 3)">Date ⬍</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($activities)): ?>
            <tr>
                <td>🐾 <?php echo htmlspecialchars($row['pet_name']); ?></td>
                <td><span class="badge"><?php echo $row['activity_type']; ?></span></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo $row['activity_date']; ?></td>
                <td>
                    <button class="btn-edit" onclick="openEdit(
                        '<?php echo $row['activity_id']; ?>',
                        '<?php echo $row['pet_id']; ?>',
                        '<?php echo $row['activity_type']; ?>',
                        '<?php echo addslashes($row['description']); ?>',
                        '<?php echo $row['activity_date']; ?>'
                    )">✏️ Edit</button>
                    <button class="btn-delete" onclick="confirmDelete(<?php echo $row['activity_id']; ?>)">🗑️ Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">📋 No activities logged yet!</div>
        <?php endif; ?>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>✏️ Edit Activity</h3>
        <form method="POST">
            <input type="hidden" name="activity_id" id="edit_act_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Pet</label>
                    <select name="pet_id" id="edit_pet_id">
                        <?php
                        $pets2 = mysqli_query($conn, "SELECT * FROM pets WHERE user_id='$user_id'");
                        while ($pet = mysqli_fetch_assoc($pets2)): ?>
                        <option value="<?php echo $pet['pet_id']; ?>">
                            <?php echo htmlspecialchars($pet['pet_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Activity Type</label>
                    <select name="activity_type" id="edit_type">
                        <option value="Feeding">🍖 Feeding</option>
                        <option value="Walking">🚶 Walking</option>
                        <option value="Grooming">✂️ Grooming</option>
                        <option value="Playing">🎾 Playing</option>
                        <option value="Training">🏋️ Training</option>
                        <option value="Bathing">🛁 Bathing</option>
                        <option value="Other">🐾 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="activity_date" id="edit_date">
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" id="edit_desc"></textarea>
                </div>
            </div>
            <div class="modal-btns">
                <button type="submit" name="edit_activity" class="btn-save">💾 Save</button>
                <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, pet_id, type, desc, date) {
    document.getElementById('edit_act_id').value  = id;
    document.getElementById('edit_pet_id').value  = pet_id;
    document.getElementById('edit_type').value    = type;
    document.getElementById('edit_desc').value    = desc;
    document.getElementById('edit_date').value    = date;
    document.getElementById('editModal').classList.add('show');
}
function closeEdit() { document.getElementById('editModal').classList.remove('show'); }
function confirmDelete(id) {
    if (confirm('Delete this activity?')) window.location.href = 'activities.php?delete=' + id;
}
function searchTable(tableId, value) {
    const input = value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
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
        if (!isNaN(aDate) && !isNaN(bDate)) return currDir === 'asc' ? aDate - bDate : bDate - aDate;
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
</script>
</body>
</html>