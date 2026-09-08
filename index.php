<?php
// Startet session, damit sich der Browser die Anmeldung merkt
session_start();
// Lädt die Verbindung zur Datenbank aus der Datei db.php
include ("db.php");
// Variable für spätere Fehlermeldungen
$fehler = "";





//LOGIN
// Wenn login durchgeführt wurde
if (isset($_POST["login"])) {
    // E-Mail wird aus dem Formular geholt
    $email = $_POST["email"];
    // Passwort wirde aus dem Formular geholt
    $passwort = $_POST["passwort"];
    // SQL-Statement für die Auswahl aus der Datenbank
    $sql = "SELECT * 
            FROM benutzer
            INNER JOIN spieler ON benutzer.id = spieler.benutzer_id
            WHERE benutzer.email = '$email' AND benutzer.passwort = '$passwort'";
    // Führt das Statement aus, $con ist die Datenbankverbindung aus db.php
    $result = mysqli_query($con, $sql);
    // Wenn Suche einen Fehler hat
    if (!$result) {
        // Stoppt alles und zeigt den Datenbank-Fehler an
        die("SQL Fehler: " . mysqli_error($con));
    }
    // Wenn genau 1 passender Benutzer für die Auswahl aus dem Statement gefunden wurde
    if (mysqli_num_rows($result) == 1) {
        // Packt die Daten des Benutzers (Assoziatives Array für E-Mail und passwort) in eine Variable
        $benutzer = mysqli_fetch_assoc($result);
        // Merkt sich die Benutzer-ID im Browser
        $_SESSION["id"] = $benutzer["id"];
        // Merkt sich die Rolle im Browser
        $_SESSION["rolle"] = $benutzer["rolle"];
        $_SESSION["team_id"] = $benutzer["team_id"];
        // Wenn Benutzer ein Trainer ist
        if ($benutzer["rolle"] == "Trainer") {
            // dann erfolgt eine Weiterleitung zum Trainer Dashboard
            header("Location: trainer_dashboard.php");
            // Skript abbrechen
            die();
            // Wenn Benutzer Spieler statt Trainer ist
        } elseif ($benutzer["rolle"] == "Spieler") {
            // dann erfolgt eine Weiterleitung zum Trainer Dashboard
            header("Location: spieler_dashboard.php");
            // Skript abbrechen
            die();
            // Wenn Rolle weder Spieler noch Trainer ist
        } else {
            // Fehlermeldung für falsche Rolle
            $fehler = "Rolle ist nicht korrekt.";
        }
        // Falls kei Benutzer mit diesen Daten gefunden wurde
    } else {
        // Schreibe Fehler auf
        $fehler = "E-Mail oder Passwort falsch.";
    }
}
?>





<!--FORMULAR-->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Blankenese - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Ein Kasten für das  Layout -->
<div class="login-container">

    <!-- Die weiße Box in der Mitte vom Bildschirm -->
    <div class="login-box">

        <h1>Teamverwaltung</h1>
        <h1>SV Blankenese</h1>
        <p>Login für Spieler und Trainer</p>

        <?php
        // Wenn ein Fehler aufgeschrieben wurde
        if (!empty($fehler)) {

            // Zeige die rote Fehlermeldung auf der Webseite an
            echo '<div class="error">' . $fehler . '</div>';
        }
        ?>

        <form method="POST">
            <label for="email">E-Mail</label>
            <input type="email" id="email" name="email" required>
            <label for="passwort">Passwort</label>
            <input type="password" id="passwort" name="passwort" required>
            <button type="submit" name="login">Einloggen</button>

        </form>

    </div>

</div>

</body>
</html>