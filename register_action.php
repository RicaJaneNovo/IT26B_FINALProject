<?php
include 'dbconnection.php';

$data     = json_decode(file_get_contents("php://input"), true);
$username = $data['username'];
$email    = $data['email'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// Check if username exists
$check = mysqli_query($conn,
    "SELECT * FROM users WHERE username = '$username'");

if (mysqli_num_rows($check) > 0) {
    echo json_encode([
        "status"  => "error",
        "message" => "Username already exists!"
    ]);
    exit();
}

// Insert new user
$query = "INSERT INTO users (username, email, password)
          VALUES ('$username', '$email', '$password')";

if (mysqli_query($conn, $query)) {
    echo json_encode([
        "status"  => "success",
        "message" => "Account created successfully!"
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Something went wrong!"
    ]);
}
?>