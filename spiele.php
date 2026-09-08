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



// Letzte Spiele abfragen
$sql_last = "SELECT *
             FROM spiele
             WHERE team_id = '$team_id'
             AND tore IS NOT NULL
             AND gegentore IS NOT NULL
             and datum <= NOW()
             ORDER BY datum DESC";
// $sql ausführen mit mysqli_query
$letzte_spiele = mysqli_query($con, $sql_last);




// Abfrage für nächstes Spiel
$sql_next = "SELECT *
             FROM spiele
             WHERE team_id = '$team_id'
             and datum >= NOW()
             ORDER BY datum ASC
             LIMIT 1";
// $sql ausführen mit mysqli_query
$result_next = mysqli_query($con, $sql_next);
//Assoziatives Array aus dem Ergebnis der SQL-Abfrage
$naechstes_spiel = mysqli_fetch_assoc($result_next);

?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>FootballHub - Spiele</title>
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
            <a href="stats.php">Statistik</a>
            <a href="spiele.php" class="active">Spiele</a>
            <a href="aufstellungen.php">Aufstellungen</a>

            <?php if ($_SESSION["rolle"] == "Trainer") { ?>
                <a href="training.php">Training</a>
            <?php } ?>

        </nav>

        <a href="logout.php" class="logout">Abmelden</a>

    </aside>


    <main class="content">

        <h2>Spiele</h2>
        <!-- Klasse für nächste Spiele-->
        <h3 class="section-title">Nächste Spiele</h3>

        <div class="card">
                <div class="next-game">

                    <div>
                        <strong><?php echo "Blankenese 1."; ?></strong>
                    </div>
                    <div class="vs">VS</div>
                    <div>
                        <strong><?php echo $naechstes_spiel["gegner"]; ?></strong>
                    </div>
                </div>
                <p>
                    <?php echo date("d.m.Y H:i", strtotime($naechstes_spiel["datum"])); ?> Uhr
                </p>
                <p>
                    <?php echo $naechstes_spiel["heim-auswärts"]; ?>
                </p>
            <p>
                Aufstellung: <a href="http://localhost/footballhub/aufstellungen.php?aufstellung=2"
                                name="4231"
                                style="font-weight: bold"
                                style="color: #000000">4 2 3 1</a>
            </p>



        </div>





        <!--Klasse für letzten Spiele-->
        <h3 class="section-title">Letzte Spiele</h3>

        <div class="card">
                <div class="games">
                    <?php while ($spiel = mysqli_fetch_assoc($letzte_spiele)): ?>
                        <div class="game">
                            <div>
                                <strong><?php echo $spiel["gegner"]; ?></strong>
                                <small><?php echo date("d.m.Y", strtotime($spiel["datum"])); ?></small>
                            </div>
                            <strong>
                                <?php echo $spiel["tore"]; ?> : <?php echo $spiel["gegentore"]; ?>
                            </strong>
                        </div>
                    <?php endwhile; ?>
                </div>
        </div>
    </main>

</div>

</body>
</html>