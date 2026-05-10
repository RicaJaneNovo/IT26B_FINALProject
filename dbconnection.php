<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$host     = "localhost";
$user     = "root";
$password = "";
$database = "pawdiary";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . mysqli_connect_error()
    ]);
    exit();
}
?>