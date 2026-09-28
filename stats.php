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

// 1. SICHERHEIT: Prepared Statements für Team-Statistiken
$sql_team = "SELECT platzierung, punkte, spiele, siege, unentschieden, niederlagen, tore, gegentore 
             FROM liga_tabelle 
             WHERE team_id = ?";
$stmt = mysqli_prepare($con, $sql_team);
mysqli_stmt_bind_param($stmt, "i", $team_id);
mysqli_stmt_execute($stmt);
$result_team = mysqli_stmt_get_result($stmt);
$team_stats = mysqli_fetch_assoc($result_team);

if (!$team_stats) {
    $team_stats = ["platzierung" => "-", "punkte" => 0, "spiele" => 0, "siege" => 0, "unentschieden" => 0, "niederlagen" => 0, "tore" => 0, "gegentore" => 0];
}

// 2. Persönliche Spieler-Statistik holen
$sql_player = "SELECT st.tore, st.vorlagen, st.spiele_gespielt 
               FROM spieler_saison_statistiken st 
               JOIN spieler s ON s.id = st.spieler_id 
               WHERE s.benutzer_id = ?";
$stmt2 = mysqli_prepare($con, $sql_player);
mysqli_stmt_bind_param($stmt2, "i", $benutzer_id);
mysqli_stmt_execute($stmt2);
$result_player = mysqli_stmt_get_result($stmt2);
$player_stats = mysqli_fetch_assoc($result_player);

if (!$player_stats) {
    $player_stats = ["tore" => 0, "vorlagen" => 0, "spiele_gespielt" => 0];
}

// 3. NEUES FEATURE: Top 5 Torschützen des Teams
$sql_top_scorers = "SELECT s.vorname, s.nachname, st.tore 
                    FROM spieler_saison_statistiken st 
                    JOIN spieler s ON s.id = st.spieler_id 
                    WHERE s.team_id = ? 
                    ORDER BY st.tore DESC 
                    LIMIT 5";
$stmt3 = mysqli_prepare($con, $sql_top_scorers);
mysqli_stmt_bind_param($stmt3, "i", $team_id);
mysqli_stmt_execute($stmt3);
$result_top_scorers = mysqli_stmt_get_result($stmt3);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Statistik</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Optimiertes Styling */
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            flex: 1 1 calc(25% - 20px);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .card h3 {
            margin-top: 0;
            color: #666;
            font-size: 1.1em;
        }
        .card p {
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
            margin: 10px 0 0 0;
        }
        
        /* Die Gold-Karte für deine Rekorde */
        .gold-card {
            background: linear-gradient(135deg, #ffd700 0%, #ffdf00 100%);
            border: 2px solid #daa520;
            box-shadow: 0 4px 15px rgba(218, 165, 32, 0.4);
            transform: scale(1.05);
        }
        .gold-card h3 { color: #554000; }
        .gold-card p.record-number {
            font-size: 3em;
            color: #000;
            text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
        }

        /* Styling für die Top-Torjäger-Tabelle */
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .ranking-table th, .ranking-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .ranking-table th {
            background-color: #f8f9fa;
            color: #333;
        }
        .ranking-table tr:hover { background-color: #f1f1f1; }
        .ranking-table tr:first-child td { font-weight: bold; color: #daa520; } /* Platz 1 markieren */

        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            color: #2c3e50;
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
                    <?php echo htmlspecialchars($player_stats["tore"]); ?> ⚽
                </p>
            </div>
            <div class="card">
                <h3>Vorlagen</h3>
                <p><?php echo htmlspecialchars($player_stats["vorlagen"]); ?></p>
            </div>
            <div class="card">
                <h3>Einsätze</h3>
                <p><?php echo htmlspecialchars($player_stats["spiele_gespielt"]); ?></p>
            </div>
        </div>

        <h2 class="section-title">Top Torjäger des Teams</h2>
        <table class="ranking-table">
            <thead>
                <tr>
                    <th>Platz</th>
                    <th>Spieler</th>
                    <th>Tore</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $platz = 1;
                while ($scorer = mysqli_fetch_assoc($result_top_scorers)) { 
                    echo "<tr>";
                    echo "<td>" . $platz . ".</td>";
                    echo "<td>" . htmlspecialchars($scorer['vorname'] . " " . $scorer['nachname']) . "</td>";
                    echo "<td>" . htmlspecialchars($scorer['tore']) . " ⚽</td>";
                    echo "</tr>";
                    $platz++;
                } 
                ?>
            </tbody>
        </table>

        <h2 class="section-title">Team-Statistik (Liga)</h2>
        <div class="cards">
            <div class="card">
                <h3>Platzierung</h3>
                <p><?php echo htmlspecialchars($team_stats["platzierung"]); ?>.</p>
            </div>
            <div class="card">
                <h3>Punkte</h3>
                <p><?php echo htmlspecialchars($team_stats["punkte"]); ?></p>
            </div>
            <div class="card">
                <h3>Spiele</h3>
                <p><?php echo htmlspecialchars($team_stats["spiele"]); ?></p>
            </div>
            <div class="card">
                <h3>Siege</h3>
                <p><?php echo htmlspecialchars($team_stats["siege"]); ?></p>
            </div>
            <div class="card">
                <h3>Remis</h3>
                <p><?php echo htmlspecialchars($team_stats["unentschieden"]); ?></p>
            </div>
            <div class="card">
                <h3>Niederlagen</h3>
                <p><?php echo htmlspecialchars($team_stats["niederlagen"]); ?></p>
            </div>
            <div class="card">
                <h3>Tore</h3>
                <p><?php echo htmlspecialchars($team_stats["tore"]); ?></p>
            </div>
            <div class="card">
                <h3>Gegentore</h3>
                <p><?php echo htmlspecialchars($team_stats["gegentore"]); ?></p>
            </div>
        </div>

    </main>
</div>

</body>
</html>
