<?php
// Prevent any output before our JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

function jsonError($message) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $message]);
    exit
}

//change these for your server
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'collection_db');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($conn === false){
    jsonError("Hiba: Nem sikerült csatlakozni az adatbázishoz. " . mysqli_connect_error());
}
// Set character set to utf8mb4

if (!mysqli_set_charset($conn, "utf8mb4")) {
    jsonError("Hiba: Nem sikerült beállítani a karakterkódolást. " . mysqli_error($conn));
}

// Set timezone
date_default_timezone_set('Europe/Budapest');
?> 