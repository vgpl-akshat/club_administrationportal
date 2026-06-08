<?php
// Session starten und DB-Verbindung einbinden.
session_start();
require_once __DIR__ . '/connection.php';

// Rendert das globale Navigationsmenü anhand der aktuellen Rolle.
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

// Funktion zur Absicherung geschützter Seiten nach Rolle.
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

// Login-Seite: authentifiziert Admin, Schulleitung oder Lehrer und setzt die Sitzung.
// Lehrer werden aus der Datenbank geladen, alle anderen Rollen verwenden statische Zugangsdaten.
$fehler = '';
$users = [
 'admin'=>['passwort'=>'admin123','rolle'=>'admin'],
 'schulleitung'=>['passwort'=>'Leitung123','rolle'=>'schulleitung']
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $passwort = $_POST['passwort'] ?? '';

    // Login für Admin und Schulleitung mit fest definierten Zugangsdaten.
    if (isset($users[$name]) && $users[$name]['passwort'] === $passwort) {
        $_SESSION['name'] = $name;
        $_SESSION['rolle'] = $users[$name]['rolle'];
        if ($_SESSION['rolle'] === 'admin') {
            header('Location: /ag_verwaltung/admin/admin.php');
        } else {
            header('Location: /ag_verwaltung/schulleitung.php');
        }
        exit;
    }

    // Lehrer-Login: Suche Lehrer in der DB und weise die Rolle aus der Tabelle zu.
    // Das erlaubt es, Lehrkräfte als normale Lehrer oder als Schulleitung zu verwenden.
    $stmt = $conn->prepare(
        "SELECT Kuerzel, Vorname, Nachname, Rolle
         FROM lehrer
         WHERE Kuerzel = ?
            OR CONCAT(Vorname, ' ', Nachname) = ?"
    );
    $stmt->execute([$name, $name]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher && $passwort === 'lehrer123') {
        // Lehrer erfolgreich gefunden. Benutzersitzung befüllen und die Rolle aus der Datenbank verwenden.
        $_SESSION['name'] = $teacher['Vorname'] . ' ' . $teacher['Nachname'];
        $userRole = $teacher['Rolle'] ?? 'lehrer';
        if (!in_array($userRole, ['lehrer', 'schulleitung'], true)) {
            $userRole = 'lehrer';
        }
        $_SESSION['rolle'] = $userRole;
        $_SESSION['kuerzel'] = $teacher['Kuerzel'];
        if ($userRole === 'schulleitung') {
            header('Location: /ag_verwaltung/schulleitung.php');
        } else {
            header('Location: /ag_verwaltung/lehrer.php');
        }
        exit;
    }

    $fehler = 'Name oder Passwort falsch.';
}
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>Login – AG-Portal</title></head>
<body>
<?php renderNav(); ?>

<h1>Login</h1>
<?php if ($fehler): ?>
    <p><strong><?= htmlspecialchars($fehler) ?></strong></p>
<?php endif; ?>

<form method="POST" action="login.php">
    <label>Name / Kürzel<br>
        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autofocus>
    </label><br><br>

    <label>Passwort<br>
        <input type="password" name="passwort" required>
    </label><br><br>

    <button type="submit">Anmelden</button>
</form>
<br><a href="index.php">Zurück zur Startseite</a>
</body>
</html>
