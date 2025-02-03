<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['bruker_id'])) {
    die("Du må være logget inn for å bruke dashboardet.");
}

$bruker_id = $_SESSION['bruker_id'];

// Hent brukerens kontoer
$query = "SELECT id, kontonummer, saldo FROM kontoer WHERE bruker_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bruker_id);
$stmt->execute();
$kontoer = $stmt->get_result();

// Hent tilgjengelige kontotyper
$kontotyper = ["Brukskonto", "Sparekonto", "Bedriftskonto"];

// Behandle skjemaer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['opprett_konto'])) {
        $kontotype = $_POST['kontotype'];

        // Generer kontonummer med ønsket format
        $del1 = rand(1000, 9999);      // Genererer 4 sifre (xxxx)
        $del2 = rand(10, 99);          // Genererer 2 sifre (yy)
        $del3 = rand(10000, 99999);    // Genererer 5 sifre (zzzzz)

        $kontonummer = "bank." . $kontotype . "." . $del1 . "." . $del2 . "." . $del3;

        $saldo = 0;

        $query = "INSERT INTO kontoer (bruker_id, kontonummer, kontotype, saldo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issd", $bruker_id, $kontonummer, $kontotype, $saldo);
        $stmt->execute();

        logg_aktivitet($conn, $bruker_id, "Opprettet ny konto: $kontonummer ($kontotype)");
        echo "Bankkonto opprettet!";

        header("Location: dashboard.php");
        exit;
    } 
    
    if (isset($_POST['sett_inn'])) {
        $konto_id = $_POST['konto_id'];
        $belop = $_POST['belop'];

        $query = "UPDATE kontoer SET saldo = saldo + ? WHERE id = ? AND bruker_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("dii", $belop, $konto_id, $bruker_id);
        $stmt->execute();

        logg_aktivitet($conn, $bruker_id, "Satte inn $belop NOK på konto ID $konto_id");
        echo "Innskudd gjennomført!";

        header("Location: dashboard.php");
        exit;
    }

    if (isset($_POST['uttak'])) {
        $konto_id = $_POST['konto_id'];
        $belop = $_POST['belop'];

        $query = "SELECT saldo FROM kontoer WHERE id = ? AND bruker_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $konto_id, $bruker_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $konto = $result->fetch_assoc();

        if ($konto && $konto['saldo'] >= $belop) {
            $query = "UPDATE kontoer SET saldo = saldo - ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("di", $belop, $konto_id);
            $stmt->execute();

            logg_aktivitet($conn, $bruker_id, "Tok ut $belop NOK fra konto ID $konto_id");
            echo "Uttak gjennomført!";

            header("Location: dashboard.php");
            exit;
        } else {
            echo "<p style='color:red;'>Ikke nok penger på konto!</p>";
        }
    }

    if (isset($_POST['overfor'])) {
        $fra_konto = $_POST['fra_konto'];
        $til_konto = $_POST['til_konto'];
        $belop = $_POST['belop'];

        if ($fra_konto != $til_konto && $belop > 0) {
            $query = "SELECT saldo FROM kontoer WHERE id = ? AND bruker_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $fra_konto, $bruker_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $konto = $result->fetch_assoc();

            if ($konto && $konto['saldo'] >= $belop) {
                $conn->begin_transaction();

                $query = "UPDATE kontoer SET saldo = saldo - ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("di", $belop, $fra_konto);
                $stmt->execute();

                $query = "UPDATE kontoer SET saldo = saldo + ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("di", $belop, $til_konto);
                $stmt->execute();

                logg_aktivitet($conn, $bruker_id, "Overførte $belop NOK fra konto ID $fra_konto til konto ID $til_konto");

                $conn->commit();
                echo "Overføring vellykket!";

                header("Location: dashboard.php");
                exit;

            } else {
                echo "<p style='color:red;'>Ikke nok penger på konto!</p>";
            }
        }
    }
}
?>

<h2>Dashboard</h2>

<h3>Opprett ny konto</h3>
<form method="POST">
    <label for="kontotype">Velg kontotype:</label>
    <select name="kontotype" required>
        <?php foreach ($kontotyper as $type) { ?>
            <option value="<?= $type ?>"><?= $type ?></option>
        <?php } ?>
    </select>
    <button type="submit" name="opprett_konto">Opprett</button>
</form>

<h3>Sett inn penger</h3>
<form method="POST">
    <label for="konto_id">Velg konto:</label>
    <select name="konto_id" required>
        <?php $stmt->execute(); $kontoer = $stmt->get_result(); ?>
        <?php while ($row = $kontoer->fetch_assoc()) { ?>
            <option value="<?= $row['id'] ?>"><?= $row['kontonummer'] ?> (Saldo: <?= $row['saldo'] ?> NOK)</option>
        <?php } ?>
    </select>
    <label for="belop">Beløp:</label>
    <input type="number" name="belop" step="0.01" min="0.01" required>
    <button type="submit" name="sett_inn">Sett inn</button>
</form>

<h3>Ta ut penger</h3>
<form method="POST">
    <label for="konto_id">Velg konto:</label>
    <select name="konto_id" required>
        <?php $stmt->execute(); $kontoer = $stmt->get_result(); ?>
        <?php while ($row = $kontoer->fetch_assoc()) { ?>
            <option value="<?= $row['id'] ?>"><?= $row['kontonummer'] ?> (Saldo: <?= $row['saldo'] ?> NOK)</option>
        <?php } ?>
    </select>
    <label for="belop">Beløp:</label>
    <input type="number" name="belop" step="0.01" min="0.01" required>
    <button type="submit" name="uttak">Ta ut</button>
</form>

<h3>Overfør penger</h3>
<form method="POST">
    <label for="fra_konto">Fra konto:</label>
    <select name="fra_konto" required>
        <?php $stmt->execute(); $kontoer = $stmt->get_result(); ?>
        <?php while ($row = $kontoer->fetch_assoc()) { ?>
            <option value="<?= $row['id'] ?>"><?= $row['kontonummer'] ?></option>
        <?php } ?>
    </select>

    <label for="til_konto">Til konto:</label>
    <select name="til_konto" required>
        <?php $stmt->execute(); $kontoer = $stmt->get_result(); ?>
        <?php while ($row = $kontoer->fetch_assoc()) { ?>
            <option value="<?= $row['id'] ?>"><?= $row['kontonummer'] ?></option>
        <?php } ?>
    </select>

    <label for="belop">Beløp:</label>
    <input type="number" name="belop" step="0.01" min="0.01" required>
    <button type="submit" name="overfor">Overfør</button>
</form>
<a href="kontooversikt.php">Oversikt kontoer</a> |
<a href="betal_regning.php">Betal regning</a> |
<a href="rediger_person.php">Rediger personopplysninger</a> |
<a href="logout.php">Logg ut</a>