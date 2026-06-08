<?php
// Session starten und zentrale Datenbankverbindung laden.
session_start();
require_once __DIR__ . '/connection.php';

// Anzeige der globalen Navigation mit Links für alle Rollen.
function renderNav() {
    $rolle = $_SESSION['rolle'] ?? '';
    echo "<nav><strong>AG-Portal</strong> | ";
    echo "<a href=\"/ag_verwaltung/index.php\">Startseite</a> | ";
    echo "<a href=\"/ag_verwaltung/impressum.php\">Impressum</a> | ";
    if (!$rolle) {
        echo "<a href=\"/ag_verwaltung/login.php\">Login</a>";
    } elseif ($rolle === "admin") {
        echo "<a href=\"/ag_verwaltung/admin/index.php\">Admin</a> | ";
        echo "<a href=\"/ag_verwaltung/logout.php\">Logout</a>";
    } elseif ($rolle === "lehrer") {
        echo "<a href=\"/ag_verwaltung/lehrer.php\">Lehrer</a> | ";
        echo "<a href=\"/ag_verwaltung/logout.php\">Logout</a>";
    } elseif ($rolle === "schulleitung") {
        echo "<a href=\"/ag_verwaltung/schulleitung.php\">Schulleitung</a> | ";
        echo "<a href=\"/ag_verwaltung/logout.php\">Logout</a>";
    }
    echo "</nav><hr>";
}
// Diese Seite ist öffentlich, deshalb wird hier keine Anmeldung verlangt.
function requireLogin($rolle = null)
{
    if (!isset($_SESSION['name']))
    {
        header('Location: /ag_verwaltung/login.php');
        exit;
    }

    if ($rolle !== null && $_SESSION['rolle'] !== $rolle)
    {
        die('Zugriff verweigert.');
    }
}

// Detailseite für eine einzelne AG: lädt Informationen zur AG und zur Lehrkraft aus der Datenbank.
$agName = $_GET['name'] ?? '';
if ($agName === '') {
    // Ohne AG-Name kann keine Detailseite angezeigt werden. Daher zurück zur Übersicht.
    header('Location: index.php');
    exit;
}

// AG-Datensatz mit Leitungsinformationen aus der Datenbank laden.
$stmt = $conn->prepare(
    "SELECT ag.*, lehrer.Vorname, lehrer.Nachname
     FROM ag
     LEFT JOIN lehrer ON ag.Leitung = lehrer.Kuerzel
     WHERE ag.Name = ?"
);
$stmt->execute([$agName]);
$ag = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ag) {
    die('AG nicht gefunden.');
}

$zStmt = $conn->prepare("SELECT COUNT(*) FROM teilnahme WHERE AgName = ?");
$zStmt->execute([$agName]);
$anzahl = $zStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title><?= htmlspecialchars($ag['Name']) ?></title></head>
<body>
<?php renderNav(); ?>

<h1><?= htmlspecialchars($ag['Name']) ?></h1>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Leitung</th><td><?= htmlspecialchars($ag['Vorname'] . ' ' . $ag['Nachname']) ?></td></tr>
    <tr><th>Raum</th><td><?= htmlspecialchars($ag['Raum']) ?></td></tr>
    <tr><th>Wochentag</th><td><?= htmlspecialchars($ag['Wochentag']) ?></td></tr>
    <tr><th>Status</th><td><?= $ag['FindetStatt'] ? 'findet statt' : 'findet nicht statt' ?></td></tr>
    <tr><th>Angemeldete Teilnehmer</th><td><?= $anzahl ?></td></tr>
</table>
<h2>Über diese AG</h2>
<p><?= nl2br(htmlspecialchars($ag['Beschreibung'])) ?></p>
<a href="anmeldung.php?ag=<?= urlencode($ag['Name']) ?>">Jetzt anmelden</a> &nbsp;|&nbsp;
<a href="index.php">Zurück zur Übersicht</a>
</body>
</html>
