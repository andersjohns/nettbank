<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $navn = $_POST['navn'];
    $adresse = $_POST['adresse'];
    $telefon = $_POST['telefon'];
    $epost = $_POST['epost'];
    $passord = $_POST['passord'];
    $er_virksomhet = isset($_POST['er_virksomhet']) ? 1 : 0; // Hvis checkbox er markert, sett som virksomhet
    $rolle = 'user';  // Standard rolle for ny bruker
    $er_aktiv = 1;  // Standardverdi for aktiv status

    // Krypter passordet
    $passord_hash = password_hash($passord, PASSWORD_BCRYPT);

    // SQL-spørring for å sette inn ny bruker
    $query = "INSERT INTO brukere (navn, adresse, telefon, epost, passord_hash, er_virksomhet, er_aktiv, rolle) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssiis", $navn, $adresse, $telefon, $epost, $passord_hash, $er_virksomhet, $er_aktiv, $rolle);
    $stmt->execute();

    echo "Bruker registrert!";
}
?>

<form method="POST" action="registrer.php">
    <input type="text" name="navn" placeholder="Navn" required><br>
    <input type="text" name="adresse" placeholder="Adresse"><br>
    <input type="text" name="telefon" placeholder="Telefon"><br>
    <input type="email" name="epost" placeholder="E-post" required><br>
    <input type="password" name="passord" placeholder="Passord" required><br>
    <label>
        Er dette en virksomhet?
        <input type="checkbox" name="er_virksomhet"> Ja
    </label><br>
    <button type="submit">Registrer</button>
</form>
