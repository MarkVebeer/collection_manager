<?php
require_once "../config.php";

header('Content-Type: application/json');

if (!isset($_GET['barcode'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Hiányzó vonalkód!'
    ]);
    exit;
}

$barcode = $_GET['barcode'];

// Termék törlése
$stmt = mysqli_prepare($conn, "DELETE FROM pringles WHERE barcode = ?");
mysqli_stmt_bind_param($stmt, "s", $barcode);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Termék sikeresen törölve!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'A termék nem található!'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Hiba történt a törlés során: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn); 