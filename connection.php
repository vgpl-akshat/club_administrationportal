<?php
// Stellt die PDO-Datenbankverbindung zur MySQL-Datenbank her.
$servername = "localhost";
$dbname = "ag_verwaltung";
$username = "root";
$password = "Akshat08";

try {
    $conn = new PDO("mysql:host=" . $servername . ";dbname=" . $dbname . ";charset=utf8", $username, $password);
    // PDO auf Ausnahmen einstellen, damit Fehler sauber abgefangen werden können.
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
