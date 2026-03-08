<?php
session_start();
require_once 'config/database.php';

$messaggio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password_chiara = $_POST['password']; 
$password = password_hash($password_chiara, PASSWORD_DEFAULT); 
    
    $stmt_check = $conn->prepare("SELECT Id_User FROM UTENTE WHERE Email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $messaggio = "<div class='error'>❌ Questa email è già registrata. Usa il Login.</div>";
    } else {
        $ruolo_base = 'Utente Esterno';
        
        $stmt_insert = $conn->prepare("INSERT INTO UTENTE (Nome, Cognome, Email, Password, Ruolo) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $nome, $cognome, $email, $password, $ruolo_base);
        
        if ($stmt_insert->execute()) {
            $messaggio = "<div class='success'>Registrazione completata con successo!<br><br><a href='login.php' style='color:#15803d; font-weight:bold;'>Clicca qui per accedere</a></div>";
        } else {
            $messaggio = "<div class='error'>Errore tecnico durante la registrazione. Riprova.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione - Associazione Micologica</title>
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
        
        .register-box { 
            background: #f4efe6; 
            padding: 45px 40px; 
            border-radius: 12px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            width: 100%; 
            max-width: 450px; 
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
        
        .register-box h2 { 
            text-align: center; 
            color: #1a2721; 
            margin-top: 0; 
            margin-bottom: 25px; 
            font-size: 1.5em;
            letter-spacing: -0.5px;
        }
        
        input { 
            width: 100%; 
            padding: 12px 14px; 
            margin-bottom: 15px; 
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
            margin-top: 10px;
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
            color: #b91c1c; background: #fef2f2; border: 1px solid #f87171;
            padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; 
            font-size: 0.9em; font-weight: 600; 
        }
        
        .success {
            color: #15803d; background: #f0fdf4; border: 1px solid #4ade80;
            padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; 
            font-size: 0.9em; font-weight: 600; 
        }

        .login-link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 0.95em; 
            color: #6b7280; 
        }
        
        .login-link a { 
            color: #1a2721; 
            text-decoration: none; 
            font-weight: 700; 
            display: inline-block; 
            margin-top: 5px; 
            transition: color 0.2s;
        }
        
        .login-link a:hover { 
            color: #cb8b22; 
        }
    </style>
</head>
<body>
    <div class="register-box">
        <div class="logo-text">Associazione Micologica</div>
        <h2>Crea un Account</h2>
        
        <?php echo $messaggio; ?>
        
        <?php if(!strpos($messaggio, 'successo')): ?>
            <form method="POST" action="registrazione.php">
                <input type="text" name="nome" placeholder="Il tuo Nome" required>
                <input type="text" name="cognome" placeholder="Il tuo Cognome" required>
                <input type="email" name="email" placeholder="Indirizzo Email" required>
                <input type="password" name="password" placeholder="Scegli una Password" required>
                <button type="submit">Crea Account</button>
            </form>
        <?php endif; ?>

        <div class="login-link">
            Hai già un account?<br>
            <a href="login.php">Accedi qui</a>
        </div>
    </div>
</body>
</html>