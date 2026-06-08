<?php
// Impressum-Seite: einfache, statische Informationen.
// Die Session wird gestartet, damit die Navigationsleiste die aktuelle Rolle kennt.
session_start();
// Diese statische Seite enthält das Impressum und zeigt dieselbe Navigation wie die anderen Seiten.
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
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"><link rel="stylesheet" href="/ag_verwaltung/stylesheet.css"><title>Impressum</title></head>
<body>
<?php renderNav(); ?>
<h1>Impressum</h1>
            <p>Anbieter:</p>
            <p>Akshat Venugopal<br/>Auerbacher Weg 24,<br/>64625 Bensheim</p>
            <p>Kontakt
            <p>Telefax: +49 491 786254650<br/>E-Mail: akshat.venugopal@ggb.kbs.schule<br/>Website: <a href="index.php">http://localhost/ag_verwaltung/index.php</a></p>
            <p>Bei redaktionellen Inhalten</p>
            <p>Verantwortlich nach § 55 Abs.2 RStV<br/>Moritz Schreiberling<br/>Musterstraße 2<br/>80999 München</p>
        </div>
<p><a href="/ag_verwaltung/index.php">Zurück zur Startseite</a></p>
</body>
</html>
