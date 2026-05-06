<?php
session_start();
include 'db.php';

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
$chart_colors = [];
$chart_borders = [];

while ($row = mysqli_fetch_assoc($chart_data)) {
    $chart_labels[] = $row['pet_name'];
    $chart_values[] = $row['total'];

    if ($row['total'] == 0) {
        $chart_colors[]  = 'rgba(231, 76, 60, 0.7)';
        $chart_borders[] = 'rgba(231, 76, 60, 1)';
    } elseif ($row['total'] <= 3) {
        $chart_colors[]  = 'rgba(241, 196, 15, 0.7)';
        $chart_borders[] = 'rgba(241, 196, 15, 1)';
    } else {
        $chart_colors[]  = 'rgba(39, 174, 96, 0.7)';
        $chart_borders[] = 'rgba(39, 174, 96, 1)';
    }
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
            width: 220px; height: 100%;
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
        .sidebar p  { color: #f48fb1; font-size: 12px; margin-bottom: 30px; }

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

        .topbar .welcome { font-size: 14px; color: #888; }
        .topbar .welcome span { color: #d63384; font-weight: bold; }

        /* ── CARDS ── */
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
        .card .count { font-size: 36px; font-weight: bold; color: #d63384; }
        .card .label { font-size: 13px; color: #aaa; margin-top: 5px; }

        /* ── GRID ── */
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

        /* ── LEGEND ── */
        .legend {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #555;
        }

        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 4px;
        }

        /* ── TABLE ── */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }

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

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            background: #ffd6e7;
            color: #d63384;
        }

        .no-data {
            text-align: center;
            color: #ccc;
            padding: 30px;
            font-size: 13px;
        }

        /* ── SEARCH ── */
        .search-bar {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #ffd6e7;
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            margin-bottom: 15px;
            background: #fff8fc;
            transition: border 0.3s;
        }

        .search-bar:focus { border-color: #d63384; }

        /* ── STATUS CARDS ── */
        .status-section {
            margin-top: 25px;
        }

        .status-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .status-card {
            border-radius: 14px;
            padding: 15px;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            border: 2px solid;
        }

        .status-card.red {
            background: #fff0f0;
            border-color: #e74c3c;
            color: #e74c3c;
        }

        .status-card.yellow {
            background: #fffbe0;
            border-color: #f1c40f;
            color: #b7950b;
        }

        .status-card.green {
            background: #eafaf1;
            border-color: #27ae60;
            color: #27ae60;
        }

        .status-card .s-icon { font-size: 28px; margin-bottom: 6px; }
        .status-card .s-name { font-weight: bold; font-size: 14px; margin-bottom: 3px; }
        .status-card .s-count { font-size: 12px; opacity: 0.8; }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="logo">📖</div>
    
    <h2>PawDiary</h2>
    <p>Pet Activity Journal</p>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item active"><span>🏠</span> Dashboard</a>
        <a href="pets.php" class="nav-item"><span>🐶</span> My Pets</a>
        <a href="activities.php" class="nav-item"><span>📋</span> Activities</a>
        <a href="health.php" class="nav-item"><span>🏥</span> Health Records</a>
        <a href="profile.php" class="nav-item"><span>👤</span> My Profile</a>
    </nav>
    <button class="logout-btn" onclick="window.location.href='logout.php'">
        Logout
    </button>
</div>

<!-- ── MAIN ── -->
<div class="main">

    <!-- Topbar -->
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

        <!-- Chart Panel -->
        <div class="panel">
            <h3>📊 Activities Per Pet</h3>

            <?php if (count($chart_labels) > 0): ?>
                <canvas id="activityChart" height="200"></canvas>

                <!-- Legend -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:rgba(231,76,60,0.7);"></div>
                        🔴 No activities — needs care
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:rgba(241,196,15,0.7);"></div>
                        🟡 1–3 activities — low
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:rgba(39,174,96,0.7);"></div>
                        🟢 4+ activities — healthy!
                    </div>
                </div>

                <!-- Pet Status Cards -->
                <div class="status-section">
                    <h3 style="margin-top:20px;">🐾 Pet Health Status</h3>
                    <div class="status-cards">
                        <?php
                        $icons = ['Dog'=>'🐶','Cat'=>'🐱','Rabbit'=>'🐰',
                                  'Hamster'=>'🐹','Bird'=>'🐦','Fish'=>'🐠','Other'=>'🐾'];

                        // Re-fetch for status cards
                        $status_data = mysqli_query($conn,
                            "SELECT p.pet_name, p.species, COUNT(a.activity_id) as total
                             FROM pets p
                             LEFT JOIN activities a ON p.pet_id = a.pet_id
                             WHERE p.user_id = '$user_id'
                             GROUP BY p.pet_id");

                        while ($s = mysqli_fetch_assoc($status_data)):
                            $icon = $icons[$s['species']] ?? '🐾';
                            $total = $s['total'];

                            if ($total == 0) {
                                $cls = 'red';
                                $status = '⚠️ Needs attention!';
                            } elseif ($total <= 3) {
                                $cls = 'yellow';
                                $status = '🟡 Low activity';
                            } else {
                                $cls = 'green';
                                $status = '💚 Healthy & Active!';
                            }
                        ?>
                        <div class="status-card <?php echo $cls; ?>">
                            <div class="s-icon"><?php echo $icon; ?></div>
                            <div class="s-name"><?php echo htmlspecialchars($s['pet_name']); ?></div>
                            <div class="s-count"><?php echo $total; ?> activities</div>
                            <div style="margin-top:5px; font-size:11px;"><?php echo $status; ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="no-data">🐾 No data yet. Add pets and activities!</div>
            <?php endif; ?>
        </div>

        <!-- Recent Activities Panel -->
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

<!-- Chart.js -->
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
            backgroundColor: <?php echo json_encode($chart_colors); ?>,
            borderColor: <?php echo json_encode($chart_borders); ?>,
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
                        if (val == 0) return '⚠️ No activities — needs attention!';
                        if (val <= 3) return '🟡 Low activity — keep going!';
                        return '🟢 Great job! Very active & healthy!';
                    }
                }
            }
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