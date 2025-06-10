<?php
$host = "localhost";
$user = "root"; // Change if you have a different DB user
$pass = ""; // Change if you set a password
$dbname = "voting_system_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
