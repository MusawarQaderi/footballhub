<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION["rolle"] != "Trainer") {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>FootballHub - Training</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <img src="svblankenese.png" alt="SV Blankenese Logo" height=100 width=100 style="display:block; margin-left: auto; margin-right: auto;">
        <br>

        <nav>

            <a href="trainer_dashboard.php">Dashboard</a>

                <a href="profil.php">Mein Profil</a>

            <a href="team.php">Team</a>
            <a href="stats.php">Statistik</a>
            <a href="spiele.php">Spiele</a>
            <a href="aufstellungen.php">Aufstellungen</a>
            <a href="training.php" class="active">Training</a>

        </nav>

        <a href="logout.php" class="logout">Abmelden</a>

    </aside>


    <main class="content">

        <h2>Training</h2>

        <div class="card">

            <h3>Training verwalten</h3>

            <button>Training erstellen</button>

        </div>

    </main>

</div>

</body>
</html>