<?php

// Prevent any output before our JSON response

error_reporting(E_ALL);

ini_set('display_errors', 0);

ini_set('log_errors', 1);

ini_set('error_log', __DIR__ . '/error.log');



// Ensure we're sending JSON response

header('Content-Type: application/json; charset=utf-8');



// Set up error handler

set_error_handler(function($errno, $errstr, $errfile, $errline) {

    $error = [

        'success' => false,

        'message' => 'PHP Error',

        'debug' => [

            'error' => $errstr,

            'file' => $errfile,

            'line' => $errline

        ]

    ];

    echo json_encode($error);

    exit;

});



require_once "../config.php";



function sendError($message, $debug = null) {

    $response = ['success' => false, 'message' => $message];

    if ($debug !== null) {

        $response['debug'] = $debug;

    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;

}



// Check database connection

if (mysqli_connect_errno()) {

    sendError("Database connection failed: " . mysqli_connect_error());

}



if (!isset($_GET['barcode'])) {

    sendError('Hiányzó vonalkód');

}



$barcode = $_GET['barcode'];

$preview = isset($_GET['preview']) && $_GET['preview'] === 'true';

$add = isset($_GET['add']) && $_GET['add'] === 'true';



// Ellenőrizzük, hogy létezik-e már a termék

$check_sql = "SELECT * FROM collection WHERE barcode = ?";

$stmt = mysqli_prepare($conn, $check_sql);

if (!$stmt) {

    sendError('SQL prepare error: ' . mysqli_error($conn));

}

mysqli_stmt_bind_param($stmt, "s", $barcode);

if (!mysqli_stmt_execute($stmt)) {

    sendError('SQL execute error: ' . mysqli_stmt_error($stmt));

}

$result = mysqli_stmt_get_result($stmt);



if (mysqli_num_rows($result) > 0) {

    echo json_encode([

        'success' => true,

        'exists' => true,

        'message' => 'A termék már létezik a gyűjteményben'

    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;

}



// Weboldal tartalmának lekérése

$url = "https://world.openfoodfacts.org/product/" . $barcode;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$html = curl_exec($ch);



$curl_info = curl_getinfo($ch);

if (curl_errno($ch)) {

    sendError('Nem sikerült lekérni az adatokat', [

        'curl_error' => curl_error($ch),

        'curl_info' => $curl_info

    ]);

}



if ($curl_info['http_code'] !== 200) {

    sendError('A szerver nem megfelelő válaszkóddal tért vissza', [

        'http_code' => $curl_info['http_code'],

        'curl_info' => $curl_info

    ]);

}



if (empty($html)) {

    sendError('Üres válasz érkezett a szervertől', [

        'curl_info' => $curl_info

    ]);

}



curl_close($ch);



// HTML karakterkódolás javítása

$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1'));



// Kép URL kinyerése

preg_match('/<img[^>]+id="og_image"[^>]+src="([^"]+)"/', $html, $image_matches);

$image_url = isset($image_matches[1]) ? $image_matches[1] : 'https://via.placeholder.com/300?text=Pringles+' . $barcode;



// Termék nevének kinyerése - keressük a previewName ID-jú elemet

preg_match('/<h4[^>]*id="previewName"[^>]*>(.*?)<\/h4>/s', $html, $name_matches);



if (empty($name_matches[1])) {

    // Ha nem találtuk meg a previewName-et, próbáljuk az első h2-t

    preg_match('/<h2[^>]*>(.*?)(?=This product page)/s', $html, $name_matches);

}



$name = !empty($name_matches[1]) ? trim(strip_tags($name_matches[1])) : 'Pringles ' . $barcode;



// Név tisztítása

function cleanProductName($name) {

    // 1. HTML tagek eltávolítása

    $name = strip_tags($name);

    

    // 2. Minden whitespace egyetlen szóközre cserélése

    $name = preg_replace('/\s+/', ' ', $name);

    

    // 3. Speciális karakterek és felesleges szövegek eltávolítása

    $name = preg_replace('/^[?×x✕✖\s]+/', '', $name);  // Kérdőjel, x és hasonló karakterek a szöveg elejéről

    $name = preg_replace('/\s*\([^)]*\)/', '', $name);  // Zárójelben lévő részek

    $name = preg_replace('/\s*-\s*$/', '', $name);      // Kötőjel a végéről

    

    // 4. Többszörös szóközök eltávolítása

    $name = preg_replace('/\s+/', ' ', $name);

    

    // 5. Whitespace trimmelése

    $name = trim($name);

    

    // 6. Karakterkódolás javítása

    $name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');

    

    return $name;

}



$name = cleanProductName($name);



$product_data = [

    'barcode' => $barcode,

    'name' => $name,

    'image_url' => $image_url

];



if ($preview) {

    $response = [

        'success' => true,

        'exists' => false,

        'product' => $product_data

    ];

    

    $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json === false) {

        sendError('JSON kódolási hiba: ' . json_last_error_msg());

    }

    echo $json;

    exit;

}



if ($add) {

    $sql = "INSERT INTO collection (barcode, name, image_url) VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "sss", $barcode, $product_data['name'], $product_data['image_url']);



    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([

            'success' => true, 

            'message' => 'Termék sikeresen hozzáadva'

        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } else {

        sendError('Hiba történt a termék hozzáadásakor: ' . mysqli_error($conn));

    }

    exit;

}



sendError('Érvénytelen kérés');

?> 