<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Admin-Dashboard: verwaltet Anmeldungen, Lehrer-Rollen und neue AGs.
// Diese Seite ist nur für Admins zugänglich.
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

// Nur Admins dürfen dieses Dashboard aufrufen.
requireLogin('admin');

// Stellt sicher, dass die Lehrer-Tabelle ein Rollen-Feld enthält.
// Das Feld ermöglicht später, Lehrer und Schulleitung zu unterscheiden.
function ensureLehrerRoleColumn()
{
    global $conn;
    $stmt = $conn->query("SHOW COLUMNS FROM lehrer LIKE 'Rolle'");
    if (!$stmt->fetch())
    {
        $conn->exec("ALTER TABLE lehrer ADD COLUMN Rolle VARCHAR(32) NOT NULL DEFAULT 'lehrer'");
    }
}

function ensureSchulleitungTable()
{
    global $conn;
    // Legt bei Bedarf eine einfache Tabelle für Schulleitungs-Einträge an.
    // Diese Tabelle dient vor allem der Rollenzuordnung und Anzeige, nicht der Kern-AG-Verwaltung.
    $conn->exec(
        "CREATE TABLE IF NOT EXISTS schulleitung (
            Kuerzel VARCHAR(32) PRIMARY KEY,
            Bezeichnung VARCHAR(255) NOT NULL
        )"
    );
}

function syncSchulleitungEntry($kuerzel, $rolle)
{
    global $conn;

    // Synchronisiert die Schulleitungsliste in der eigenen Tabelle.
    // Bei Rolle 'schulleitung' wird ein Eintrag angelegt oder aktualisiert,
    // sonst wird der Eintrag entfernt.
    if ($rolle === 'schulleitung')
    {
        $stmt = $conn->prepare("SELECT Kuerzel FROM schulleitung WHERE Kuerzel = ?");
        $stmt->execute([$kuerzel]);

        if ($stmt->fetch()) {
            $stmt = $conn->prepare("UPDATE schulleitung SET Bezeichnung = ? WHERE Kuerzel = ?");
            $stmt->execute(['schulleitung', $kuerzel]);
        } else {
            $stmt = $conn->prepare("INSERT INTO schulleitung (Kuerzel, Bezeichnung) VALUES (?, ?)");
            $stmt->execute([$kuerzel, 'schulleitung']);
        }

        return;
    }

    $stmt = $conn->prepare("DELETE FROM schulleitung WHERE Kuerzel = ?");
    $stmt->execute([$kuerzel]);
}

ensureLehrerRoleColumn();
ensureSchulleitungTable();

