<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

//Datenbank einbeziehen
include ("db.php");
//ID in eine Variable packen
$benutzer_id = $_SESSION["id"];
$team_id = $_SESSION["team_id"];



// Spielerdaten aus Datenbank für den Nutzer mit der ID
$sql = "SELECT COUNT(*) AS spiele, SUM(tore > gegentore) AS siege,
SUM(tore = gegentore) AS unentschieden, SUM(tore < gegentore) AS niederlagen,
SUM(tore) AS tore, SUM(gegentore) AS gegentore FROM spiele WHERE team_id = '$team_id' and datum <= NOW()"
;

// $sql ausführen mit mysqli_query
$result = mysqli_query($con, $sql);

$stats = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>FootballHub - Statistik</title>
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


            <a href="profil.php">Mein Profil</a>
            <a href="team.php">Team</a>
            <a href="stats.php" class="active">Statistik</a>
            <a href="spiele.php">Spiele</a>
            <a href="aufstellungen.php">Aufstellungen</a>

            <?php if ($_SESSION["rolle"] == "Trainer") { ?>
                <a href="training.php">Training</a>
            <?php } ?>

        </nav>

        <a href="logout.php" class="logout">Abmelden</a>

    </aside>


    <main class="content">

        <h2>Statistik</h2>

        <div class="cards">

            <div class="card">
                <h3>Spiele</h3>
                <p>
                    <?php echo $stats["spiele"]; ?>
                </p>
            </div>

            <div class="card">
                <h3>Siege</h3>
                <p>
                    <?php echo $stats["siege"]; ?>
                </p>
            </div>

            <div class="card">
                <h3>Unentschieden</h3>
                <p>
                    <?php echo $stats["unentschieden"]; ?>
                </p>
            </div>

            <div class="card">
                <h3>Niederlagen</h3>
                <p>
                    <?php echo $stats["niederlagen"]; ?>
                </p>
            </div>

            <div class="card">
                <h3>Tore</h3>
                <p>
                    <?php echo $stats["tore"]; ?>
                </p>
            </div>

            <div class="card">
                <h3>Gegentore</h3>
                <p>
                    <?php echo $stats["gegentore"]; ?>
                </p>
            </div>

        </div>

    </main>

</div>

</body>
</html>