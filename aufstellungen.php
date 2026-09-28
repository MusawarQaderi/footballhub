<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}
include "db.php";

// Welche Aufstellung (Formation) soll geladen werden?
$formation_id = isset($_GET["aufstellung"]) ? (int)$_GET["aufstellung"] : 1;

// Aufstellung aus der Datenbank holen (Sicher mit Prepared Statement)
$sql = "SELECT 
            lineup.formation_id, 
            lineup.spieler_id, 
            spieler.vorname, 
            spieler.nachname, 
            spieler.trikotnummer, 
            lineup.position_x, 
            lineup.position_y, 
            lineup.position_rolle 
        FROM lineup 
        JOIN spieler ON lineup.spieler_id = spieler.id 
        WHERE lineup.formation_id = ? 
        ORDER BY lineup.id";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $formation_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Aufstellungen</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Taktikfeld im HOCHFORMAT (Torwart unten, Angriff oben) */
        .taktikfeld {
            position: relative;
            width: 100%;
            max-width: 600px; /* Schmaler als vorher, für echtes Hochformat */
            height: 800px;    /* Viel höher als vorher */
            margin: 30px auto;
            background: #218c45;
            border: 3px solid white;
            box-sizing: border-box;
            overflow: hidden;
            border-radius: 5px;
        }
        
        /* Linien auf dem Feld */
        .mittellinie {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background: white;
            transform: translateY(-50%);
        }
        .mittelkreis {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120px;
            height: 120px;
            transform: translate(-50%, -50%);
            border: 2px solid white;
            border-radius: 50%;
        }
        .mittelpunkt {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 50%;
        }
        
        /* Straf- und Torraum Oben (Gegner) */
        .strafraum-oben {
            position: absolute;
            top: 0;
            left: 20%;
            width: 60%;
            height: 120px;
            border: 2px solid white;
            border-top: none;
            box-sizing: border-box;
        }
        .torraum-oben {
            position: absolute;
            top: 0;
            left: 35%;
            width: 30%;
            height: 50px;
            border: 2px solid white;
            border-top: none;
            box-sizing: border-box;
        }

        /* Straf- und Torraum Unten (Eigenes Team) */
        .strafraum-unten {
            position: absolute;
            bottom: 0;
            left: 20%;
            width: 60%;
            height: 120px;
            border: 2px solid white;
            border-bottom: none;
            box-sizing: border-box;
        }
        .torraum-unten {
            position: absolute;
            bottom: 0;
            left: 35%;
            width: 30%;
            height: 50px;
            border: 2px solid white;
            border-bottom: none;
            box-sizing: border-box;
        }

        /* Design der Spieler */
        .spieler-pin {
            position: absolute;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
        }
        .spieler-kreis {
            width: 40px;
            height: 40px;
            background: #fff;
            border: 3px solid #0b5ed7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b5ed7;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 3px 8px rgba(0,0,0,0.5);
        }
        .spieler-info {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            text-align: center;
            margin-top: 5px;
            min-width: 60px;
        }
        .spieler-name {
            font-size: 11px;
            font-weight: bold;
            display: block;
            white-space: nowrap;
        }
        .spieler-position {
            font-size: 9px;
            color: #ccc;
            display: block;
        }

        /* Navigation für die Formationen */
        .formationen { margin-bottom: 20px; }
        .formationen a {
            display: inline-block;
            padding: 10px 18px;
            margin-right: 8px;
            background: #eeeeee;
            color: #222;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .formationen a:hover { background: #dddddd; }
        .formationen a.aktiv { background: #0b5ed7; color: white; }
        
        .empty-state {
            text-align: center;
            color: white;
            position: absolute;
            top: 40%;
            width: 100%;
            font-size: 1.2em;
            background: rgba(0,0,0,0.5);
            padding: 10px 0;
        }
    </style>
</head>

<body>
<div class="dashboard">
    <aside class="sidebar">
        <img src="svblankenese.png" alt="SV Blankenese Logo" height="100" width="100" style="display:block; margin-left:auto; margin-right:auto;">
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
            <a href="spiele.php">Spiele</a>
            <a href="aufstellungen.php" class="active">Aufstellungen</a>
            <?php if ($_SESSION["rolle"] == "Trainer") { ?>
                <a href="training.php">Training</a>
            <?php } ?>
        </nav>
        <a href="logout.php" class="logout">Abmelden</a>
    </aside>

    <main class="content">
        <h2>Aufstellungen</h2>
        <div class="card">
            <h3>Aufstellung auswählen</h3>
            <div class="formationen">
                <a href="aufstellungen.php?aufstellung=1" class="<?php echo ($formation_id == 1) ? 'aktiv' : ''; ?>">3-2-3-2</a>
                <a href="aufstellungen.php?aufstellung=2" class="<?php echo ($formation_id == 2) ? 'aktiv' : ''; ?>">4-2-3-1</a>
            </div>
            
            <div class="taktikfeld">
                <!-- Linien -->
                <div class="mittellinie"></div>
                <div class="mittelkreis"></div>
                <div class="mittelpunkt"></div>
                <div class="strafraum-oben"></div>
                <div class="torraum-oben"></div>
                <div class="strafraum-unten"></div>
                <div class="torraum-unten"></div>

                <!-- Spieler laden -->
                <?php 
                if (mysqli_num_rows($result) > 0) {
                    while ($spieler = mysqli_fetch_assoc($result)) { 
                ?>
                    <div class="spieler-pin" style="left: <?php echo htmlspecialchars($spieler["position_x"]); ?>%; top: <?php echo htmlspecialchars($spieler["position_y"]); ?>%;">
                        <div class="spieler-kreis">
                            <?php echo !empty($spieler["trikotnummer"]) ? htmlspecialchars($spieler["trikotnummer"]) : "-"; ?>
                        </div>
                        <div class="spieler-info">
                            <span class="spieler-name"><?php echo htmlspecialchars($spieler["nachname"]); ?></span>
                            <span class="spieler-position"><?php echo htmlspecialchars($spieler["position_rolle"]); ?></span>
                        </div>
                    </div>
                <?php 
                    } 
                } else {
                    echo '<div class="empty-state">Noch keine Spieler für diese Formation aufgestellt.</div>';
                }
                ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
