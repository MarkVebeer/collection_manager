<?php
session_start();

// Ha már be van jelentkezve, átirányítjuk a főoldalra
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Felhasználónév ellenőrzése
    if(empty(trim($_POST["username"]))){
        $username_err = "Kérlek add meg a felhasználóneved.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Jelszó ellenőrzése
    if(empty(trim($_POST["password"]))){
        $password_err = "Kérlek add meg a jelszavad.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Hitelesítés
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            
                            header("location: index.php");
                        } else{
                            $login_err = "Hibás felhasználónév vagy jelszó.";
                        }
                    }
                } else{
                    $login_err = "Hibás felhasználónév vagy jelszó.";
                }
            } else{
                $login_err = "Hiba történt. Kérlek próbáld újra később.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>
 
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - Pringles Gyűjtemény</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">Bejelentkezés</h2>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group mb-3">
                <label>Felhasználónév</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group mb-3">
                <label>Jelszó</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group mb-3">
                <button type="submit" class="btn btn-primary w-100">Bejelentkezés</button>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Vissza a főoldalra
                </a>
            </div>
        </form>
    </div>
</body>
</html> 