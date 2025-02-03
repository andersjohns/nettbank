<?php
session_start();
require_once 'db.php';  // Inkluderer DB-tilkoblingen

// Sjekk om bruker er logget inn
if (!isset($_SESSION['bruker_id'])) {
    header("Location: login.php");  // Hvis ikke, send til login
    exit();
}

// Hent brukerens kontoer fra databasen
$bruker_id = $_SESSION['bruker_id'];
$query = "SELECT * FROM kontoer WHERE bruker_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bruker_id);
$stmt->execute();
$result = $stmt->get_result();

// Vis kontoer
echo "<h1>Kontooversikt</h1>";
while ($konto = $result->fetch_assoc()) {
    echo "<p>Konto: " . htmlspecialchars($konto['kontonummer']) . " - Saldo: " . htmlspecialchars($konto['saldo']) . " NOK</p>";
}
?>

<a href="dashboard.php">Tilbake til dashboard</a>
