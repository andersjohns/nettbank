<?php
$host = "localhost";
$dbname = "nettbank";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

// Sjekk om tilkoblingen fungerer
if ($conn->connect_error) {
    die("Tilkobling feilet: " . $conn->connect_error);
}
?>
