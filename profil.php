<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    die();
}
include ("db.php");

$benutzer_id = $_SESSION["id"];
$rolle = $_SESSION["rolle"];

// Sichere Abfrage mit Prepared Statement
// Das Alter wird berechnet und die Saison-Statistiken (Tore, Vorlagen) werden direkt mitgeladen
$sql = "SELECT benutzer.email, 
               spieler.vorname, 
               spieler.nachname, 
               spieler.position, 
               spieler.trikotnummer,
               spieler.geburtsdatum, 
               TIMESTAMPDIFF(YEAR, spieler.geburtsdatum, CURDATE()) AS berechnetes_alter,
               teams.name AS teamname,
               st.spiele_gespielt,
               st.tore,
               st.vorlagen
        FROM benutzer 
        LEFT JOIN spieler ON benutzer.id = spieler.benutzer_id 
        LEFT JOIN teams ON spieler.team_id = teams.id 
        LEFT JOIN spieler_saison_statistiken st ON spieler.id = st.spieler_id
        WHERE benutzer.id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $benutzer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$profil = mysqli_fetch_assoc($result);

// Fallbacks, falls Werte noch nicht existieren (z.B. bei Trainern)
$tore = $profil["tore"] ?? 0;
$vorlagen = $profil["vorlagen"] ?? 0;
$spiele_gespielt = $profil["spiele_gespielt"] ?? 0;
$name_anzeige = (!empty($profil["vorname"]) && !empty($profil["nachname"])) ? $profil["vorname"] . " " . $profil["nachname"] : "Kein Name hinterlegt";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FootballHub - Mein Profil</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .stat-highlight {
            font-size: 1.5em;
            color: #daa520; /* Gold für deine Tore */
            font-weight: bold;
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

        <div class="cards" style="display: flex; gap: 20px; flex-wrap: wrap;">
            
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3>Persönliche Daten</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name_anzeige); ?></p>
                <p><strong>E-Mail:</strong> <?php echo htmlspecialchars($profil["email"]); ?></p>
                
                <?php if (!empty($profil["geburtsdatum"])) { ?>
                    <p><strong>Geburtsdatum:</strong> <?php echo date("d.m.Y", strtotime($profil["geburtsdatum"])); ?></p>
                    <p><strong>Alter:</strong> <?php echo htmlspecialchars($profil["berechnetes_alter"]); ?> Jahre</p>
                <?php } ?>
                
                <p><strong>Rolle:</strong> <?php echo htmlspecialchars($rolle); ?></p>
                
                <?php if (!empty($profil["teamname"])) { ?>
                    <p><strong>Team:</strong> <?php echo htmlspecialchars($profil["teamname"]); ?></p>
                <?php } ?>

                <?php if ($rolle == "Spieler") { ?>
                    <p><strong>Position:</strong> <?php echo !empty($profil["position"]) ? htmlspecialchars($profil["position"]) : "Keine Position"; ?></p>
                    <p><strong>Trikotnummer:</strong> <?php echo !empty($profil["trikotnummer"]) ? htmlspecialchars($profil["trikotnummer"]) : "-"; ?></p>
                <?php } ?>
            </div>

            <?php if ($rolle == "Spieler") { ?>
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3>Meine Saison-Statistik</h3>
                <p><strong>Einsätze:</strong> <?php echo htmlspecialchars($spiele_gespielt); ?></p>
                <p><strong>Tore:</strong> <span class="stat-highlight"><?php echo htmlspecialchars($tore); ?> ⚽</span></p>
                <p><strong>Vorlagen:</strong> <?php echo htmlspecialchars($vorlagen); ?></p>
            </div>
            <?php } ?>

        </div>
    </main>

</div>

</body>
</html>
