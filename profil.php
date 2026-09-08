<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    die();
}
include ("db.php");
$benutzer_id = $_SESSION["id"];
$rolle = $_SESSION["rolle"];



// Benutzer und Rollendaten abfragen
$sql = "SELECT benutzer.email, 
       spieler.vorname, 
       spieler.nachname, 
       spieler.position, 
       spieler.trikotnummer,
       spieler.geburtsdatum, 
       spieler.alter, 
       teams.name AS teamname
            FROM benutzer 
            INNER JOIN spieler ON benutzer.id = spieler.benutzer_id 
            LEFT JOIN teams ON spieler.team_id = teams.id 
            WHERE benutzer.id = '$benutzer_id'";


$result = mysqli_query($con, $sql);

$profil = mysqli_fetch_assoc($result);
?>




<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Mein Profil</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <img src="svblankenese.png" alt="SV Blankenese Logo" height=100 width=100 style="display:block; margin-left: auto; margin-right: auto;">
        <br>

        <nav>
            <?php if ($_SESSION["rolle"] == "Spieler") { ?>
                <a href="spieler_dashboard.php">Dashboard</a>
            <?php } else { ?>
                <a href="trainer_dashboard.php">Dashboard</a>
            <?php } ?>

            <a href="profil.php" class="active">Mein Profil</a>
            <a href="team.php">Team</a>
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
        <h2>Mein Profil</h2>

        <div class="card">
            <h3>Persönliche Daten</h3>
            <p><strong>Name:</strong> <?php echo $profil["vorname"] . " " . $profil["nachname"]; ?></p>
            <p><strong>E-Mail:</strong> <?php echo $profil["email"]; ?></p>
            <p><strong>Geburtsdatum:</strong> <?php echo date("d.m.Y", strtotime($profil["geburtsdatum"])) ?></p>
            <p><strong>Alter:</strong> <?php echo $profil["alter"]; ?></p>
            <p><strong>Rolle:</strong> <?php echo $rolle; ?></p>
            <p><strong>Team:</strong> <?php echo $profil["teamname"]; ?></p>

            <?php if ($rolle == "Spieler") { ?>
                <p><strong>Position:</strong> <?php echo !empty($profil["position"]) ? $profil["position"] : "Keine Position"; ?></p>
                <p><strong>Trikotnummer:</strong> <?php echo !empty($profil["trikotnummer"]) ? $profil["trikotnummer"] : "-"; ?></p>
            <?php } ?>
        </div>

        <?php if ($rolle == "Spieler") { ?>
        <div class="card">
            <h3>Statistiken</h3>
        </div>
            <?php } ?>
    </main>

</div>

</body>
</html>