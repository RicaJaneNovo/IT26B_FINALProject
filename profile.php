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

// --- Handle Profile Update ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $bio = $_POST['bio'];
    $profile_pic = $_POST['current_pic'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $new_name = "profile_" . $user_id . "_" . time() . "." . $ext;
        $upload_path = "uploads/" . $new_name;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                $profile_pic = $new_name;
            }
        } else {
            $error = "Only JPG, PNG, GIF allowed!";
        }
    }

    if (!$error) {
        $query = "UPDATE users SET full_name='$full_name', bio='$bio', profile_pic='$profile_pic' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $query)) {
            $success = "Profile updated successfully!";
        }
    }
}

// --- Handle Pet Post ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_post'])) {
    $pet_id = $_POST['pet_id'];
    $caption = $_POST['caption'];
    $post_image = "";

    if (!empty($_FILES['post_image']['name'])) {
        $ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $new_name = "post_" . $user_id . "_" . time() . "." . $ext;
        $upload_path = "uploads/" . $new_name;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $upload_path)) {
                $post_image = $new_name;
            }
        } else {
            $error = "Only JPG, PNG, GIF allowed!";
        }
    }

    if (!$error) {
        $query = "INSERT INTO pet_posts (user_id, pet_id, caption, post_image) VALUES ('$user_id', '$pet_id', '$caption', '$post_image')";
        if (mysqli_query($conn, $query)) {
            $success = "Pet post added!";
        }
    }
}

// --- Fetch User Data ---
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE user_id = '$user_id'"));

// --- Fetch User's Pets for dropdown ---
$pets = mysqli_query($conn,
    "SELECT * FROM pets WHERE user_id = '$user_id'");

