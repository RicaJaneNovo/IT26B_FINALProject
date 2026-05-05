<?php
session_start();
include 'db.php';

// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// --- Summary Counts ---
$total_pets = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM pets WHERE user_id = '$user_id'"))['total'];

$total_activities = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM activities a
     INNER JOIN pets p ON a.pet_id = p.pet_id
     WHERE p.user_id = '$user_id'"))['total'];

$total_health = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM health_records h
     INNER JOIN pets p ON h.pet_id = p.pet_id
     WHERE p.user_id = '$user_id'"))['total'];

// --- Recent Activities (INNER JOIN) ---
$recent_activities = mysqli_query($conn,
    "SELECT a.activity_type, a.description, a.activity_date, p.pet_name
     FROM activities a
     INNER JOIN pets p ON a.pet_id = p.pet_id
     WHERE p.user_id = '$user_id'
     ORDER BY a.activity_date DESC
     LIMIT 5");

// --- Chart Data: Activities per Pet (LEFT JOIN) ---
$chart_data = mysqli_query($conn,
    "SELECT p.pet_name, COUNT(a.activity_id) as total
     FROM pets p
     LEFT JOIN activities a ON p.pet_id = a.pet_id
     WHERE p.user_id = '$user_id'
     GROUP BY p.pet_id");

$chart_labels = [];
$chart_values = [];
while ($row = mysqli_fetch_assoc($chart_data)) {
    $chart_labels[] = $row['pet_name'];
    $chart_values[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawDiary - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .sidebar h2 {
            color: #d63384;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .sidebar p {
            color: #f48fb1;
            font-size: 12px;
            margin-bottom: 30px;
        }

        .nav-menu {
            width: 100%;
            padding: 0 15px;
        }

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

        .nav-item span {
            margin-right: 8px;
            font-size: 16px;
        }

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

        .logout-btn:hover {
            background: #d63384;
            color: white;
        }

        /* ── MAIN CONTENT ── */
        .main {
            margin-left: 220px;
            padding: 30px;
        }

        /* ── TOP BAR ── */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 {
            color: #d63384;
            font-size: 24px;
        }

        .topbar .welcome {
            font-size: 14px;
            color: #888;
        }

        .topbar .welcome span {
            color: #d63384;
            font-weight: bold;
        }

        /* ── SUMMARY CARDS ── */
        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            border: 2px solid #ffd6e7;
            box-shadow: 0 4px 15px rgba(214,51,132,0.08);
            transition: transform 0.3s;
        }

        .card:hover { transform: translateY(-4px); }

        .card .icon { font-size: 40px; margin-bottom: 10px; }

        .card .count {
            font-size: 36px;
            font-weight: bold;
            color: #d63384;
        }

        .card .label {
            font-size: 13px;
            color: #aaa;
            margin-top: 5px;
        }

        /* ── GRID LAYOUT ── */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* ── PANEL ── */
        .panel {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 2px solid #ffd6e7;
            box-shadow: 0 4px 15px rgba(214,51,132,0.08);
        }

        .panel h3 {
            color: #d63384;
            font-size: 16px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #ffd6e7;
        }

        /* ── TABLE ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background: #ffd6e7;
            color: #d63384;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f9f4ff;
            color: #555;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fff8fc; }

        .no-data {
            text-align: center;
            color: #ccc;
            padding: 20px;
            font-size: 13px;
        }

        /* ── ACTIVITY BADGE ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            background: #ffd6e7;
            color: #d63384;
        }

        /* ── SEARCH BAR ── */
        .search-bar {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #ffd6e7;
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            margin-bottom: 15px;
            transition: border 0.3s;
        }

        .search-bar:focus { border-color: #d63384; }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="logo">📖</div>
    <h2>PawDiary</h2>
    <p>Pet Activity Journal</p>

    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item active">
            <a href="profile.php" class="nav-item">
            <span>👤</span> My Profile
        </a>
            <span>🏠</span> Dashboard
        </a>
        <a href="pets.php" class="nav-item">
            <span>🐶</span> My Pets
        </a>
        <a href="activities.php" class="nav-item">
            <span>📋</span> Activities
        </a>
        <a href="health.php" class="nav-item">
            <span>🏥</span> Health Records
        </a>
    </nav>

    <button class="logout-btn" onclick="window.location.href='logout.php'">
        🚪 Logout
    </button>
</div>

<!-- ── MAIN CONTENT ── -->
<div class="main">

    <!-- Top Bar -->
    <div class="topbar">
        <h1>🐾 Dashboard</h1>
        <div class="welcome">
            Welcome back, <span><?php echo htmlspecialchars($username); ?>!</span> 🌸
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="cards">
        <div class="card">
            <div class="icon">🐶</div>
            <div class="count"><?php echo $total_pets; ?></div>
            <div class="label">Total Pets</div>
        </div>
        <div class="card">
            <div class="icon">📋</div>
            <div class="count"><?php echo $total_activities; ?></div>
            <div class="label">Total Activities</div>
        </div>
        <div class="card">
            <div class="icon">🏥</div>
            <div class="count"><?php echo $total_health; ?></div>
            <div class="label">Health Records</div>
        </div>
    </div>

    <!-- Chart + Recent Activities -->
    <div class="grid-2">

        <!-- Chart -->
        <div class="panel">
            <h3>📊 Activities Per Pet</h3>
            <?php if (count($chart_labels) > 0): ?>
                <canvas id="activityChart" height="200"></canvas>
            <?php else: ?>
                <div class="no-data">🐾 No data yet. Add pets and activities!</div>
            <?php endif; ?>
        </div>

        <!-- Recent Activities Table -->
        <div class="panel">
            <h3>📋 Recent Activities</h3>
            <input class="search-bar" type="text" id="searchInput"
                placeholder="🔍 Search activity..." onkeyup="searchTable()">

            <?php if (mysqli_num_rows($recent_activities) > 0): ?>
                <table id="activityTable">
                    <thead>
                        <tr>
                            <th>Pet</th>
                            <th>Activity</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent_activities)): ?>
                        <tr>
                            <td>🐾 <?php echo htmlspecialchars($row['pet_name']); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars($row['activity_type']); ?></span></td>
                            <td><?php echo $row['activity_date']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">🐾 No activities yet. Start logging!</div>
            <?php endif; ?>
        </div>

    </div>
</div>


<script>
<?php if (count($chart_labels) > 0): ?>
const ctx = document.getElementById('activityChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Activities',
            data: <?php echo json_encode($chart_values); ?>,
            backgroundColor: [
                '#ffd6e7', '#f48fb1', '#d63384',
                '#7b2d8b', '#ffb6c1', '#ff6b9d'
            ],
            borderColor: '#d63384',
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
<?php endif; ?>


function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#activityTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>

</body>
</html>