<?php
session_start();

require_once "config.php";

// Ellenőrizzük, hogy van-e már felhasználó
$sql = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['count'] > 0) {
    die("A regisztráció le van tiltva, mert már van regisztrált felhasználó.");
}

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Felhasználónév ellenőrzése
    if(empty(trim($_POST["username"]))){
        $username_err = "Kérlek add meg a felhasználóneved.";
    } else{
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "Ez a felhasználónév már foglalt.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Hiba történt. Kérlek próbáld újra később.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Jelszó ellenőrzése
    if(empty(trim($_POST["password"]))){
        $password_err = "Kérlek add meg a jelszavad.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "A jelszónak legalább 6 karakterből kell állnia.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Jelszó megerősítése
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Kérlek erősítsd meg a jelszavad.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "A jelszavak nem egyeznek.";
        }
    }
    
    // Adatok mentése
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
                echo "Hiba történt. Kérlek próbáld újra később.";
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
    <title>Regisztráció - Pringles Gyűjtemény</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
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
    <div class="register-container">
        <h2 class="text-center mb-4">Admin Regisztráció</h2>
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
                <label>Jelszó megerősítése</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group mb-3">
                <button type="submit" class="btn btn-primary w-100">Regisztráció</button>
            </div>
            <p class="text-center">Már van fiókod? <a href="login.php">Jelentkezz be itt</a></p>
        </form>
    </div>
</body>
</html> 