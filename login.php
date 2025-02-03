<?php
session_start();
require_once 'db.php';  // Inkluderer DB-tilkoblingen

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $epost = $_POST['epost'];
    $passord = $_POST['passord'];

    // SQL-spørring for å hente bruker basert på epost
    $query = "SELECT * FROM brukere WHERE epost = ? AND er_aktiv = 1"; // Sjekk om bruker er aktiv
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $epost);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $bruker = $result->fetch_assoc();
        // Verifiser passordet
        if (password_verify($passord, $bruker['passord_hash'])) {
            // Lagre brukerens id i session for tilgang på dashboard
            $_SESSION['bruker_id'] = $bruker['id'];
            $_SESSION['rolle'] = $bruker['rolle'];  // Sett rolle i session (kan brukes for tilgangskontroll)

            // Omdiriger til dashboard basert på brukerens rolle
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Feil passord.";
        }
    } else {
        echo "Bruker ikke funnet.";
    }
}
?>

<form method="POST" action="login.php">
    <input type="email" name="epost" placeholder="E-post" required><br>
    <input type="password" name="passord" placeholder="Passord" required><br>
    <button type="submit">Logg inn</button>
</form>
