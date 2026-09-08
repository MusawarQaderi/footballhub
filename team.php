<?php
session_start();
$con = "";

//Wenn keine ID zugeordnet werden kann
if (!isset($_SESSION["id"])) {
    // Zurück zur Login Seite
    header("Location: index.php");
    // Rest des Skripts abbrechen
    die();
}



//Datenbank einbeziehen
include ("db.php");
//ID in eine Variable packen
$benutzer_id = $_SESSION["id"];
$team_id = $_SESSION["team_id"];



// Spielerdaten aus Datenbank für den Nutzer mit der ID
$sql = "SELECT
            spieler.id,
            spieler.vorname,
            spieler.nachname,
            spieler.position,
            spieler.trikotnummer,
            spieler.geburtsdatum,
            spieler.alter,
            spieler.team_id,
            teams.name AS teamname,
            teams.saison
        FROM spieler
        INNER JOIN teams ON spieler.team_id = teams.id
        WHERE spieler.team_id = '$team_id'";

// $sql ausführen mit mysqli_query
$result = mysqli_query($con, $sql);


//leeres Array für while-Schleife
$alle_spieler = [];

//Assoziatives Array aus dem Ergebnis der SQL-Abfrage
while ($row = mysqli_fetch_assoc($result)) {
    //Alle abfragen speichern
    $alle_spieler[] = $row;
}

////vorher
//while ($row = mysqli_fetch_assoc($result)) {
//
//}
//$spieler= mysqli_fetch_assoc($result);

?>









<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>FootballHub - Team</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <img src="svblankenese.png" alt="SV Blankenese Logo" height=100 width=100 style="display:block; margin-left: auto; margin-right: auto;">
        <br>

        <nav>

            <?php
            if ($_SESSION["rolle"] == "Spieler") {
                ?>
                <a href="spieler_dashboard.php">Dashboard</a>
            <?php }
            else {
                ?>
                <a href="trainer_dashboard.php">Dashboard</a>
            <?php } ?>


                <a href="profil.php">Mein Profil</a>

            <a href="team.php" class="active">Team</a>
            <a href="stats.php">Statistik</a>
            <a href="spiele.php">Spiele</a>
            <a href="aufstellungen.php">Aufstellungen</a>

            <?php if ($_SESSION["rolle"] == "Trainer") { ?>
                <a href="training.php">Training</a>
            <?php } ?>

        </nav>

        <a href="logout.php" class="logout">Abmelden</a>

    </aside>


    <main class="content">

        <h2>Mein Team</h2>

        <div class="card">

            <h3>FootballHub</h3>
<!--            foreach-Schleife für alle Spielerkarten-->
<!--            jedes element abfragen und in eine card packen-->
            <?php foreach ($alle_spieler as $spieler) {?>

            <div class="player-info card">
                <div>
                    <h3><?php echo $spieler["vorname"] . " " . $spieler["nachname"]; ?></h3>
                    <p>
                        <strong>Team: </strong><?php echo $spieler["teamname"]; ?>
                        <br>
                        <strong>Position: </strong><?php echo !empty(["position"]) ? $spieler["position"] : "Trainer";?>
                        <br>
                        <strong>Geburtsdatum: </strong><?php echo $spieler["geburtsdatum"]; ?>
                        <br>
                        <strong>Alter: </strong><?php echo $spieler["alter"]; ?>

                    </p>
                </div>
                <div class="player-number">
                    <?php echo !empty($spieler["trikotnummer"]) ? $spieler["trikotnummer"] : "TR"; ?>
                </div>
            </div>
            <?php } ?>


        </div>

    </main>

</div>

</body>
</html>