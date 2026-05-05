<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$success = "";
$error = "";

// --- ADD PET ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $pet_name = $_POST['pet_name'];
    $species  = $_POST['species'];
    $breed    = $_POST['breed'];
    $age      = $_POST['age'];
    $query = "INSERT INTO pets (user_id, pet_name, species, breed, age)
              VALUES ('$user_id', '$pet_name', '$species', '$breed', '$age')";
    if (mysqli_query($conn, $query)) $success = "Pet added successfully! 🐾";
    else $error = "Something went wrong!";
}

// --- DELETE PET ---
if (isset($_GET['delete'])) {
    $pet_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM activities WHERE pet_id='$pet_id'");
    mysqli_query($conn, "DELETE FROM health_records WHERE pet_id='$pet_id'");
    mysqli_query($conn, "DELETE FROM pets WHERE pet_id='$pet_id' AND user_id='$user_id'");
    $success = "Pet removed.";
}

// --- EDIT PET ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pet'])) {
    $pet_id   = $_POST['pet_id'];
    $pet_name = $_POST['pet_name'];
    $species  = $_POST['species'];
    $breed    = $_POST['breed'];
    $age      = $_POST['age'];
    $query = "UPDATE pets SET pet_name='$pet_name', species='$species',
              breed='$breed', age='$age'
              WHERE pet_id='$pet_id' AND user_id='$user_id'";
    if (mysqli_query($conn, $query)) $success = "Pet updated! 🐾";
    else $error = "Something went wrong!";
}