$fehler = [];
$nachrichten = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Formularverarbeitung für verschiedene Admin-Aktionen.
    if ($action === 'add_teacher') {
        // Neuen Lehrer anlegen und optional als Schulleitung kennzeichnen.
        $kuerzel  = trim($_POST['Kuerzel'] ?? '');
        $vorname  = trim($_POST['Vorname'] ?? '');
        $nachname = trim($_POST['Nachname'] ?? '');
        $rolle     = $_POST['Rolle'] ?? 'lehrer';
        $allowedRoles = ['lehrer', 'schulleitung'];
        if (!in_array($rolle, $allowedRoles, true)) {
            $rolle = 'lehrer';
        }

        if ($kuerzel === '') {
            $fehler[] = 'Kürzel ist erforderlich.';
        }
        if ($vorname === '') {
            $fehler[] = 'Vorname ist erforderlich.';
        }
        if ($nachname === '') {
            $fehler[] = 'Nachname ist erforderlich.';
        }

        if (empty($fehler)) {
            $check = $conn->prepare("SELECT Kuerzel FROM lehrer WHERE Kuerzel = ?");
            $check->execute([$kuerzel]);
            if ($check->fetch()) {
                $fehler[] = 'Ein Lehrer mit diesem Kürzel existiert bereits.';
            } else {
                $stmt = $conn->prepare("INSERT INTO lehrer (Kuerzel, Vorname, Nachname, Rolle) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kuerzel, $vorname, $nachname, $rolle]);

                if ($rolle === 'schulleitung') {
                    syncSchulleitungEntry($kuerzel, $rolle);
                }

                $nachrichten[] = 'Lehrer wurde hinzugefügt.';
            }
        }
    } elseif ($action === 'update_teacher') {
        // Rolle eines bestehenden Lehrers ändern und ggf. die Schulleitungs-Tabelle synchronisieren.
        $kuerzel = trim($_POST['Kuerzel'] ?? '');
        $rolle   = $_POST['Rolle'] ?? 'lehrer';
        $allowedRoles = ['lehrer', 'schulleitung'];
        if (!in_array($rolle, $allowedRoles, true)) {
            $rolle = 'lehrer';
        }
        if ($kuerzel === '') {
            $fehler[] = 'Kürzel fehlt.';
        }
        if (empty($fehler)) {
            $teacherStmt = $conn->prepare("SELECT Vorname, Nachname FROM lehrer WHERE Kuerzel = ?");
            $teacherStmt->execute([$kuerzel]);
            $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);

            if (!$teacher) {
                $fehler[] = 'Lehrer nicht gefunden.';
            }
        }
        if (empty($fehler)) {
            $stmt = $conn->prepare("UPDATE lehrer SET Rolle = ? WHERE Kuerzel = ?");
            $stmt->execute([$rolle, $kuerzel]);

            syncSchulleitungEntry($kuerzel, $rolle);

            $nachrichten[] = 'Rolle wurde aktualisiert.';
        }
    } elseif ($action === 'add_ag') {
        // Neue AG anlegen mit Wochentagsauswahl und Leitung.
        $name     = trim($_POST['Name'] ?? '');
        $raum     = trim($_POST['Raum'] ?? '');
        $wochentag = $_POST['Wochentag'] ?? '';
        $leitung = $_POST['Leitung'] ?? '';
        $allowedDays = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        if (!in_array($wochentag, $allowedDays, true)) {
            $wochentag = '';
        }

        if ($name === '') {
            $fehler[] = 'AG-Name ist erforderlich.';
        }
        if ($raum === '') {
            $fehler[] = 'Raum ist erforderlich.';
        }
        if ($wochentag === '') {
            $fehler[] = 'Wochentag ist erforderlich.';
        }
        if ($leitung === '') {
            $fehler[] = 'Leitung ist erforderlich.';
        }

        if (empty($fehler)) {
            $check = $conn->prepare("SELECT Name FROM ag WHERE Name = ?");
            $check->execute([$name]);
            if ($check->fetch()) {
                $fehler[] = 'Eine AG mit diesem Namen existiert bereits.';
            } else {
                $stmt = $conn->prepare("INSERT INTO ag (Name, Raum, Wochentag, Leitung, FindetStatt) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$name, $raum, $wochentag, $leitung]);
                $nachrichten[] = 'AG wurde hinzugefügt.';
            }
        }
    }
}

// Löschen
// Entfernt eine Anmeldung vollständig aus der Datenbank.
if (isset($_GET['loeschen'])) {
    $TID = (int)$_GET['loeschen'];
    $stmt = $conn->prepare("DELETE FROM teilnahme WHERE TID = ?");
    $stmt->execute([$TID]);
    header('Location: index.php');
    exit;
}

