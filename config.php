<?php
// Prevent any output before our JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Log database connection attempt
error_log("Attempting database connection...");

// Database credentials - CHANGE THESE FOR YOUR SERVER
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'pringles_collection');

try {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if($conn === false){
        error_log("Database connection failed: " . mysqli_connect_error());
        throw new Exception("Hiba: Nem sikerült csatlakozni az adatbázishoz. " . mysqli_connect_error());
    }

    // Set character set to utf8mb4
    if (!mysqli_set_charset($conn, "utf8mb4")) {
        error_log("Character set error: " . mysqli_error($conn));
        throw new Exception("Hiba: Nem sikerült beállítani a karakterkódolást. " . mysqli_error($conn));
    }

    // Log successful connection
    error_log("Database connection successful");

    // Set timezone
    date_default_timezone_set('Europe/Budapest');

} catch (Exception $e) {
    if (strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false) {
        // If called from API, return JSON error
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    } else {
        // If called from web page, show error message
        die($e->getMessage());
    }
}
?> 