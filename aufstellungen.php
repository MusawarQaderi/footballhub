<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}
include "db.php";


// Welche Aufstellung
if (isset($_GET["aufstellung"])) {
    $aufstellung_id = (int) $_GET["aufstellung"];
} else {
    $aufstellung_id = 1;
}


// Aufstellung aus der Datenbank holen
$sql = "
    SELECT
        lineup.aufstellung_id,
        lineup.spieler_id,
        spieler.vorname,
        spieler.nachname,
        spieler.trikotnummer,
        lineup.position_x,
        lineup.position_y,
        lineup.position_rolle
    FROM lineup 
    JOIN spieler 
        ON lineup.spieler_id = spieler.id
    WHERE lineup.aufstellung_id = $aufstellung_id
    ORDER BY lineup.id
";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Aufstellungen</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .taktikfeld {
            position: relative;
            width: 100%;
            max-width: 900px;
            height: 600px;
            margin: 30px auto;
            background: #218c45;
            border: 3px solid white;
            box-sizing: border-box;
        }
        .mittellinie {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background: white;
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
        .strafraum-oben {
            position: absolute;
            top: 0;
            left: 25%;
            width: 50%;
            height: 120px;
            border: 2px solid white;
            border-top: none;
            box-sizing: border-box;
        }
        .torraum-oben {
            position: absolute;
            top: 0;
            left: 38%;
            width: 24%;
            height: 50px;
            border: 2px solid white;
            border-top: none;
            box-sizing: border-box;
        }
        .strafraum-unten {
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 120px;
            border: 2px solid white;
            border-bottom: none;
            box-sizing: border-box;
        }
        .torraum-unten {
            position: absolute;
            bottom: 0;
            left: 38%;
            width: 24%;
            height: 50px;
            border: 2px solid white;
            border-bottom: none;
            box-sizing: border-box;
        }
        .spieler {
            position: absolute;
            width: 60px;
            height: 60px;
            transform: translate(-50%, -50%);
            background: white;
            border: 3px solid #0b5ed7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #222;
            font-size: 11px;
            font-weight: bold;
            box-shadow: 0 3px 8px rgba(0,0,0,0.35);
        }
        .spieler-name {
            max-width: 55px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .spieler-position {
            position: absolute;
            top: 63px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }
        .formationen {
            margin-bottom: 20px;
        }
        .formationen a {
            display: inline-block;
            padding: 10px 18px;
            margin-right: 8px;
            background: #eeeeee;
            color: #222;
            text-decoration: none;
            border-radius: 6px;
        }
        .formationen a:hover {
            background: #dddddd;
        }
        .formationen a.aktiv {
            background: #0b5ed7;
            color: white;
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
                <a href="spieler_dashboard.php">
                    Dashboard
                </a>
            <?php } else { ?>
                <a href="trainer_dashboard.php">
                    Dashboard
                </a>
            <?php } ?>

            <a href="profil.php">Mein Profil</a>
            <a href="team.php">Team</a>
            <a href="stats.php">Statistik</a>
            <a href="spiele.php">Spiele</a>
            <a href="aufstellungen.php" class="active">Aufstellungen</a>
            <?php if ($_SESSION["rolle"] == "Trainer") { ?><a href="training.php">Training</a><?php } ?>
        </nav>
        <a href="logout.php" class="logout">Abmelden</a>
    </aside>



    <main class="content">
        <h2>Aufstellungen</h2>
        <div class="card">
            <h3>Aufstellung auswählen</h3>
            <div class="formationen">
                <a href="aufstellungen.php?aufstellung=1"
                   class="<?php if ($aufstellung_id == 1) {
                       echo 'aktiv';
                        } ?>">3-2-3-2</a>

                <a href="aufstellungen.php?aufstellung=2"
                   class="<?php if ($aufstellung_id == 2) {
                       echo 'aktiv';
                        } ?>">4-2-3-1</a>
            </div>
            <div class="taktikfeld">
                <div class="mittellinie"></div>
                <div class="mittelkreis"></div>
                <div class="mittelpunkt"></div>
                <div class="strafraum-oben"></div>
                <div class="torraum-oben"></div>
                <div class="strafraum-unten"></div>
                <div class="torraum-unten"></div>



<!--<!--                --><?php
////                while ($spieler = mysqli_fetch_assoc($result)) {
////                    ?>
<!--<!--                    <div-->
<!--<!--                            class="spieler"-->
<!--<!--                            style="left: --><?php ////= htmlspecialchars($spieler["position_y"]) ?><!--/*%;*/-->
<!--/*                                    top: */--><?php ////= htmlspecialchars($spieler["position_x"]) ?><!--/*%;">*/-->
<!--/*                        <span class="spieler-name">*/-->
<!--/*                            */--><?php ////= htmlspecialchars($spieler["nachname"]) ?>
<!--<!--                        </span>-->
<!--<!--                        <span class="spieler-position">-->
<!--<!--                            --><?php ////= htmlspecialchars(
////                                    $spieler["position_rolle"]
////                            ) ?>
<!--<!--                        </span>-->
<!--<!--                    </div>-->
<!--<!--                    --><?php
////                }
                ///
                ///
                ///
////                ?>
                    <?php
                    while ($spieler = mysqli_fetch_assoc($result)) {
                        echo '<div class="spieler" style="left: ' . $spieler["position_y"] . '%; top: ' . $spieler["position_x"] . '%;">';
                        echo '<span class="spieler-name">' . $spieler["nachname"] . '</span>';
                        echo '<span class="spieler-position">' . $spieler["position_rolle"] . '</span>';
                        echo '</div>';
                    }
                    ?>

            </div>
        </div>
    </main>
</div>
</body>
</html>
