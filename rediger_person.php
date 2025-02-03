<?php
session_start();
require_once 'db.php';  // Inkluderer DB-tilkoblingen

// Sjekk om bruker er logget inn
if (!isset($_SESSION['bruker_id'])) {
    header("Location: login.php");  // Hvis ikke, send til login
    exit();
}

// Hent brukerens informasjon fra databasen
$bruker_id = $_SESSION['bruker_id'];
$query = "SELECT * FROM brukere WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bruker_id);
$stmt->execute();
$result = $stmt->get_result();
$bruker = $result->fetch_assoc();

// Funksjonalitet for å oppdatere personopplysninger
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['oppdater_personopplysninger'])) {
    $navn = $_POST['navn'];
    $adresse = $_POST['adresse'];
    $telefon = $_POST['telefon'];
    $epost = $_POST['epost'];

    $query = "UPDATE brukere SET navn = ?, adresse = ?, telefon = ?, epost = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $navn, $adresse, $telefon, $epost, $bruker_id);
    $stmt->execute();

    echo "Personopplysninger oppdatert!";
}
?>

<h1>Rediger personopplysninger</h1>

<form method="POST">
    <input type="text" name="navn" value="<?php echo htmlspecialchars($bruker['navn']); ?>" required><br>
    <input type="text" name="adresse" value="<?php echo htmlspecialchars($bruker['adresse']); ?>" required><br>
    <input type="text" name="telefon" value="<?php echo htmlspecialchars($bruker['telefon']); ?>" required><br>
    <input type="email" name="epost" value="<?php echo htmlspecialchars($bruker['epost']); ?>" required><br>
    <button type="submit" name="oppdater_personopplysninger">Oppdater</button>
</form>

<a href="dashboard.php">Tilbake til dashboard</a>