// Genehmigung umschalten
// Setzt eine Anmeldung auf 'bestätigt', sobald sie vom Admin freigegeben wurde.
if (isset($_GET['genehmigen'])) {
    $TID = (int)$_GET['genehmigen'];
    $stmt = $conn->prepare("UPDATE teilnahme SET Genehmigt = 1 WHERE TID = ?");
    $stmt->execute([$TID]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>Admin-Dashboard</title></head>
<body>
<?php renderNav(); ?>

<h1>Admin-Dashboard</h1>

<h2>Alle Anmeldungen</h2>
<!-- Tabelle zeigt alle Teilnehmer und den Genehmigungsstatus -->
<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>TID</th><th>Vorname</th><th>Nachname</th><th>Klasse</th>
        <th>E-Mail</th><th>AG</th><th>Status</th><th>Aktionen</th>
    </tr>
    <?php
    $stmt = $conn->query(
        "SELECT t.TID, s.Vorname, s.Nachname, s.Klasse, s.Email,
                t.AgName, t.Genehmigt
         FROM teilnahme t
         JOIN schueler s ON t.SID = s.SID
         ORDER BY t.TID DESC"
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
    ?>
    <tr>
        <td><?= $row['TID'] ?></td>
        <td><?= htmlspecialchars($row['Vorname']) ?></td>
        <td><?= htmlspecialchars($row['Nachname']) ?></td>
        <td><?= htmlspecialchars($row['Klasse']) ?></td>
        <td><?= htmlspecialchars($row['Email']) ?></td>
        <td><?= htmlspecialchars($row['AgName']) ?></td>
        <td><?= $row['Genehmigt'] ? 'bestätigt' : 'offen' ?></td>
        <td>
            <a href="bearbeiten.php?tid=<?= $row['TID'] ?>">Bearbeiten</a> |
            <?php if (!$row['Genehmigt']): ?>
                <a href="index.php?genehmigen=<?= $row['TID'] ?>">Bestätigen</a> |
            <?php endif; ?>
            <a href="index.php?loeschen=<?= $row['TID'] ?>"
               onclick="return confirm('Anmeldung löschen?')">Löschen</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<h2>AG-Übersicht</h2>
<!-- Zeigt eine komprimierte Übersicht aller AGs mit Teilnahmezahlen und Status -->
<table border="1" cellpadding="6" cellspacing="0">
    <tr><th>Name</th><th>Leitung</th><th>Raum</th><th>Wochentag</th><th>Teilnehmer</th><th>Status</th></tr>
    <?php
    $ags = $conn->query(
        "SELECT ag.Name, ag.Raum, ag.Wochentag, ag.FindetStatt,
                lehrer.Vorname, lehrer.Nachname,
                COUNT(t.TID) AS Anzahl
         FROM ag
         LEFT JOIN lehrer ON ag.Leitung = lehrer.Kuerzel
         LEFT JOIN teilnahme t ON ag.Name = t.AgName
         GROUP BY ag.Name
         ORDER BY ag.Name"
    );
    while ($ag = $ags->fetch(PDO::FETCH_ASSOC)):
    ?>
    <tr>
        <td><?= htmlspecialchars($ag['Name']) ?></td>
        <td><?= htmlspecialchars($ag['Vorname'] . ' ' . $ag['Nachname']) ?></td>
        <td><?= htmlspecialchars($ag['Raum']) ?></td>
        <td><?= htmlspecialchars($ag['Wochentag']) ?></td>
        <td><?= $ag['Anzahl'] ?></td>
        <td><?= $ag['FindetStatt'] ? 'findet statt' : 'findet nicht statt' ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php
// Daten für Lehrer- und AG-Verwaltung laden.
$teachers = $conn->query("SELECT Kuerzel, Vorname, Nachname, Rolle FROM lehrer ORDER BY Nachname, Vorname")->fetchAll(PDO::FETCH_ASSOC);
$weekdays = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
?>

<h2>Lehrer verwalten</h2>
<!-- Verwaltung von Lehrkräften und der Rollen-Zuordnung (Lehrer / Schulleitung) -->
<?php if ($fehler): ?>
    <div style="color:red;"><ul>
    <?php foreach ($fehler as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>
<?php if ($nachrichten): ?>
    <div style="color:green;"><ul>
    <?php foreach ($nachrichten as $m): ?><li><?= htmlspecialchars($m) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="action" value="update_teacher">
    <label>Lehrer *<br>
        <select name="Kuerzel" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($teachers as $teacher): ?>
                <option value="<?= htmlspecialchars($teacher['Kuerzel']) ?>">
                    <?= htmlspecialchars($teacher['Vorname'] . ' ' . $teacher['Nachname'] . ' (' . $teacher['Kuerzel'] . ') - ' . $teacher['Rolle']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Rolle *<br>
        <select name="Rolle" required>
            <option value="lehrer">lehrer</option>
            <option value="schulleitung">schulleitung</option>
        </select>
    </label><br><br>

    <button type="submit">Rolle ändern</button>
</form>

<h3>Neuen Lehrer hinzufügen</h3>
<form method="POST">
    <input type="hidden" name="action" value="add_teacher">
    <label>Kürzel *<br><input type="text" name="Kuerzel" required></label><br><br>
    <label>Vorname *<br><input type="text" name="Vorname" required></label><br><br>
    <label>Nachname *<br><input type="text" name="Nachname" required></label><br><br>
    <label>Rolle *<br>
        <select name="Rolle" required>
            <option value="lehrer">lehrer</option>
            <option value="schulleitung">schulleitung</option>
        </select>
    </label><br><br>
    <button type="submit">Lehrer hinzufügen</button>
</form>

<h2>Neue AG hinzufügen</h2>
<form method="POST">
    <input type="hidden" name="action" value="add_ag">
    <label>Name *<br><input type="text" name="Name" required></label><br><br>
    <label>Raum *<br><input type="text" name="Raum" required></label><br><br>
    <label>Wochentag *<br>
        <select name="Wochentag" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($weekdays as $day): ?>
                <option value="<?= htmlspecialchars($day) ?>"><?= htmlspecialchars($day) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Leitung *<br>
        <select name="Leitung" required>
            <option value="">-- Bitte wählen --</option>
            <?php foreach ($teachers as $teacher): ?>
                <option value="<?= htmlspecialchars($teacher['Kuerzel']) ?>">
                    <?= htmlspecialchars($teacher['Vorname'] . ' ' . $teacher['Nachname'] . ' (' . $teacher['Kuerzel'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <button type="submit">AG hinzufügen</button>
</form>

</body>
</html>
