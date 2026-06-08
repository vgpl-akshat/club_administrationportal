<?php
// Startet die PHP-Session für die Nutzer-Anmeldung und Rollenverwaltung.
// Lädt außerdem die Datenbankverbindung, die auf jeder Seite benötigt wird.
session_start();
require_once __DIR__ . '/connection.php';

// Rendert die globale Navigation basierend auf der aktuellen Benutzerrolle.
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

// Hilfsfunktion zur Absicherung geschützter Bereiche (nicht notwendig für Startseite).
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

// Startseite: zeigt die AG-Übersicht und das Anmeldeformular für Schülerinnen und Schüler.
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>AG-Portal</title></head>
<body>
<?php renderNav(); ?>

<h1>AG Portal - Goethe Gymnasium Bensheim</h1>
<p>Hier findest du alle AGs unserer Schule. Klicke auf eine AG für Details oder melde dich direkt an.</p>
<p><em>Hinweis: Eine AG findet nur statt, wenn mindestens 10 Teilnehmer angemeldet sind.</em></p>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Name</th>
        <th>Leitung</th>
        <th>Raum</th>
        <th>Wochentag</th>
        <th>Status</th>
        <th>Beschreibung</th>
    </tr>
    <?php
    // Lädt alle AGs inklusive der Leitungsdaten für die Startseite.
    // Die Übersicht wird öffentlich angezeigt, deshalb erfolgt hier keine Zugriffskontrolle.
    $stmt = $conn->query(
        "SELECT ag.Name, ag.Raum, ag.Wochentag, ag.FindetStatt,
                ag.Beschreibung, lehrer.Vorname, lehrer.Nachname
         FROM ag
         LEFT JOIN lehrer ON ag.Leitung = lehrer.Kuerzel
         ORDER BY ag.Wochentag"
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        $status = $row['FindetStatt'] ? 'findet statt' : 'findet nicht statt';
    ?>
    <tr>
        <td><?= htmlspecialchars($row['Name']) ?></td>
        <td><?= htmlspecialchars($row['Vorname'] . ' ' . $row['Nachname']) ?></td>
        <td><?= htmlspecialchars($row['Raum']) ?></td>
        <td><?= htmlspecialchars($row['Wochentag']) ?></td>
        <td><?= $status ?></td>
        <td><?= htmlspecialchars(mb_substr($row['Beschreibung'], 0, 120)) ?><?= strlen($row['Beschreibung']) > 120 ? '…' : '' ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<p>Mehr Details zu den AGs am Goethe-Gymnasium gibts <a href="https://www.goethe-bensheim.de/index.php/unterricht/arbeitsgemeinschaften" target="_blank" rel="noopener noreferrer">hier</a>.</p>

<?php
// Daten für das Anmeldeformular laden: verfügbare AGs und Klassen.
// Die Auswahlfelder werden dynamisch aus der Datenbank gefüllt.
$ags_select = $conn->query("SELECT Name, Wochentag FROM ag ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
$klassen = $conn->query("SELECT Klasse FROM klassen ORDER BY Klasse")->fetchAll(PDO::FETCH_ASSOC);
$vorauswahl = $_GET['ag'] ?? '';
?>

            <h2>AG-Anmeldung</h2>
            <p><small>* Pflichtfeld</small></p>
<form method="POST" action="anmeldung.php">
    <label>Vorname *<br>
        <input type="text" name="Vorname" required>
    </label><br><br>

    <label>Nachname *<br>
        <input type="text" name="Nachname" required>
    </label><br><br>

    <label>E-Mail *<br>
        <input type="email" name="Email" required>
    </label><br><br>

    <label>Klasse *<br>
        <select name="Klasse" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($klassen as $k): ?>
                <option value="<?= htmlspecialchars($k['Klasse']) ?>"><?= htmlspecialchars($k['Klasse']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>AG *<br>
        <select name="AgName" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($ags_select as $ag): ?>
                <option value="<?= htmlspecialchars($ag['Name']) ?>" <?= ($vorauswahl === $ag['Name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ag['Name']) ?> (<?= htmlspecialchars($ag['Wochentag']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <button type="submit">Anmelden</button>
</form>
</body>
</html>
