<?php
session_start();
require_once 'db.php';  // Inkluderer DB-tilkoblingen
require_once 'functions.php';


// Sjekk om brukeren er logget inn
if (!isset($_SESSION['bruker_id'])) {
    header("Location: login.php");
    exit();
}

$melding = "";

// Behandle betaling av regning
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['betal_regning'])) {
    // Hente data fra skjema
    $konto_id = $_POST['konto_id'];  // Konto ID som ble valgt
    $belop = $_POST['belop'];  // Beløpet som skal betales
    $kid = $_POST['kid'];  // KID-nummeret til betalingen
    
    // Sjekk at saldo på konto er nok
    $query = "SELECT saldo FROM kontoer WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $konto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $konto = $result->fetch_assoc();

    if ($konto && $konto['saldo'] >= $belop) {
        // Oppdater saldo på konto
        $ny_saldo = $konto['saldo'] - $belop;
        $query_update = "UPDATE kontoer SET saldo = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("di", $ny_saldo, $konto_id);
        $stmt_update->execute();

        // Legg inn transaksjonen for betalingen
        $query_trans = "INSERT INTO transaksjoner (konto_id, type, beløp, referanse) 
                        VALUES (?, 'betaling', ?, ?)";
        $stmt_trans = $conn->prepare($query_trans);
        $stmt_trans->bind_param("ids", $konto_id, $belop, $kid);
        $stmt_trans->execute();

        $melding = "<p style='color: green;'>Betaling gjennomført!</p>";
    } else {
        $melding = "<p style='color: red;'>Ikke nok penger på konto!</p>";
    }
}

// Hent brukerens kontoer
$bruker_id = $_SESSION['bruker_id'];
$query = "SELECT id, kontonummer FROM kontoer WHERE bruker_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bruker_id);
$stmt->execute();
$result = $stmt->get_result();
$kontoer = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Betal regning</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form { max-width: 400px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        label, input, select { display: block; width: 100%; margin-bottom: 10px; }
        button { background-color: #28a745; color: white; padding: 10px; border: none; cursor: pointer; width: 100%; }
        button:hover { background-color: #218838; }
        p { text-align: center; }
    </style>
</head>
<body>
    <h2>Betal regning</h2>
    
    <?php echo $melding; ?>

    <form method="POST" action="">
        <label for="konto_id">Velg konto:</label>
        <select name="konto_id" required>
            <?php foreach ($kontoer as $konto): ?>
                <option value="<?= htmlspecialchars($konto['id']) ?>">
                    <?= htmlspecialchars($konto['kontonummer']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="belop">Beløp:</label>
        <input type="number" name="belop" step="0.01" min="0.01" required>
        
        <label for="kid">KID-nummer:</label>
        <input type="text" name="kid" required>
        
        <button type="submit" name="betal_regning">Betal</button>
    </form>
    <a href="dashboard.php">Tilbake til dashboard</a>
</body>
</html>
