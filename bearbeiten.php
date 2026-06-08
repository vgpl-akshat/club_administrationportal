<?php
// Admin-Formular für die Bearbeitung einer einzelnen Anmeldung.
session_start();
require_once __DIR__ . '/../connection.php';

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

// Admin-Zugriff für das Bearbeitungsformular.
requireLogin('admin');

$TID    = isset($_GET['tid']) ? (int)$_GET['tid'] : 0;
$fehler = [];

// Lädt die Anmeldung und die zugehörigen Schülerdaten für das gewählte TID.
$stmt = $conn->prepare(
    "SELECT t.*, s.Vorname, s.Nachname, s.Klasse, s.Email
     FROM teilnahme t JOIN schueler s ON t.SID = s.SID
     WHERE t.TID = ?"
);
$stmt->execute([$TID]);
$anmeldung = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$anmeldung) {
    die('Datensatz nicht gefunden.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzer-Eingaben aus dem Formular einlesen und validieren.
    $Vorname   = trim($_POST['Vorname']   ?? '');
    $Nachname  = trim($_POST['Nachname']  ?? '');
    $Email     = trim($_POST['Email']     ?? '');
    $Klasse    = $_POST['Klasse']  ?? '';
    $AgName    = $_POST['AgName']  ?? '';
    $Genehmigt = isset($_POST['Genehmigt']) ? 1 : 0;

    if ($Vorname  === '') {
        $fehler[] = 'Vorname fehlt.';
    }
    if ($Nachname === '') {
        $fehler[] = 'Nachname fehlt.';
    }
    if ($Klasse   === '') {
        $fehler[] = 'Klasse fehlt.';
    }
    if ($AgName   === '') {
        $fehler[] = 'AG fehlt.';
    }

    if (empty($fehler)) {
        // Schüler-Daten aktualisieren und Auftrag in der Teilnahme speichern.
        $upd = $conn->prepare(
            "UPDATE schueler SET Vorname=?, Nachname=?, Email=?, Klasse=? WHERE SID=?"
        );
        $upd->execute([$Vorname, $Nachname, $Email, $Klasse, $anmeldung['SID']]);

        $upd2 = $conn->prepare("UPDATE teilnahme SET AgName=?, Genehmigt=? WHERE TID=?");
        $upd2->execute([$AgName, $Genehmigt, $TID]);

        header('Location: index.php');
        exit;
    }
}

// Auswahllisten für AGs und Klassen laden.
$ags     = $conn->query("SELECT Name FROM ag ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
$klassen = $conn->query("SELECT Klasse FROM klassen ORDER BY Klasse")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>Anmeldung bearbeiten</title></head>
<body>
<?php renderNav(); ?>

<h1>Anmeldung bearbeiten</h1>
<?php foreach ($fehler as $f): ?><p><strong><?= htmlspecialchars($f) ?></strong></p><?php endforeach; ?>

<form method="POST">
    <label>Vorname *<br>
        <input type="text" name="Vorname" value="<?= htmlspecialchars($_POST['Vorname'] ?? $anmeldung['Vorname']) ?>" required>
    </label><br><br>

    <label>Nachname *<br>
        <input type="text" name="Nachname" value="<?= htmlspecialchars($_POST['Nachname'] ?? $anmeldung['Nachname']) ?>" required>
    </label><br><br>

    <label>E-Mail<br>
        <input type="email" name="Email" value="<?= htmlspecialchars($_POST['Email'] ?? $anmeldung['Email']) ?>">
    </label><br><br>

    <label>Klasse *<br>
        <select name="Klasse" required>
            <?php foreach ($klassen as $k): ?>
                <option value="<?= htmlspecialchars($k['Klasse']) ?>"
                    <?= (($_POST['Klasse'] ?? $anmeldung['Klasse']) === $k['Klasse']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['Klasse']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>AG *<br>
        <select name="AgName" required>
            <?php foreach ($ags as $ag): ?>
                <option value="<?= htmlspecialchars($ag['Name']) ?>"
                    <?= (($_POST['AgName'] ?? $anmeldung['AgName']) === $ag['Name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ag['Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>
        <input type="checkbox" name="Genehmigt" value="1"
            <?= (($_POST['Genehmigt'] ?? $anmeldung['Genehmigt']) ? 'checked' : '') ?>>
        Anmeldung bestätigt
    </label><br><br>

    <button type="submit">Speichern</button>
    <a href="index.php">Abbrechen</a>
</form>
</body>
</html>
