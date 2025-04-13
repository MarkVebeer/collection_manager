<?php
session_start();
require_once "config.php";

// Keresési feltétel ellenőrzése
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM collection";
if (!empty($search)) {
    $sql .= " WHERE barcode LIKE ? OR name LIKE ?";
}
$sql .= " ORDER BY added_date DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($search)) {
    $searchParam = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $searchParam, $searchParam);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gyűjtemény</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .modal-img {
            max-width: 100%;
            height: auto;
        }
        .product-preview {
            max-width: 200px;
            margin: 0 auto;
        }
        .input-group {
            max-width: 500px;
            margin: 0 auto;
        }
        .alert {
            max-width: 500px;
            margin: 20px auto;
        }
        .card-img-container {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .card-img-top {
            max-width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }
        .card-img-top:hover {
            transform: scale(1.05);
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .card-text {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .search-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .login-status {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if($isLoggedIn): ?>
        <div class="login-status">
            <span class="me-2">Üdv, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</span>
            <a href="logout.php" class="btn btn-danger btn-sm">Kijelentkezés</a>
        </div>
        <?php else: ?>
        <div class="login-status">
            <a href="login.php" class="btn btn-primary btn-sm">Bejelentkezés</a>
        </div>
        <?php endif; ?>

        <h1 class="text-center mb-4">Gyűjtemény</h1>
        
        <!-- Keresés és Vonalkód beolvasás -->
        <div class="search-container">
            <div class="row g-3">
                <!-- Keresés -->
                <div class="col-md-6">
                    <h5 class="mb-3">Keresés</h5>
                    <form action="" method="GET" class="d-flex">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Keresés név vagy vonalkód alapján..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   id="searchInput">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Keresés
                            </button>
                            <button type="button" class="btn btn-primary" onclick="startSearchScanning()">
                                <i class="fas fa-barcode"></i> Szkennelés
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="?" class="btn btn-secondary">Összes</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <!-- Vonalkód beolvasás -->
                <?php if($isLoggedIn): ?>
                <div class="col-md-6">
                    <h5 class="mb-3">Új termék hozzáadása</h5>
                    <div class="input-group">
                        <input type="text" id="barcodeInput" class="form-control" placeholder="Vonalkód">
                        <button class="btn btn-primary" onclick="checkBarcode()">
                            <i class="fas fa-plus"></i> Hozzáadás
                        </button>
                        <button class="btn btn-primary" onclick="startScanning()">
                            <i class="fas fa-barcode"></i> Szkennelés
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Találatok száma -->
        <?php
        $count = mysqli_num_rows($result);
        if (!empty($search)):
        ?>
        <div class="alert alert-info">
            <?php echo $count; ?> találat a következőre: "<?php echo htmlspecialchars($search); ?>"
        </div>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-img-container">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>"
                                 style="max-height: 200px; object-fit: contain; padding: 10px;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><small class="text-muted">Vonalkód: <?php echo htmlspecialchars($row['barcode']); ?></small></p>
                            <?php if($isLoggedIn): ?>
                            <button class="btn btn-danger btn-sm" 
                                    onclick="confirmDelete('<?php echo htmlspecialchars($row['barcode']); ?>', '<?php echo htmlspecialchars($row['name']); ?>')">
                                <i class="fas fa-trash"></i> Törlés
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($count == 0): ?>
        <div class="alert alert-warning">
            Nincs találat.
        </div>
        <?php endif; ?>
    </div>

    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vonalkód beolvasása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="searchReader"></div>
                    <div class="mt-3" id="lastScannedContainer" style="display: none;">
                        <div class="alert alert-success">
                            Beolvasott kód: <strong id="lastScannedCode"></strong>
                        </div>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-primary" onclick="searchLastScanned()">Keresés</button>
                            <button class="btn btn-secondary" onclick="continueScanning()">Új beolvasás</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kép nagyítás modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="" class="modal-img" id="modalImage">
                </div>
            </div>
        </div>
    </div>

    <!-- Termék hozzáadás jóváhagyó modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Termék hozzáadása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" class="product-preview mb-3" id="previewImage">
                    <h4 id="previewName"></h4>
                    <p class="text-muted">Biztosan hozzá szeretnéd adni ezt a terméket?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" class="btn btn-primary" id="confirmAdd">Hozzáadás</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Modal for Adding Products -->
    <div class="modal fade" id="addProductScannerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vonalkód beolvasása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="addProductReader"></div>
                    <div class="mt-3" id="addProductLastScannedContainer" style="display: none;">
                        <div class="alert alert-success">
                            Beolvasott kód: <strong id="addProductLastScannedCode"></strong>
                        </div>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-primary" onclick="checkLastScannedBarcode()">Hozzáadás</button>
                            <button class="btn btn-secondary" onclick="continueAddProductScanning()">Új beolvasás</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Törlés megerősítő modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Termék törlése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Biztosan törölni szeretnéd ezt a terméket?</p>
                    <p class="fw-bold" id="deleteProductName"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Törlés</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let currentBarcode = null;
        let html5QrcodeScanner = null;
        let searchScanner = null;
        let lastScannedBarcode = null;
        let addProductScanner = null;
        let lastScannedAddProductBarcode = null;
        let deleteBarcode = null;

        function startSearchScanning() {
            if (searchScanner) {
                searchScanner.clear();
            }

            const modal = new bootstrap.Modal(document.getElementById('scannerModal'));
            modal.show();

            // Reset the last scanned container
            document.getElementById('lastScannedContainer').style.display = 'none';
            document.getElementById('searchReader').style.display = 'block';

            searchScanner = new Html5QrcodeScanner(
                "searchReader", 
                { 
                    fps: 10, 
                    qrbox: 250,
                    aspectRatio: 1.0,
                    formatsToSupport: [ Html5QrcodeSupportedFormats.EAN_13 ]
                }
            );
            
            searchScanner.render((decodedText, decodedResult) => {
                if (searchScanner) {
                    searchScanner.pause();
                }
                lastScannedBarcode = decodedText;
                document.getElementById('lastScannedCode').textContent = decodedText;
                document.getElementById('searchReader').style.display = 'none';
                document.getElementById('lastScannedContainer').style.display = 'block';
            }, (error) => {
                // handle scan error
            });
        }

        function searchLastScanned() {
            if (lastScannedBarcode) {
                document.getElementById('searchInput').value = lastScannedBarcode;
                const modal = bootstrap.Modal.getInstance(document.getElementById('scannerModal'));
                modal.hide();
                document.querySelector('form').submit();
            }
        }

        function continueScanning() {
            if (searchScanner) {
                document.getElementById('lastScannedContainer').style.display = 'none';
                document.getElementById('searchReader').style.display = 'block';
                searchScanner.resume();
            }
        }

        // When scanner modal is hidden, clear the scanner
        document.getElementById('scannerModal').addEventListener('hidden.bs.modal', function () {
            if (searchScanner) {
                searchScanner.clear();
            }
        });

        function startScanning() {
            if (addProductScanner) {
                addProductScanner.clear();
            }

            const modal = new bootstrap.Modal(document.getElementById('addProductScannerModal'));
            modal.show();

            // Reset the last scanned container
            document.getElementById('addProductLastScannedContainer').style.display = 'none';
            document.getElementById('addProductReader').style.display = 'block';

            addProductScanner = new Html5QrcodeScanner(
                "addProductReader", 
                { 
                    fps: 10, 
                    qrbox: 250,
                    aspectRatio: 1.0,
                    formatsToSupport: [ Html5QrcodeSupportedFormats.EAN_13 ]
                }
            );
            
            addProductScanner.render((decodedText, decodedResult) => {
                if (addProductScanner) {
                    addProductScanner.pause();
                }
                lastScannedAddProductBarcode = decodedText;
                document.getElementById('addProductLastScannedCode').textContent = decodedText;
                document.getElementById('addProductReader').style.display = 'none';
                document.getElementById('addProductLastScannedContainer').style.display = 'block';
            }, (error) => {
                // handle scan error
            });
        }

        function checkLastScannedBarcode() {
            if (lastScannedAddProductBarcode) {
                document.getElementById('barcodeInput').value = lastScannedAddProductBarcode;
                const modal = bootstrap.Modal.getInstance(document.getElementById('addProductScannerModal'));
                modal.hide();
                checkBarcodeAndShowPreview(lastScannedAddProductBarcode);
            }
        }

        function continueAddProductScanning() {
            if (addProductScanner) {
                document.getElementById('addProductLastScannedContainer').style.display = 'none';
                document.getElementById('addProductReader').style.display = 'block';
                addProductScanner.resume();
            }
        }

        // When scanner modal is hidden, clear the scanner
        document.getElementById('addProductScannerModal').addEventListener('hidden.bs.modal', function () {
            if (addProductScanner) {
                addProductScanner.clear();
            }
        });

        function checkBarcode() {
            const barcode = document.getElementById('barcodeInput').value;
            if (barcode) {
                checkBarcodeAndShowPreview(barcode);
            }
        }

        function checkBarcodeAndShowPreview(barcode) {
            <?php if(!$isLoggedIn): ?>
            window.location.href = 'login.php';
            return;
            <?php endif; ?>

            currentBarcode = barcode;
            fetch(`api/get_product.php?barcode=${barcode}&preview=true`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.exists) {
                            alert('Ez a termék már szerepel a gyűjteményben!');
                        } else {
                            document.getElementById('previewImage').src = data.product.image_url;
                            document.getElementById('previewName').textContent = data.product.name;
                            new bootstrap.Modal(document.getElementById('confirmModal')).show();
                        }
                    } else {
                        alert(data.message || 'Hiba történt a termék hozzáadásakor');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hiba történt a termék hozzáadásakor');
                });
        }

        document.getElementById('confirmAdd').addEventListener('click', function() {
            if(currentBarcode) {
                fetch(`api/get_product.php?barcode=${currentBarcode}&add=true`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Hiba történt a termék hozzáadásakor!');
                            console.error('API hiba:', data);
                        }
                    })
                    .catch(error => {
                        alert('Hiba történt a kérés során: ' + error.message);
                        console.error('Hálózati hiba:', error);
                    });
            }
        });

        function confirmDelete(barcode, name) {
            <?php if(!$isLoggedIn): ?>
            window.location.href = 'login.php';
            return;
            <?php endif; ?>

            deleteBarcode = barcode;
            document.getElementById('deleteProductName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if(deleteBarcode) {
                fetch(`api/delete_product.php?barcode=${deleteBarcode}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Hiba történt a termék törlésekor!');
                            console.error('API hiba:', data);
                        }
                    })
                    .catch(error => {
                        alert('Hiba történt a kérés során: ' + error.message);
                        console.error('Hálózati hiba:', error);
                    });
            }
        });
    </script>
</body>
</html>
