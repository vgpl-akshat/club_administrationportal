<?php
// Lehrerbereich: zeigt dem leitenden Lehrer nur seine eigenen AGs und deren Anmeldungen.
session_start();
require_once __DIR__ . '/connection.php';

// Rendert das globale Navigationsmenü mit rollenbasierten Links.
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

// Nur Lehrer dürfen diese Seite sehen.
requireLogin('lehrer');

if (empty($_SESSION['kuerzel'])) {
    $_SESSION['kuerzel'] = $_SESSION['name'];
}
$Kuerzel = $_SESSION['kuerzel'];

// Wenn ein Lehrer eine Anmeldung bestätigt, prüfen wir die Berechtigung und setzen den Status.
// Diese Aktion ist nur für Anmeldungen aus den eigenen AGs erlaubt.
if (isset($_GET['genehmigen'])) {
    $TID = (int)$_GET['genehmigen'];
    // Sicherstellen dass Anmeldung zur eigenen AG gehört
    $check = $conn->prepare(
        "SELECT t.TID FROM teilnahme t
         JOIN ag ON t.AgName = ag.Name
         WHERE t.TID = ? AND ag.Leitung = ?"
    );
    $check->execute([$TID, $Kuerzel]);
    if ($check->fetch()) {
        $upd = $conn->prepare("UPDATE teilnahme SET Genehmigt = 1 WHERE TID = ?");
        $upd->execute([$TID]);
    }
    header('Location: /ag_verwaltung/lehrer.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>Meine AGs</title></head>
<body>
<?php renderNav(); ?>

<h1>Meine AGs</h1>
<p>Angemeldet als: <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></p>

<h2>Übersicht</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>AG</th><th>Raum</th><th>Wochentag</th><th>Teilnehmer</th><th>Status</th></tr>
    <?php
    // Alle AGs laden, die dieser Lehrer leitet, inklusive Anzahl der Teilnehmer.
    // So sieht der Lehrer nur seine eigenen Kurse.
    $stmt = $conn->prepare(
        "SELECT ag.Name, ag.Raum, ag.Wochentag, ag.FindetStatt,
                COUNT(t.TID) AS Anzahl
         FROM ag
         LEFT JOIN teilnahme t ON ag.Name = t.AgName
         WHERE ag.Leitung = ?
         GROUP BY ag.Name"
    );
    $stmt->execute([$Kuerzel]);
    while ($ag = $stmt->fetch(PDO::FETCH_ASSOC)):
    ?>
    <tr>
        <td><?= htmlspecialchars($ag['Name']) ?></td>
        <td><?= htmlspecialchars($ag['Raum']) ?></td>
        <td><?= htmlspecialchars($ag['Wochentag']) ?></td>
        <td><?= $ag['Anzahl'] ?></td>
        <td><?= $ag['FindetStatt'] ? 'findet statt' : 'findet nicht statt' ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<h2>Anmeldungen</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Vorname</th><th>Nachname</th><th>Klasse</th><th>AG</th><th>Status</th><th>Aktion</th></tr>
    <?php
    // Alle Teilnehmeranmeldungen für die eigenen AGs anzeigen.
    $stmt2 = $conn->prepare(
        "SELECT t.TID, s.Vorname, s.Nachname, s.Klasse, t.AgName, t.Genehmigt
         FROM teilnahme t
         JOIN schueler s ON t.SID = s.SID
         JOIN ag ON t.AgName = ag.Name
         WHERE ag.Leitung = ?
         ORDER BY t.AgName, s.Nachname"
    );
    $stmt2->execute([$Kuerzel]);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)):
    ?>
    <tr>
        <td><?= htmlspecialchars($row['Vorname']) ?></td>
        <td><?= htmlspecialchars($row['Nachname']) ?></td>
        <td><?= htmlspecialchars($row['Klasse']) ?></td>
        <td><?= htmlspecialchars($row['AgName']) ?></td>
        <td><?= $row['Genehmigt'] ? 'bestätigt' : 'offen' ?></td>
        <td>
            <?php if (!$row['Genehmigt']): ?>
                <a href="lehrer.php?genehmigen=<?= $row['TID'] ?>">Bestätigen</a>
            <?php else: ?>
                ✓
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
