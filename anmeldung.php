<?php
// Startet die Session und bindet die Datenbankverbindung ein.
session_start();
require_once __DIR__ . '/connection.php';

// Rendert die Hauptnavigation oben auf der Seite.
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

// Datei für die Verarbeitung der AG-Anmeldung: prüft Eingaben, legt Schüler an, und speichert die Teilnahme.
// Fehler oder Erfolg werden später in der Formularausgabe angezeigt.
$fehler  = [];
$erfolg  = false;
$vorauswahl = $_GET['ag'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzereingaben aus dem Formular lesen und trimmen.
    $Vorname  = trim($_POST['Vorname']  ?? '');
    $Nachname = trim($_POST['Nachname'] ?? '');
    $Email    = trim($_POST['Email']    ?? '');
    $Klasse   = $_POST['Klasse']  ?? '';
    $AgName   = $_POST['AgName']  ?? '';

    if ($Vorname  === '') {
        $fehler[] = 'Vorname ist ein Pflichtfeld.';
    }
    if ($Nachname === '') {
        $fehler[] = 'Nachname ist ein Pflichtfeld.';
    }
    if ($Klasse   === '') {
        $fehler[] = 'Bitte eine Klasse wählen.';
    }
    if ($AgName   === '') {
        $fehler[] = 'Bitte eine AG wählen.';
    }
    if ($Email === '' || !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $fehler[] = 'Bitte eine gültige E-Mail-Adresse eingeben.';
    }

    if (empty($fehler)) {
        // Schüler anlegen oder vorhandenen Schüler anhand der Daten finden.
        // So kann derselbe Schüler mehrfach eine AG wählen, ohne ein zweites Profil zu erzeugen.
        $check = $conn->prepare("SELECT SID FROM schueler WHERE Vorname = ? AND Nachname = ? AND Klasse = ?");
        $check->execute([$Vorname, $Nachname, $Klasse]);
        $schueler = $check->fetch(PDO::FETCH_ASSOC);

        if ($schueler) {
            $SID = $schueler['SID'];
        } else {
            $ins = $conn->prepare("INSERT INTO schueler (Vorname, Nachname, Email, Klasse) VALUES (?, ?, ?, ?)");
            $ins->execute([$Vorname, $Nachname, $Email, $Klasse]);
            $SID = $conn->lastInsertId();
        }

        // Doppelte Anmeldung verhindern: gleiche Kombination aus Schüler und AG nicht erneut speichern.
        $doppelt = $conn->prepare("SELECT TID FROM teilnahme WHERE AgName = ? AND SID = ?");
        $doppelt->execute([$AgName, $SID]);
        if ($doppelt->fetch()) {
            $fehler[] = 'Du bist für diese AG bereits angemeldet.';
        } else {
            // Neue Anmeldung speichern und den Genehmigungsstatus auf offen setzen.
            $stmt = $conn->prepare("INSERT INTO teilnahme (AgName, SID, Genehmigt) VALUES (?, ?, 0)");
            $stmt->execute([$AgName, $SID]);

            // Prüfen, ob die AG aufgrund der Teilnehmerzahl automatisch freigegeben werden soll.
            $zStmt = $conn->prepare("SELECT COUNT(*) FROM teilnahme WHERE AgName = ?");
            $zStmt->execute([$AgName]);
            if ($zStmt->fetchColumn() > 10) {
                $upd = $conn->prepare("UPDATE ag SET FindetStatt = 1 WHERE Name = ?");
                $upd->execute([$AgName]);
            }

            $erfolg = true;
        }
    }
}

// Listen für die Formular-Auswahl laden: AGs und Klassen.
$ags     = $conn->query("SELECT Name, Wochentag FROM ag ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
$klassen = $conn->query("SELECT Klasse FROM klassen ORDER BY Klasse")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>AG-Anmeldung</title></head>
<body>
<?php renderNav(); ?>

<h1>AG-Anmeldung</h1>

<?php if ($erfolg): ?>
    <p><strong>Anmeldung erfolgreich! Du wirst benachrichtigt, sobald deine Anmeldung bestätigt wurde.</strong></p>
    <a href="index.php">Zurück zur Startseite</a>
<?php else: ?>
    <?php foreach ($fehler as $f): ?>
        <p><strong><?= htmlspecialchars($f) ?></strong></p>
    <?php endforeach; ?>

    <form method="POST" action="anmeldung.php">
        <label>Vorname *<br>
            <input type="text" name="Vorname" value="<?= htmlspecialchars($_POST['Vorname'] ?? '') ?>" required>
        </label><br><br>

        <label>Nachname *<br>
            <input type="text" name="Nachname" value="<?= htmlspecialchars($_POST['Nachname'] ?? '') ?>" required>
        </label><br><br>

        <label>E-Mail *<br>
            <input type="email" name="Email" value="<?= htmlspecialchars($_POST['Email'] ?? '') ?>" required>
        </label><br><br>

        <label>Klasse *<br>
            <select name="Klasse" required>
                <option value="">-- Bitte wählen --</option>
                <?php foreach ($klassen as $k): ?>
                    <option value="<?= htmlspecialchars($k['Klasse']) ?>"
                        <?= (($_POST['Klasse'] ?? '') === $k['Klasse']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['Klasse']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>AG *<br>
            <select name="AgName" required>
                <option value="">-- Bitte wählen --</option>
                <?php foreach ($ags as $ag): ?>
                    <option value="<?= htmlspecialchars($ag['Name']) ?>"
                        <?= (($_POST['AgName'] ?? $vorauswahl) === $ag['Name']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ag['Name']) ?> (<?= htmlspecialchars($ag['Wochentag']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Anmeldung absenden</button>
    </form>
<?php endif; ?>
</body>
</html>
