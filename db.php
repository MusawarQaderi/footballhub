<?php

// Verbindung zum Datenbankserver herstellen
$con = mysqli_connect("172.16.201.214", "kurs", "kurs2026", "kurs_qaderi");

// Wenn Verbindung nicht hergestellt wurde,
// mit "die" wird das Skript beendet
// und "mysqli_error" die Fehlermeldung ausgegeben
if (!$con) {
    die("Verbindung fehlgeschlagen: " . mysqli_error($con));
}

?>