// --- Fetch Pet Posts (with pet name using LEFT JOIN) ---
$posts = mysqli_query($conn,
    "SELECT pp.*, p.pet_name
     FROM pet_posts pp
     LEFT JOIN pets p ON pp.pet_id = p.pet_id
     WHERE pp.user_id = '$user_id'
     ORDER BY pp.posted_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawDiary - My Profile</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f4ff;
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 220px;
            height: 100%;
            background: white;
            border-right: 2px solid #ffd6e7;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 0;
            z-index: 100;
        }

        .sidebar .logo {
            font-size: 40px;
            margin-bottom: 5px;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .sidebar h2 { color: #d63384; font-size: 20px; margin-bottom: 5px; }
        .sidebar p { color: #f48fb1; font-size: 12px; margin-bottom: 30px; }

        .nav-menu { width: 100%; padding: 0 15px; }

        .nav-item {
            display: block;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 12px;
            text-decoration: none;
            color: #888;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-item:hover, .nav-item.active {
            background: #ffd6e7;
            color: #d63384;
        }

        .nav-item span { margin-right: 8px; font-size: 16px; }

        .logout-btn {
            margin-top: auto;
            margin-bottom: 20px;
            padding: 10px 25px;
            background: white;
            color: #d63384;
            border: 2px solid #d63384;
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .logout-btn:hover { background: #d63384; color: white; }

        /* ── MAIN ── */
        .main { margin-left: 220px; padding: 30px; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 { color: #d63384; font-size: 24px; }

        /* ── MESSAGES ── */
        .success {
            background: #e0ffe0;
            color: #27ae60;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-msg {
            background: #ffe0e0;
            color: #c0392b;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* ── PANELS ── */
        .panel {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 2px solid #ffd6e7;
            box-shadow: 0 4px 15px rgba(214,51,132,0.08);
            margin-bottom: 25px;
        }

        .panel h3 {
            color: #d63384;
            font-size: 16px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #ffd6e7;
        }

        /* ── PROFILE SECTION ── */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 25px;
        }

        .profile-pic-wrapper {
            position: relative;
            width: 110px;
            height: 110px;
        }

        .profile-pic-wrapper img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffd6e7;
        }

        .profile-pic-wrapper .change-pic {
            position: absolute;
            bottom: 0; right: 0;
            background: #d63384;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-info h2 { color: #333; font-size: 20px; }
        .profile-info p { color: #aaa; font-size: 13px; margin-top: 3px; }
        .profile-info .bio { color: #777; font-size: 14px; margin-top: 8px; font-style: italic; }

        /* ── FORM ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }

        label { font-size: 13px; color: #888; font-weight: 600; }

        input[type="text"],
        input[type="file"],
        textarea,
        select {
            padding: 10px 12px;
            border: 2px solid #ffd6e7;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            font-family: 'Segoe UI', sans-serif;
            transition: border 0.3s;
            background: #fff8fc;
        }

        input:focus, textarea:focus, select:focus { border-color: #d63384; }

        textarea { resize: vertical; min-height: 80px; }

        .btn-save {
            padding: 11px 25px;
            background: linear-gradient(135deg, #ff6b9d, #d63384);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(214,51,132,0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(214,51,132,0.4);
        }

        /* ── PET POSTS GRID ── */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .post-card {
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid #ffd6e7;
            background: white;
            transition: transform 0.3s;
        }

        .post-card:hover { transform: translateY(-4px); }

        .post-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .post-card .post-info {
            padding: 12px;
        }

        .post-card .pet-tag {
            display: inline-block;
            background: #ffd6e7;
            color: #d63384;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 6px;
        }

        .post-card .caption {
            font-size: 13px;
            color: #555;
            margin-bottom: 5px;
        }

        .post-card .date {
            font-size: 11px;
            color: #bbb;
        }

        .no-posts {
            text-align: center;
            color: #ccc;
            padding: 40px;
            font-size: 14px;
            grid-column: 1 / -1;
        }

        /* ── FILE PREVIEW ── */
        #previewImg {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffd6e7;
            display: none;
            margin-top: 10px;
        }

        #postPreview {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            display: none;
            margin-top: 10px;
            border: 2px solid #ffd6e7;
        }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="logo">🐾</div>
    <h2>PawDiary</h2>
    <p>Pet Activity Journal</p>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item"><span>🏠</span> Dashboard</a>
        <a href="pets.php" class="nav-item"><span>🐶</span> My Pets</a>
        <a href="activities.php" class="nav-item"><span>📋</span> Activities</a>
        <a href="health.php" class="nav-item"><span>🏥</span> Health Records</a>
        <a href="profile.php" class="nav-item active"><span>👤</span> My Profile</a>
    </nav>
    <button class="logout-btn" onclick="window.location.href='logout.php'">
        🚪 Logout
    </button>
</div>

<!-- ── MAIN ── -->
<div class="main">
    <div class="topbar">
        <h1>👤 My Profile</h1>
    </div>

    <?php if ($success): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-msg">😿 <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- ── PROFILE DISPLAY ── -->
    <div class="panel">
        <div class="profile-header">
            <div class="profile-pic-wrapper">
                <img id="currentPic"
                    src="<?php echo !empty($user['profile_pic']) && file_exists('uploads/'.$user['profile_pic'])
                        ? 'uploads/'.$user['profile_pic'] : 'https://placehold.co/110x110/ffd6e7/d63384?text=🐾'; ?>"
                    alt="Profile">
            </div>
            <div class="profile-info">
                <h2><?php echo !empty($user['full_name']) ? htmlspecialchars($user['full_name']) : htmlspecialchars($username); ?></h2>
                <p>@<?php echo htmlspecialchars($username); ?></p>
                <p class="bio"><?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'No bio yet. Tell us about yourself! 🐾'; ?></p>
            </div>
        </div>

        <!-- ── EDIT PROFILE FORM ── -->
        <h3>✏️ Edit Profile</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="current_pic" value="<?php echo $user['profile_pic'] ?? ''; ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Your full name"
                        value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*"
                        onchange="previewProfile(event)">
                    <img id="previewImg" src="" alt="Preview">
                </div>
                <div class="form-group full">
                    <label>Bio / About Me</label>
                    <textarea name="bio" placeholder="Tell us about yourself and your pets! 🐾"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
            </div>
            <button type="submit" name="update_profile" class="btn-save">
                💾 Save Profile
            </button>
        </form>
    </div>

    <!-- ── ADD PET POST ── -->
    <div class="panel">
        <h3>📸 Post Your Pet's Photo</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Pet</label>
                    <select name="pet_id" required>
                        <option value="">-- Choose a pet --</option>
                        <?php
                        mysqli_data_seek($pets, 0);
                        while ($pet = mysqli_fetch_assoc($pets)):
                        ?>
                        <option value="<?php echo $pet['pet_id']; ?>">
                            <?php echo htmlspecialchars($pet['pet_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pet Photo</label>
                    <input type="file" name="post_image" accept="image/*"
                        onchange="previewPost(event)" required>
                    <img id="postPreview" src="" alt="Preview">
                </div>
                <div class="form-group full">
                    <label>Caption</label>
                    <textarea name="caption" placeholder="Write something cute about your pet! 🐾"></textarea>
                </div>
            </div>
            <button type="submit" name="add_post" class="btn-save">
                🐾 Post Photo
            </button>
        </form>
    </div>

    <!-- ── PET POSTS GALLERY ── -->
    <div class="panel">
        <h3>🖼️ My Pet Gallery</h3>
        <div class="posts-grid">
            <?php if (mysqli_num_rows($posts) > 0): ?>
                <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                <div class="post-card">
                    <?php if (!empty($post['post_image']) && file_exists('uploads/'.$post['post_image'])): ?>
                        <img src="uploads/<?php echo $post['post_image']; ?>" alt="Pet Photo">
                    <?php else: ?>
                        <div style="height:180px; background:#ffd6e7; display:flex; align-items:center; justify-content:center; font-size:50px;">🐾</div>
                    <?php endif; ?>
                    <div class="post-info">
                        <span class="pet-tag">🐶 <?php echo htmlspecialchars($post['pet_name'] ?? 'Unknown'); ?></span>
                        <p class="caption"><?php echo htmlspecialchars($post['caption']); ?></p>
                        <p class="date">📅 <?php echo date('M d, Y', strtotime($post['posted_at'])); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-posts">
                    🐾 No pet photos yet! Post your first cute pet photo above! 📸
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function previewProfile(event) {
    const img = document.getElementById('previewImg');
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = 'block';
    document.getElementById('currentPic').src = img.src;
}

function previewPost(event) {
    const img = document.getElementById('postPreview');
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = 'block';
}
</script>

</body>
</html>