// --- FETCH PETS (LEFT JOIN with activity count) ---
$pets = mysqli_query($conn,
    "SELECT p.*, COUNT(a.activity_id) as total_activities
     FROM pets p
     LEFT JOIN activities a ON p.pet_id = a.pet_id
     WHERE p.user_id = '$user_id'
     GROUP BY p.pet_id
     ORDER BY p.pet_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawDiary - My Pets</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f9f4ff; min-height: 100vh; }

        .sidebar {
            position: fixed; top: 0; left: 0;
            width: 220px; height: 100%;
            background: white; border-right: 2px solid #ffd6e7;
            display: flex; flex-direction: column;
            align-items: center; padding: 30px 0; z-index: 100;
        }
        .sidebar .logo { font-size: 40px; margin-bottom: 5px; animation: bounce 2s ease-in-out infinite; }
        @keyframes bounce { 0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)} }
        .sidebar h2 { color: #d63384; font-size: 20px; margin-bottom: 5px; }
        .sidebar p { color: #f48fb1; font-size: 12px; margin-bottom: 30px; }
        .nav-menu { width: 100%; padding: 0 15px; }
        .nav-item {
            display: block; padding: 12px 15px; margin-bottom: 8px;
            border-radius: 12px; text-decoration: none;
            color: #888; font-size: 14px; font-weight: 500; transition: all 0.3s;
        }
        .nav-item:hover, .nav-item.active { background: #ffd6e7; color: #d63384; }
        .nav-item span { margin-right: 8px; font-size: 16px; }
        .logout-btn {
            margin-top: auto; margin-bottom: 20px;
            padding: 10px 25px; background: white; color: #d63384;
            border: 2px solid #d63384; border-radius: 12px;
            cursor: pointer; font-size: 14px; font-weight: bold; transition: all 0.3s;
        }
        .logout-btn:hover { background: #d63384; color: white; }

        .main { margin-left: 220px; padding: 30px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .topbar h1 { color: #d63384; font-size: 24px; }

        .success { background: #e0ffe0; color: #27ae60; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .error-msg { background: #ffe0e0; color: #c0392b; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }

        .panel {
            background: white; border-radius: 16px; padding: 25px;
            border: 2px solid #ffd6e7;
            box-shadow: 0 4px 15px rgba(214,51,132,0.08); margin-bottom: 25px;
        }
        .panel h3 {
            color: #d63384; font-size: 16px; margin-bottom: 20px;
            padding-bottom: 10px; border-bottom: 2px dashed #ffd6e7;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        label { font-size: 13px; color: #888; font-weight: 600; }
        input[type="text"], input[type="number"], select {
            padding: 10px 12px; border: 2px solid #ffd6e7;
            border-radius: 10px; font-size: 14px; outline: none;
            background: #fff8fc; transition: border 0.3s;
        }
        input:focus, select:focus { border-color: #d63384; }

        .btn-save {
            padding: 11px 25px;
            background: linear-gradient(135deg, #ff6b9d, #d63384);
            color: white; border: none; border-radius: 12px;
            font-size: 14px; font-weight: bold; cursor: pointer;
            margin-top: 15px; transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(214,51,132,0.3);
        }
        .btn-save:hover { transform: translateY(-2px); }

        /* PETS GRID */
        .pets-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .pet-card {
            background: white; border-radius: 16px;
            border: 2px solid #ffd6e7; padding: 20px;
            text-align: center; transition: transform 0.3s;
            box-shadow: 0 4px 15px rgba(214,51,132,0.06);
        }
        .pet-card:hover { transform: translateY(-4px); }

        .pet-icon { font-size: 50px; margin-bottom: 10px; }

        .pet-card h4 { color: #d63384; font-size: 18px; margin-bottom: 5px; }
        .pet-card .species { color: #aaa; font-size: 13px; margin-bottom: 3px; }
        .pet-card .breed { color: #bbb; font-size: 12px; margin-bottom: 8px; }

        .pet-badge {
            display: inline-block; background: #ffd6e7;
            color: #d63384; font-size: 11px;
            font-weight: bold; padding: 3px 10px;
            border-radius: 20px; margin-bottom: 15px;
        }

        .pet-actions { display: flex; gap: 8px; justify-content: center; }

        .btn-edit {
            padding: 7px 16px; background: #fff8fc;
            color: #7b2d8b; border: 2px solid #7b2d8b;
            border-radius: 10px; font-size: 12px;
            cursor: pointer; transition: all 0.3s;
        }
        .btn-edit:hover { background: #7b2d8b; color: white; }

        .btn-delete {
            padding: 7px 16px; background: #fff0f0;
            color: #e74c3c; border: 2px solid #e74c3c;
            border-radius: 10px; font-size: 12px;
            cursor: pointer; transition: all 0.3s;
        }
        .btn-delete:hover { background: #e74c3c; color: white; }

        .no-pets { text-align: center; color: #ccc; padding: 40px; font-size: 14px; }

        /* MODAL */
        .modal-overlay {
            display: none; position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.3); z-index: 999;
            align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: white; border-radius: 20px;
            padding: 30px; width: 450px;
            border: 2px solid #ffd6e7;
            box-shadow: 0 10px 40px rgba(214,51,132,0.2);
        }
        .modal h3 { color: #d63384; margin-bottom: 20px; }
        .modal-btns { display: flex; gap: 10px; margin-top: 15px; }
        .btn-cancel {
            padding: 10px 20px; background: white;
            color: #888; border: 2px solid #ddd;
            border-radius: 10px; cursor: pointer; font-size: 14px;
        }

        .search-bar {
            width: 100%; padding: 10px 14px;
            border: 2px solid #ffd6e7; border-radius: 12px;
            font-size: 14px; outline: none; margin-bottom: 20px;
            background: #fff8fc; transition: border 0.3s;
        }
        .search-bar:focus { border-color: #d63384; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">🐾</div>
    <h2>PawDiary</h2>
    <p>Pet Activity Journal</p>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item"><span>🏠</span> Dashboard</a>
        <a href="pets.php" class="nav-item active"><span>🐶</span> My Pets</a>
        <a href="activities.php" class="nav-item"><span>📋</span> Activities</a>
        <a href="health.php" class="nav-item"><span>🏥</span> Health Records</a>
        <a href="profile.php" class="nav-item"><span>👤</span> My Profile</a>
    </nav>
    <button class="logout-btn" onclick="window.location.href='logout.php'">🚪 Logout</button>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1>🐶 My Pets</h1>
    </div>

    <?php if ($success): ?><div class="success">✅ <?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error-msg">😿 <?php echo $error; ?></div><?php endif; ?>

    <!-- ADD PET FORM -->
    <div class="panel">
        <h3>➕ Add New Pet</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Pet Name</label>
                    <input type="text" name="pet_name" placeholder="e.g. Fluffy" required>
                </div>
                <div class="form-group">
                    <label>Species</label>
                    <select name="species">
                        <option value="Dog">🐶 Dog</option>
                        <option value="Cat">🐱 Cat</option>
                        <option value="Rabbit">🐰 Rabbit</option>
                        <option value="Hamster">🐹 Hamster</option>
                        <option value="Bird">🐦 Bird</option>
                        <option value="Fish">🐠 Fish</option>
                        <option value="Other">🐾 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" name="breed" placeholder="e.g. Shih Tzu">
                </div>
                <div class="form-group">
                    <label>Age (years)</label>
                    <input type="number" name="age" placeholder="e.g. 2" min="0">
                </div>
            </div>
            <button type="submit" name="add_pet" class="btn-save">🐾 Add Pet</button>
        </form>
    </div>

    <!-- PETS LIST -->
    <div class="panel">
        <h3>🐾 My Pet List</h3>
        <input class="search-bar" type="text" id="searchInput"
            placeholder="🔍 Search pet..." onkeyup="searchPets()">
        <div class="pets-grid" id="petsGrid">
            <?php if (mysqli_num_rows($pets) > 0):
                while ($pet = mysqli_fetch_assoc($pets)):
                    $icons = ['Dog'=>'🐶','Cat'=>'🐱','Rabbit'=>'🐰','Hamster'=>'🐹','Bird'=>'🐦','Fish'=>'🐠','Other'=>'🐾'];
                    $icon = $icons[$pet['species']] ?? '🐾';
            ?>
            <div class="pet-card" data-name="<?php echo strtolower($pet['pet_name']); ?>">
                <div class="pet-icon"><?php echo $icon; ?></div>
                <h4><?php echo htmlspecialchars($pet['pet_name']); ?></h4>
                <p class="species"><?php echo $pet['species']; ?></p>
                <p class="breed"><?php echo $pet['breed'] ?: 'Unknown breed'; ?></p>
                <span class="pet-badge">
                    Age: <?php echo $pet['age'] ?: '?'; ?> yrs |
                    <?php echo $pet['total_activities']; ?> activities
                </span>
                <div class="pet-actions">
                    <button class="btn-edit" onclick="openEdit(
                        '<?php echo $pet['pet_id']; ?>',
                        '<?php echo addslashes($pet['pet_name']); ?>',
                        '<?php echo $pet['species']; ?>',
                        '<?php echo addslashes($pet['breed']); ?>',
                        '<?php echo $pet['age']; ?>'
                    )">✏️ Edit</button>
                    <button class="btn-delete" onclick="confirmDelete(<?php echo $pet['pet_id']; ?>)">🗑️ Delete</button>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="no-pets" style="grid-column:1/-1">
                🐾 No pets yet! Add your first pet above.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>✏️ Edit Pet</h3>
        <form method="POST">
            <input type="hidden" name="pet_id" id="edit_pet_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Pet Name</label>
                    <input type="text" name="pet_name" id="edit_pet_name" required>
                </div>
                <div class="form-group">
                    <label>Species</label>
                    <select name="species" id="edit_species">
                        <option value="Dog">🐶 Dog</option>
                        <option value="Cat">🐱 Cat</option>
                        <option value="Rabbit">🐰 Rabbit</option>
                        <option value="Hamster">🐹 Hamster</option>
                        <option value="Bird">🐦 Bird</option>
                        <option value="Fish">🐠 Fish</option>
                        <option value="Other">🐾 Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" name="breed" id="edit_breed">
                </div>
                <div class="form-group">
                    <label>Age (years)</label>
                    <input type="number" name="age" id="edit_age" min="0">
                </div>
            </div>
            <div class="modal-btns">
                <button type="submit" name="edit_pet" class="btn-save">💾 Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, species, breed, age) {
    document.getElementById('edit_pet_id').value = id;
    document.getElementById('edit_pet_name').value = name;
    document.getElementById('edit_species').value = species;
    document.getElementById('edit_breed').value = breed;
    document.getElementById('edit_age').value = age;
    document.getElementById('editModal').classList.add('show');
}
function closeEdit() {
    document.getElementById('editModal').classList.remove('show');
}
function confirmDelete(id) {
    if (confirm('Are you sure you want to remove this pet? 🐾')) {
        window.location.href = 'pets.php?delete=' + id;
    }
}
function searchPets() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.pet-card').forEach(card => {
        card.style.display = card.dataset.name.includes(input) ? '' : 'none';
    });
}
</script>
</body>
</html>