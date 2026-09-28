<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Datenbank einbeziehen
include("db.php");

// ID in eine Variable packen
$team_id = $_SESSION["team_id"];

// SQL Abfrage mit Prepared Statement (Sicher!)
// Wir berechnen das Alter live aus dem Geburtsdatum
$sql = "SELECT 
            spieler.id, 
            spieler.vorname, 
            spieler.nachname, 
            spieler.position, 
            spieler.trikotnummer, 
            spieler.geburtsdatum, 
            TIMESTAMPDIFF(YEAR, spieler.geburtsdatum, CURDATE()) AS berechnetes_alter,
            teams.name AS teamname
        FROM spieler
        INNER JOIN teams ON spieler.team_id = teams.id
        WHERE spieler.team_id = ?
        ORDER BY spieler.trikotnummer ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $team_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Array erstellen und Spieler nach Positionen gruppieren
$kader = [
    "Torwart" => [],
    "Abwehr" => [],
    "Mittelfeld" => [],
    "Sturm" => []
];

while ($row = mysqli_fetch_assoc($result)) {
    $pos = $row['position'];
    // Fallback, falls die Position unbekannt ist
    if (!array_key_exists($pos, $kader)) {
        $pos = "Mittelfeld"; 
    }
    $kader[$pos][] = $row;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Team</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Styling für die Positionen und Spieler-Karten */
        .position-group {
            margin-bottom: 40px;
        }
        .position-title {
            font-size: 1.5em;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        .players-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .player-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            flex: 1 1 calc(33.333% - 15px); /* 3 Karten nebeneinander */
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }
        .player-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .player-info h3 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 1.1em;
        }
        .player-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
            line-height: 1.4;
        }
        .player-number {
            background: #3498db;
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.4);
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
        <h2>Kader Übersicht - SV Blankenese</h2>

        <?php foreach ($kader as $position => $spieler_liste) { ?>
            <?php if (count($spieler_liste) > 0) { ?>
                <div class="position-group">
                    <h3 class="position-title"><?php echo $position; ?></h3>
                    
                    <div class="players-container">
                        <?php foreach ($spieler_liste as $spieler) { ?>
                            <div class="player-card">
                                <div class="player-info">
                                    <h3><?php echo htmlspecialchars($spieler["vorname"] . " " . $spieler["nachname"]); ?></h3>
                                    <p>
                                        <strong>Team:</strong> <?php echo htmlspecialchars($spieler["teamname"]); ?><br>
                                        <strong>Geburtstag:</strong> <?php echo htmlspecialchars(date("d.m.Y", strtotime($spieler["geburtsdatum"]))); ?><br>
                                        <strong>Alter:</strong> <?php echo htmlspecialchars($spieler["berechnetes_alter"]); ?> Jahre
                                    </p>
                                </div>
                                <div class="player-number">
                                    <?php echo !empty($spieler["trikotnummer"]) ? htmlspecialchars($spieler["trikotnummer"]) : "-"; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            <?php } ?>
        <?php } ?>

    </main>

</div>

</body>
</html>
