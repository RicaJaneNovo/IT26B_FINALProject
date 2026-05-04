<?php
session_start();
include 'dp.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user ['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    header("Location: dashboard.php");
    exit();

  } else {
    $error = "Invalid username or password!";
  }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawDiary - LogIn</title>
    <style>
        *{ margin: 0; padding: 0; box-sizing: border-box; }
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

        input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
             border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
        }

        input:focus { border-color: #7b2d8b; }

        .btn-login {
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
        .btn-login:hover { opacity: 0.9; }

        .btn-register {
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

        .btn-register:hover {
            background: #7b2d8b;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="logo">🐾</div>
    <h1>PawDiary</h1>
    <p class="tagline">Your Pet's Daily Activity Journal</p>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn-login">Login</button>
    </form>

    <br>
    <button class="btn-register" onclick="window.location.href='register.php'">
        Register
    </button>
</div>

</body>
</html>