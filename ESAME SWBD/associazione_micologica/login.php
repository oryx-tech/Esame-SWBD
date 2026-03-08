<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['loggato']) && $_SESSION['loggato'] === true) {
    header("Location: index.php");
    exit;
}

$errore = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password_inserita = $_POST['password'];

    $stmt = $conn->prepare("SELECT Id_User, Nome, Cognome, Ruolo, Password FROM UTENTE WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hash_salvato_nel_db = $row['Password'];
        
        if ($hash_salvato_nel_db === '***ACCOUNT_ELIMINATO***') {
            $errore = "Il tuo account è stato chiuso o disabilitato dall'Amministratore.";
        } else {
            if (password_verify($password_inserita, $hash_salvato_nel_db)) {
                $_SESSION['loggato'] = true;
                $_SESSION['id_user'] = $row['Id_User'];
                $_SESSION['nome'] = $row['Nome'];
                $_SESSION['cognome'] = $row['Cognome'];
                $_SESSION['ruolo'] = $row['Ruolo'];
                
                header("Location: index.php");
                exit;
            } else {
                $errore = "Password errata. Riprova.";
            }
        }
    } else {
        $errore = "Nessun account trovato con questa email.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - Associazione Micologica</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a2721; 
            background-image: radial-gradient(circle at top right, #243b30, #111a16);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        
       .login-box { 
            background: #f4efe6; 
            padding: 45px 40px; 
            border-radius: 12px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            width: 100%; 
            max-width: 400px; 
            box-sizing: border-box;
            border: 1px solid #e3dccf; 
        }

        
        .logo-text {
            text-align: center;
            color: #cb8b22; 
            font-size: 1.1em;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 5px;
        }
        
        .login-box h2 { 
            text-align: center; 
            color: #1a2721; 
            margin-top: 0; 
            margin-bottom: 30px; 
            font-size: 1.5em;
            letter-spacing: -0.5px;
        }
        
        input { 
            width: 100%; 
            padding: 14px; 
            margin-bottom: 20px; 
            border: 1px solid #d1d5db; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-size: 1em; 
            background-color: #f9fafb; 
            transition: all 0.3s ease; 
        }
        
        
        input:focus {
            outline: none;
            border-color: #cb8b22; 
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(203, 139, 34, 0.15);
        }
        
        button { 
            width: 100%; 
            padding: 14px; 
            background: #cb8b22; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 1.05em; 
            font-weight: bold; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease; 
        }
        
        button:hover { 
            background: #b3791d; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 10px rgba(203, 139, 34, 0.3);
        }
        
        .error { 
            color: #b91c1c; 
            background: #fef2f2; 
            border: 1px solid #f87171;
            padding: 12px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 0.9em; 
            font-weight: 600; 
        }
        
        .register-link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 0.95em; 
            color: #6b7280; 
        }
        
        .register-link a { 
            color: #1a2721; 
            text-decoration: none; 
            font-weight: 700; 
            display: inline-block; 
            margin-top: 5px; 
            transition: color 0.2s;
        }
        
        .register-link a:hover { 
            color: #cb8b22; 
        }
    </style>
</head>
<body>

    <div class="login-box">
        <div class="logo-text"> Associazione Micologica</div>
        <h2>Accesso Portale</h2>
        
        <?php if($errore): ?>
            <div class="error"><?php echo $errore; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Indirizzo Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Entra nel sistema</button>
        </form>

        <div class="register-link">
            Non hai ancora un account? <br>
            <a href="registrazione.php">Registrati gratis</a>
        </div>

    </div>

</body>
</html>