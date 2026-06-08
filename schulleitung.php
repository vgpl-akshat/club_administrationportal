<?php
// Startet die Session, bindet die DB-Verbindung ein und zeigt die Navigation.
session_start();
require_once __DIR__ . '/connection.php';

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

// Nur Schulleitung darf diese Seite aufrufen.
requireLogin('schulleitung');
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>Schulleitung</title></head>
<body>
<?php renderNav(); ?>

<h1>Schulleitung – Übersicht</h1>
<p>Angemeldet als: <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></p>

<h2>Alle AGs</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Name</th><th>Leitung</th><th>Wochentag</th><th>Raum</th><th>Teilnehmer</th><th>Status</th></tr>
    <?php
    // Alle AGs mit Teilnahmezahlen laden, um den Status für die Schulleitung anzuzeigen.
    // Der Status wird anschließend basierend auf der Teilnehmerzahl automatisch aktualisiert.
        $stmt = $conn->query(
            "SELECT ag.Name, ag.Raum, ag.Wochentag, ag.FindetStatt,
                    lehrer.Vorname, lehrer.Nachname,
                    COUNT(t.TID) AS Anzahl
            FROM ag
            LEFT JOIN lehrer ON ag.Leitung = lehrer.Kuerzel
            LEFT JOIN teilnahme t ON ag.Name = t.AgName
            GROUP BY ag.Name
            ORDER BY ag.Name"
        );
        while ($ag = $stmt->fetch(PDO::FETCH_ASSOC)):
            // Status automatisch aktualisieren, wenn die Teilnehmerzahl die Schwelle über-/unterschreitet.
            $sollStatus = $ag['Anzahl'] > 10 ? 1 : 0;
            if ($ag['FindetStatt'] != $sollStatus) {
                $upd = $conn->prepare("UPDATE ag SET FindetStatt = ? WHERE Name = ?");
                $upd->execute([$sollStatus, $ag['Name']]);
                $ag['FindetStatt'] = $sollStatus;
            }
    ?>
    <tr>
        <td><?= htmlspecialchars($ag['Name']) ?></td>
        <td><?= htmlspecialchars($ag['Vorname'] . ' ' . $ag['Nachname']) ?></td>
        <td><?= htmlspecialchars($ag['Wochentag']) ?></td>
        <td><?= htmlspecialchars($ag['Raum']) ?></td>
        <td><?= $ag['Anzahl'] ?> / 10</td>
        <td><?= $ag['FindetStatt'] ? 'findet statt' : 'findet nicht statt' ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
