<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Datenbank einbeziehen
include("db.php");

// ID in eine Variable packen
$benutzer_id = $_SESSION["id"];
$team_id = $_SESSION["team_id"];

// 1. Team-Statistiken aus der neuen Liga-Tabelle holen
$sql_team = "SELECT platzierung, punkte, spiele, siege, unentschieden, niederlagen, tore, gegentore 
             FROM liga_tabelle 
             WHERE team_id = '$team_id'";
$result_team = mysqli_query($con, $sql_team);
$team_stats = mysqli_fetch_assoc($result_team);

// Fallback, falls die Tabelle noch leer sein sollte
if (!$team_stats) {
    $team_stats = [
        "platzierung" => "-", "punkte" => 0, "spiele" => 0, 
        "siege" => 0, "unentschieden" => 0, "niederlagen" => 0, 
        "tore" => 0, "gegentore" => 0
    ];
}

// 2. Persönliche Spieler-Statistik holen (Deine 127 Tore!)
$sql_player = "SELECT st.tore, st.vorlagen, st.spiele_gespielt 
               FROM spieler_saison_statistiken st 
               JOIN spieler s ON s.id = st.spieler_id 
               WHERE s.benutzer_id = '$benutzer_id'";
$result_player = mysqli_query($con, $sql_player);
$player_stats = mysqli_fetch_assoc($result_player);

// Fallback, falls keine Spielerstatistiken gefunden wurden
if (!$player_stats) {
    $player_stats = ["tore" => 0, "vorlagen" => 0, "spiele_gespielt" => 0];
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Statistik</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Zusätzliches Styling für deinen Torrekord */
        .gold-card {
            background: linear-gradient(135deg, #ffd700 0%, #ffdf00 100%);
            color: #000;
            border: 2px solid #daa520;
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.4);
        }
        .gold-card h3 {
            color: #333;
        }
        .record-number {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
            text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
        }
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
    </style>
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">
        <img src="svblankenese.png" alt="SV Blankenese Logo" height="100" width="100" style="display:block; margin-left: auto; margin-right: auto;">
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

        <h2 class="section-title">Meine Saison-Statistik</h2>
        <div class="cards">
            <div class="card gold-card">
                <h3>Tore</h3>
                <p class="record-number">
                    <?php echo $player_stats["tore"]; ?> ⚽
                </p>
            </div>
            <div class="card">
                <h3>Vorlagen</h3>
                <p><?php echo $player_stats["vorlagen"]; ?></p>
            </div>
            <div class="card">
                <h3>Einsätze</h3>
                <p><?php echo $player_stats["spiele_gespielt"]; ?></p>
            </div>
        </div>

        <h2 class="section-title">Team-Statistik (Liga)</h2>
        <div class="cards">
            <div class="card">
                <h3>Tabellenplatz</h3>
                <p><?php echo $team_stats["platzierung"]; ?>.</p>
            </div>
            <div class="card">
                <h3>Punkte</h3>
                <p><?php echo $team_stats["punkte"]; ?></p>
            </div>
            <div class="card">
                <h3>Spiele</h3>
                <p><?php echo $team_stats["spiele"]; ?></p>
            </div>
            <div class="card">
                <h3>Siege</h3>
                <p><?php echo $team_stats["siege"]; ?></p>
            </div>
            <div class="card">
                <h3>Unentschieden</h3>
                <p><?php echo $team_stats["unentschieden"]; ?></p>
            </div>
            <div class="card">
                <h3>Niederlagen</h3>
                <p><?php echo $team_stats["niederlagen"]; ?></p>
            </div>
            <div class="card">
                <h3>Tore</h3>
                <p><?php echo $team_stats["tore"]; ?></p>
            </div>
            <div class="card">
                <h3>Gegentore</h3>
                <p><?php echo $team_stats["gegentore"]; ?></p>
            </div>
        </div>

    </main>
</div>

</body>
</html>
