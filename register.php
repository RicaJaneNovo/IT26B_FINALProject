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
            $success = "Yay! Account created! You can now login. 🎉";
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff8f0;
            overflow: hidden;
            position: relative;
        }

        .bg-animals {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .animal {
            position: absolute;
            font-size: 40px;
            animation: float 6s ease-in-out infinite;
            opacity: 0.15;
        }

        .animal:nth-child(1)  { top: 5%;  left: 5%;  font-size: 50px; animation-delay: 0s; }
        .animal:nth-child(2)  { top: 15%; left: 80%; font-size: 35px; animation-delay: 1s; }
        .animal:nth-child(3)  { top: 30%; left: 20%; font-size: 45px; animation-delay: 2s; }
        .animal:nth-child(4)  { top: 50%; left: 70%; font-size: 40px; animation-delay: 0.5s; }
        .animal:nth-child(5)  { top: 65%; left: 10%; font-size: 55px; animation-delay: 1.5s; }
        .animal:nth-child(6)  { top: 75%; left: 85%; font-size: 38px; animation-delay: 2.5s; }
        .animal:nth-child(7)  { top: 85%; left: 40%; font-size: 42px; animation-delay: 3s; }
        .animal:nth-child(8)  { top: 40%; left: 50%; font-size: 48px; animation-delay: 1s; }
        .animal:nth-child(9)  { top: 10%; left: 50%; font-size: 36px; animation-delay: 0.8s; }
        .animal:nth-child(10) { top: 90%; left: 60%; font-size: 44px; animation-delay: 2s; }
        .animal:nth-child(11) { top: 20%; left: 35%; font-size: 52px; animation-delay: 3.5s; }
        .animal:nth-child(12) { top: 60%; left: 30%; font-size: 38px; animation-delay: 1.2s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .paw-pattern {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
            background-image:
                radial-gradient(circle, #ffb6c1 2px, transparent 2px);
            background-size: 50px 50px;
            opacity: 0.1;
        }

        .container {
            position: relative;
            z-index: 1;
            background: white;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(255, 100, 150, 0.2);
            width: 100%;
            max-width: 420px;
            text-align: center;
            border: 3px solid #ffd6e7;
        }

        .top-animals {
            font-size: 35px;
            margin-bottom: 5px;
            letter-spacing: 5px;
        }

        .logo {
            font-size: 55px;
            margin-bottom: 5px;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        h1 {
            color: #d63384;
            font-size: 30px;
            margin-bottom: 3px;
            text-shadow: 1px 1px 0px #ffb6c1;
        }

        p.tagline {
            color: #f48fb1;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .divider {
            font-size: 18px;
            margin-bottom: 20px;
            letter-spacing: 3px;
        }

        .error {
            background: #ffe0e0;
            color: #c0392b;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .success {
            background: #e0ffe0;
            color: #27ae60;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 2px solid #ffd6e7;
            border-radius: 15px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
            background: #fff8f0;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #d63384;
            background: white;
        }

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
            accent-color: #d63384;
        }

        .show-password label {
            font-size: 13px;
            color: #f48fb1;
            cursor: pointer;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #ff6b9d, #d63384);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(214, 51, 132, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(214, 51, 132, 0.4);
        }

        .btn-back {
            width: 100%;
            padding: 13px;
            background: white;
            color: #d63384;
            border: 2px solid #d63384;
            border-radius: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #ff6b9d, #d63384);
            color: white;
            transform: translateY(-2px);
        }

        .bottom-animals {
            font-size: 25px;
            margin-top: 20px;
            letter-spacing: 3px;
        }
    </style>
</head>
<body>

<div class="bg-animals">
    <div class="animal">🐶</div>
    <div class="animal">🐱</div>
    <div class="animal">🐾</div>
    <div class="animal">🐰</div>
    <div class="animal">🐹</div>
    <div class="animal">🐶</div>
    <div class="animal">🐱</div>
    <div class="animal">🐾</div>
    <div class="animal">🦴</div>
    <div class="animal">🐰</div>
    <div class="animal">🐹</div>
    <div class="animal">🐾</div>
</div>

<div class="paw-pattern"></div>

<div class="container">
    <div class="top-animals">🐱🐾🐶</div>
    <div class="logo">🐾</div>
    <h1>PawDiary</h1>
    <p class="tagline">🌸 Join our pet-loving family! 🌸</p>
    <div class="divider">🐾 · 🦴 · 🐾</div>

    <?php if ($error): ?>
        <div class="error">😿 <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">🎉 <?php echo $success; ?></div>
        <button class="btn-back" onclick="window.location.href='index.php'">
            🐾 Go to Login
        </button>
    <?php else: ?>
        <form method="POST">
            <input type="text" name="username" placeholder="🐾 Username" required />
            <input type="email" name="email" placeholder="📧 Email" required />
            <input type="password" name="password" id="registerPassword" placeholder="🔒 Password" required />

            <div class="show-password">
                <input type="checkbox" id="showRegisterPassword" onclick="toggleRegisterPassword()">
                <label for="showRegisterPassword">👁️ Show Password</label>
            </div>

            <button type="submit" class="btn-register">🐱 Create Account</button>
        </form>

        <br>
        <button class="btn-back" onclick="window.location.href='index.php'">
            ← Back to Login
        </button>
    <?php endif; ?>

    <div class="bottom-animals">🐶 🐾 🐱</div>
</div>

<script>
    function toggleRegisterPassword() {
        var pwd = document.getElementById("registerPassword");
        pwd.type = pwd.type === "password" ? "text" : "password";
    }
</script>

</body>
</html>