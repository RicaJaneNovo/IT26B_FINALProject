<?php
include 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already exists!";
    } else {
        $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            $success = "Account created! You can now login.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawDiary - Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f0ff, #fce4ec);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo { font-size: 50px; margin-bottom: 10px; }

        h1 { color: #7b2d8b; font-size: 28px; margin-bottom: 5px; }

        p.tagline { color: #aaa; font-size: 14px; margin-bottom: 25px; }

        .error {
            background: #ffe0e0;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .success {
            background: #e0ffe0;
            color: #27ae60;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus { border-color: #7b2d8b; }

        .show-password {
            display: flex;
            align-items: center;
            gap: 6px;
            text-align: left;
            margin-top: -10px;
            margin-bottom: 15px;
        }

        .show-password input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            margin: 0;
        }

        .show-password label {
            font-size: 13px;
            color: #888;
            cursor: pointer;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #7b2d8b, #e91e8c);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 10px;
            transition: opacity 0.3s;
        }

        .btn-register:hover { opacity: 0.9; }

        .btn-back {
            width: 100%;
            padding: 12px;
            background: white;
            color: #7b2d8b;
            border: 2px solid #7b2d8b;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #7b2d8b;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">🐾</div>
    <h1>PawDiary</h1>
    <p class="tagline">Create your account</p>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
        <button class="btn-back" onclick="window.location.href='index.php'">
            ← Go to Login
        </button>
    <?php else: ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" id="registerPassword" placeholder="Password" required />

            <div class="show-password">
                <input type="checkbox" id="showRegisterPassword" onclick="toggleRegisterPassword()">
                <label for="showRegisterPassword">Show Password</label>
            </div>

            <button type="submit" class="btn-register">Register</button>
        </form>

        <br>
        <button class="btn-back" onclick="window.location.href='index.php'">
            ← Back to Login
        </button>
    <?php endif; ?>
</div>

<script>
    function toggleRegisterPassword() {
        var pwd = document.getElementById("registerPassword");
        pwd.type = pwd.type === "password" ? "text" : "password";
    }
</script>

</body>
</html>