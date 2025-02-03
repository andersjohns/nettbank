
<?php
session_start();
require_once 'db.php';  // Inkluder databaseforbindelsen

// Sjekk om brukeren er logget inn som administrator
if (!isset($_SESSION['bruker_id']) || $_SESSION['rolle'] !== 'admin') {
    echo "Du har ikke tilgang til denne siden.";
    exit;
}

// Funksjon for å logge aktiviteter
function logg_aktivitet($conn, $bruker_id, $melding) {
    $query = "INSERT INTO aktivitetslogger (bruker_id, handling, tidspunkt) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $bruker_id, $melding);
    $stmt->execute();
}

// Funksjon for å beregne renter
function beregn_renter($conn) {
    // Hent saldo og renter for alle kontoer
    $query = "SELECT k.id AS konto_id, k.saldo, k.bruker_id, r.rente
              FROM kontoer k
              JOIN renter r ON k.kontotype = r.kontotype";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $konto_id = $row['konto_id'];
        $saldo = $row['saldo'];
        $bruker_id = $row['bruker_id'];
        $rentesats = $row['rente'];  

        // Beregn renter (forenklet til månedlig rente)
        $rentebelop = ($saldo * $rentesats) / 100;

        if ($rentebelop > 0) {
            // Oppdater saldo med rentebeløpet
            $query_update = "UPDATE kontoer SET saldo = saldo + ? WHERE id = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param("di", $rentebelop, $konto_id);
            $stmt_update->execute();

            // Registrer transaksjonen i transaksjonsloggen
            $query_trans = "INSERT INTO transaksjoner (konto_id, type, beløp, referanse) 
                            VALUES (?, 'rente', ?, 'Renteinnskudd')";
            $stmt_trans = $conn->prepare($query_trans);
            $stmt_trans->bind_param("id", $konto_id, $rentebelop);
            $stmt_trans->execute();

            // Logg aktiviteten
            logg_aktivitet($conn, $bruker_id, "Rente beregnet og lagt til konto $konto_id: +$rentebelop NOK");
        }
    }
    echo "<p style='color: green;'>Renter er beregnet og lagt til kontoene!</p>";
}

// Utfør renteberegning hvis knappen trykkes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['beregn_rente'])) {
    beregn_renter($conn);
}

?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renteberegning</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; }
        form { margin-top: 20px; }
        button { padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

    <h2>Renteberegning</h2>
    <p>Klikk på knappen nedenfor for å beregne og legge til renter på alle kontoer.</p>

    <form method="POST" action="renteberegning.php">
        <button type="submit" name="beregn_rente">Beregn Renter</button>
    </form>

</body>
</html>
