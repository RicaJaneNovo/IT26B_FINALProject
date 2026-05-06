<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// --- ADD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_health'])) {
    $pet_id = $_POST['pet_id'];
    $type = $_POST['record_type'];
    $notes = $_POST['notes'];
    $date = $_POST['record_date'];
    mysqli_query($conn, "INSERT INTO health_records (pet_id, record_type, notes, record_date)
        VALUES ('$pet_id', '$type', '$notes', '$date')");
    $success = "Health record added! 🏥";
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM health_records WHERE record_id='".$_GET['delete']."'");
    $success = "Record deleted.";
}

// --- EDIT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_health'])) {
    $rec_id = $_POST['record_id'];
    $pet_id = $_POST['pet_id'];
    $type = $_POST['record_type'];
    $notes = $_POST['notes'];
    $date = $_POST['record_date'];
    mysqli_query($conn, "UPDATE health_records SET pet_id='$pet_id', record_type='$type',
        notes='$notes', record_date='$date' WHERE record_id='$rec_id'");
    $success = "Health record updated! 🏥";
}

$pets = mysqli_query($conn, "SELECT * FROM pets WHERE user_id='$user_id'");

// INNER JOIN + LEFT JOIN for health records
$records = mysqli_query($conn,
    "SELECT h.*, p.pet_name FROM health_records h
     INNER JOIN pets p ON h.pet_id = p.pet_id
     WHERE p.user_id = '$user_id'
     ORDER BY h.record_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PawDiary - Health Records</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:#f9f4ff; min-height:100vh; }
        .sidebar {
            position:fixed; top:0; left:0; width:220px; height:100%;
            background:white; border-right:2px solid #ffd6e7;
            display:flex; flex-direction:column; align-items:center; padding:30px 0; z-index:100;
        }
        .sidebar .logo { font-size:40px; margin-bottom:5px; animation:bounce 2s ease-in-out infinite; }
        @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)} }
        .sidebar h2 { color:#d63384; font-size:20px; margin-bottom:5px; }
        .sidebar p { color:#f48fb1; font-size:12px; margin-bottom:30px; }
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
        .panel {
            background:white; border-radius:16px; padding:25px;
            border:2px solid #ffd6e7; box-shadow:0 4px 15px rgba(214,51,132,0.08); margin-bottom:25px;
        }
        .panel h3 { color:#d63384; font-size:16px; margin-bottom:20px; padding-bottom:10px; border-bottom:2px dashed #ffd6e7; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .form-group { display:flex; flex-direction:column; gap:6px; }
        .form-group.full { grid-column:1/-1; }
        label { font-size:13px; color:#888; font-weight:600; }
        input[type="date"],textarea,select {
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
        th { background:#ffd6e7; color:#d63384; padding:10px 12px; text-align:left; font-weight:600; }
        td { padding:10px 12px; border-bottom:1px solid #f9f4ff; color:#555; }
        tr:hover td { background:#fff8fc; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:bold; background:#ffd6e7; color:#d63384; }
        .badge.green { background:#e0ffe0; color:#27ae60; }
        .badge.blue { background:#e0f0ff; color:#2980b9; }
        .badge.orange { background:#fff0e0; color:#e67e22; }
        .btn-edit { padding:5px 12px; background:#fff8fc; color:#7b2d8b; border:2px solid #7b2d8b; border-radius:8px; font-size:11px; cursor:pointer; transition:all 0.3s; }
        .btn-edit:hover { background:#7b2d8b; color:white; }
        .btn-delete { padding:5px 12px; background:#fff0f0; color:#e74c3c; border:2px solid #e74c3c; border-radius:8px; font-size:11px; cursor:pointer; transition:all 0.3s; }
        .btn-delete:hover { background:#e74c3c; color:white; }
        .search-bar { width:100%; padding:10px 14px; border:2px solid #ffd6e7; border-radius:12px; font-size:14px; outline:none; margin-bottom:15px; background:#fff8fc; transition:border 0.3s; }
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
        <a href="dashboard.php" class="nav-item"><span>🏠</span> Dashboard</a>
        <a href="pets.php" class="nav-item"><span>🐶</span> My Pets</a>
        <a href="activities.php" class="nav-item"><span>📋</span> Activities</a>
        <a href="health.php" class="nav-item active"><span>🏥</span> Health Records</a>
        <a href="profile.php" class="nav-item"><span>👤</span> My Profile</a>
    </nav>
    <button class="logout-btn" onclick="window.location.href='logout.php'">🚪 Logout</button>
</div>

<div class="main">
    <div class="topbar"><h1>🏥 Health Records</h1></div>

    <?php if ($success): ?><div class="success">✅ <?php echo $success; ?></div><?php endif; ?>

    <div class="panel">
        <h3>➕ Add Health Record</h3>
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
                    <label>Record Type</label>
                    <select name="record_type">
                        <option value="Vet Visit">🏥 Vet Visit</option>
                        <option value="Vaccination">💉 Vaccination</option>
                        <option value="Medication">💊 Medication</option>
                        <option value="Weight Check">⚖️ Weight Check</option>
                        <option value="Deworming">🐛 Deworming</option>
                        <option value="Other">🐾 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="record_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group full">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Add notes about this health record..."></textarea>
                </div>
            </div>
            <button type="submit" name="add_health" class="btn-save">🏥 Add Record</button>
        </form>
    </div>

    <div class="panel">
        <h3>🏥 Health Records</h3>
        <input class="search-bar" type="text" id="searchInput"
            placeholder="🔍 Search records..." onkeyup="searchTable()">
        <?php if (mysqli_num_rows($records) > 0): ?>
        <table id="healthTable">
            <thead>
                <tr>
                    <th>Pet</th>
                    <th>Record Type</th>
                    <th>Notes</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($records)):
                $badgeClass = match($row['record_type']) {
                    'Vaccination' => 'green',
                    'Vet Visit' => 'blue',
                    'Medication' => 'orange',
                    default => ''
                };
            ?>
            <tr>
                <td>🐾 <?php echo htmlspecialchars($row['pet_name']); ?></td>
                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $row['record_type']; ?></span></td>
                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                <td><?php echo $row['record_date']; ?></td>
                <td>
                    <button class="btn-edit" onclick="openEdit(
                        '<?php echo $row['record_id']; ?>',
                        '<?php echo $row['pet_id']; ?>',
                        '<?php echo $row['record_type']; ?>',
                        '<?php echo addslashes($row['notes']); ?>',
                        '<?php echo $row['record_date']; ?>'
                    )">✏️ Edit</button>
                    <button class="btn-delete" onclick="confirmDelete(<?php echo $row['record_id']; ?>)">🗑️ Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">🏥 No health records yet!</div>
        <?php endif; ?>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>✏️ Edit Health Record</h3>
        <form method="POST">
            <input type="hidden" name="record_id" id="edit_rec_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Pet</label>
                    <select name="pet_id" id="edit_pet_id">
                        <?php
                        $pets3 = mysqli_query($conn, "SELECT * FROM pets WHERE user_id='$user_id'");
                        while ($pet = mysqli_fetch_assoc($pets3)): ?>
                        <option value="<?php echo $pet['pet_id']; ?>">
                            <?php echo htmlspecialchars($pet['pet_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Record Type</label>
                    <select name="record_type" id="edit_type">
                        <option value="Vet Visit">🏥 Vet Visit</option>
                        <option value="Vaccination">💉 Vaccination</option>
                        <option value="Medication">💊 Medication</option>
                        <option value="Weight Check">⚖️ Weight Check</option>
                        <option value="Deworming">🐛 Deworming</option>
                        <option value="Other">🐾 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="record_date" id="edit_date">
                </div>
                <div class="form-group full">
                    <label>Notes</label>
                    <textarea name="notes" id="edit_notes"></textarea>
                </div>
            </div>
            <div class="modal-btns">
                <button type="submit" name="edit_health" class="btn-save">💾 Save</button>
                <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, pet_id, type, notes, date) {
    document.getElementById('edit_rec_id').value = id;
    document.getElementById('edit_pet_id').value = pet_id;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('edit_date').value = date;
    document.getElementById('editModal').classList.add('show');
}
function closeEdit() { document.getElementById('editModal').classList.remove('show'); }
function confirmDelete(id) {
    if (confirm('Delete this health record?')) window.location.href = 'health.php?delete=' + id;
}
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#healthTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
</body>
</html>