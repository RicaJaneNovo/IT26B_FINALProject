<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, password)
              VALUES ('$username', '$password')";

    if (mysqli_query($conn, $query)) {
        echo "Registered Successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>