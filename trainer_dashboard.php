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

//SEHR WICHTIG FÜR BERECHTIGUNG
//Wenn keine Trainerrolle beim Login
if ($_SESSION["rolle"] != "Trainer") {
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
            spieler.team_id,
            teams.name AS teamname,
            teams.saison
        FROM spieler
        INNER JOIN teams ON spieler.team_id = teams.id
        WHERE spieler.benutzer_id = '$benutzer_id'";
// $sql ausführen mit mysqli_query
$result = mysqli_query($con, $sql);
//Assoziatives Array aus dem Ergebnis der SQL-Abfrage
//Nur für die eingeloggte $benutzer ID
$spieler = mysqli_fetch_assoc($result);
//team-id speichern für den eingeloggten Nutzer
$team_id = $spieler["team_id"];



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



// Teamstats abfragen
$sql_stat = "SELECT
                COUNT(*) AS spiele,
                SUM(tore > gegentore) AS siege,
                SUM(tore = gegentore) AS unentschieden,
                SUM(tore < gegentore) AS niederlagen,
                SUM(tore) AS tore,
                SUM(gegentore) AS gegentore
            FROM spiele
            WHERE team_id = '$team_id'
and datum <= NOW()
            ";
// $sql ausführen mit mysqli_query
$result_stat = mysqli_query($con, $sql_stat);
//Assoziatives Array aus dem Ergebnis der SQL-Abfrage
$statistik = mysqli_fetch_assoc($result_stat);



// Meisten Tore abfragen
$sql_tore = "SELECT
                    spieler.vorname,
                    spieler.nachname,
                    COUNT(spiel_ereignisse.id) AS tore
                FROM spieler
                INNER JOIN spiel_ereignisse ON spieler.id = spiel_ereignisse.spieler_id
                WHERE spieler.team_id = '$team_id'
                AND spiel_ereignisse.ereignis_typ = 'Tor'
                GROUP BY spieler.id, spieler.vorname, spieler.nachname
                ORDER BY tore DESC
                LIMIT 1";
// SQL ausführen
$result_tore = mysqli_query($con, $sql_tore);
// Ergebnis holen
$playerstatistik = mysqli_fetch_assoc($result_tore);



// Letzte 5 Spiele abfragen
$sql_last = "SELECT *
             FROM spiele
             WHERE team_id = '$team_id'
             AND tore IS NOT NULL
             AND gegentore IS NOT NULL
             and datum <= NOW()
             ORDER BY datum DESC
             LIMIT 3";
// $sql ausführen mit mysqli_query
$letzte_spiele = mysqli_query($con, $sql_last);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>FootballHub - Trainer</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <img src="svblankenese.png" alt="SV Blankenese Logo" height=100 width=100 style="display:block; margin-left: auto; margin-right: auto;">
        <br>

        <nav>
            <a href="trainer_dashboard.php" class="active">Dashboard</a>
            <a href="profil.php">Mein Profil</a>
            <a href="team.php">Team</a>
            <a href="stats.php">Statistik</a>
            <a href="spiele.php">Spiele</a>
            <a href="aufstellungen.php">Aufstellungen</a>
            <a href="training.php">Training</a>
        </nav>

        <a href="logout.php" class="logout">Abmelden</a>

    </aside>


    <!--Hauptinhalt-->
    <main class="content">

        <!--Klasse für Header-->
        <div class="header">
            <div>
                <h2>Hallo, <?php echo $spieler["vorname"]; ?> </h2>
                <p>Hier ist dein aktueller Überblick</p>
            </div>
        </div>

        <!--Klasse für Spielerinformationen-->
        <div class="player-info card">
            <div>
                <h3><?php echo $spieler["vorname"] . " " . $spieler["nachname"]; ?></h3>
                <p>
                    <?php echo $spieler["teamname"]; ?> ·
                    <?php echo $spieler["position"]; ?>
                </p>
            </div>
            <div class="player-number">
                <?php echo !empty($spieler["trikotnummer"]) ? $spieler["trikotnummer"] : 'TR'; ?>
            </div>
        </div>

        <!-- Klasse für nächste Spiele-->
        <div class="card">
            <h3>Nächstes Spiel</h3>
            <div class="next-game">
                <div>
                    <strong><?php echo $spieler["teamname"]; ?></strong>
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
        </div>

        <!--Klasse für Teamstats-->
        <h3 class="section-title">Teamstatistik</h3>

        <div class="cards">
            <div class="card stat-card">
                <span>Spiele</span>
                <strong><?php echo $statistik["spiele"]; ?></strong>
            </div>
            <div class="card stat-card">
                <span>Siege</span>
                <!--Prüfung ob "siege" einen Wert hat und gibt diesen wieder, ansonsten 0-->
                <strong><?php echo !empty($statistik["siege"]) ? $statistik["siege"] : 0; ?></strong>
            </div>
            <div class="card stat-card">
                <span>Unentschieden</span>
                <strong><?php echo !empty($statistik["unentschieden"]) ? $statistik["unentschieden"] : 0; ?></strong>
            </div>
            <div class="card stat-card">
                <span>Niederlagen</span>
                <strong><?php echo !empty($statistik["niederlagen"]) ? $statistik["niederlagen"] : 0; ?></strong>
            </div>
            <div class="card stat-card">
                <span>Tore</span>
                <strong><?php echo !empty($statistik["tore"]) ? $statistik["tore"] : 0; ?></strong>
            </div>
            <div class="card stat-card">
                <span>Gegentore</span>
                <strong><?php echo !empty($statistik["gegentore"]) ? $statistik["gegentore"] : 0; ?></strong>
            </div>
        </div>


        <h3 class="section-title">Spielerstatistik</h3>
        <div class="cards">
            <div class="card stat-card">
                <span>Meisten Tore</span>
                <h2>
                    <?php echo $playerstatistik["vorname"] . " " . $playerstatistik["nachname"]; ?>
                </h2>
                <b>
                    <?php echo $playerstatistik["tore"]; ?> Tore
                </b>
            </div>
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
