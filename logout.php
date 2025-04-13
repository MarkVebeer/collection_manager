<?php
session_start();

// Töröljük az összes session változót
$_SESSION = array();

// Töröljük a session cookie-t
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Megszüntetjük a session-t
session_destroy();

// Átirányítás a főoldalra
header("location: index.php");
exit; 