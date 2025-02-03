<?php
session_start();
require_once 'db.php';  // Inkluderer DB-tilkoblingen

// Sjekk om bruker er logget inn og er admin
if (!isset($_SESSION['bruker_id']) || $_SESSION['rolle'] != 'admin') {
    header("Location: login.php");  // Hvis ikke, send til login
    exit();
}

// Hent alle brukere
$query = "SELECT * FROM brukere";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Hent alle kontoer
$query_kontoer = "SELECT * FROM kontoer";
$stmt_kontoer = $conn->prepare($query_kontoer);
$stmt_kontoer->execute();
$result_kontoer = $stmt_kontoer->get_result();

echo "<h1>Admin Dashboard</h1>";

echo "<h2>Brukere</h2>";
while ($bruker = $result->fetch_assoc()) {
    echo "<p>Navn: " . htmlspecialchars($bruker['navn']) . " - E-post: " . htmlspecialchars($bruker['epost']) . "</p>";
}

echo "<h2>Kontoer</h2>";
while ($konto = $result_kontoer->fetch_assoc()) {
    echo "<p>Konto: " . htmlspecialchars($konto['kontonummer']) . " - Saldo: " . htmlspecialchars($konto['saldo']) . "</p>";
}
?>

<a href="dashboard.php">Tilbake til dashboard</a>
