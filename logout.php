<?php
// Beendet die aktuelle Session und leitet sicher zurück zur Startseite.
// Dadurch werden alle Sitzungsdaten entfernt und der User ausgeloggt.
session_start();
session_destroy();
header('Location: /ag_verwaltung/index.php');
exit;
?>
