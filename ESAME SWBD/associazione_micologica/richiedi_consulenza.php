<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Utente Premium') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso Negato</h2><p>Questo servizio è riservato agli Utenti Premium.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$id_premium = $_SESSION['id_user'];
$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_richiesta = date('Y-m-d');
    $colore_cappello = trim($_POST['colore_cappello']);
    $colore_fusto = trim($_POST['colore_fusto']);
    $habitat = trim($_POST['habitat']);
    
    $target_dir = "uploads/premium/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $estensione = strtolower(pathinfo($_FILES["foto_fungo"]["name"], PATHINFO_EXTENSION));
    $nuovo_nome_file = "premium_" . uniqid() . "." . $estensione;
    $target_file = $target_dir . $nuovo_nome_file;
    
    $uploadOk = 1;
    $check = getimagesize($_FILES["foto_fungo"]["tmp_name"]);
    if($check === false) { $messaggio = "<div class='msg msg-error'>Il file inviato non è un'immagine valida.</div>"; $uploadOk = 0; }
    if($estensione != "jpg" && $estensione != "png" && $estensione != "jpeg") { $messaggio = "<div class='msg msg-error'>Sono permessi solo file JPG, JPEG e PNG.</div>"; $uploadOk = 0; }
    
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["foto_fungo"]["tmp_name"], $target_file)) {
            
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO CONSULENZA_PRIVATA (Data_Richiesta, URL_Foto, Colore_Cappello, Colore_Fusto, Habitat_Alberi, Id_User_Premium) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $data_richiesta, $target_file, $colore_cappello, $colore_fusto, $habitat, $id_premium);
                $stmt->execute();
                
                $query_check = "SELECT Tipo_Servizio FROM PAGAMENTO WHERE Id_User_Cliente = ? ORDER BY Id_Pagamento DESC LIMIT 1";
                $stmt_check = $conn->prepare($query_check);
                $stmt_check->bind_param("i", $id_premium);
                $stmt_check->execute();
                $res_check = $stmt_check->get_result();
                
                $tipo_servizio = 'Sconosciuto';
                if($row_check = $res_check->fetch_assoc()) {
                    $tipo_servizio = $row_check['Tipo_Servizio'];
                }
                
                if ($tipo_servizio === 'Consulenza Singola') {
                    $stmt_down = $conn->prepare("UPDATE UTENTE SET Ruolo = 'Utente Esterno' WHERE Id_User = ?");
                    $stmt_down->bind_param("i", $id_premium);
                    $stmt_down->execute();
                    
                    $_SESSION['ruolo'] = 'Utente Esterno'; 
                    $messaggio = "<div class='msg msg-success'>Richiesta inviata con successo! <br>Hai utilizzato il tuo gettone per la <b>Consulenza Singola</b>. Il tuo account è tornato alla versione base.</div>";
                } else {
                    $messaggio = "<div class='msg msg-success'>Richiesta inviata con successo! <br>Grazie per aver usato il tuo <b>Abbonamento Annuale</b> illimitato.</div>";
                }
                
                $conn->commit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $messaggio = "<div class='msg msg-error'>Errore durante il salvataggio nel database.</div>";
            }
            
        } else {
            $messaggio = "<div class='msg msg-error'>Errore tecnico durante il caricamento dell'immagine.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Richiedi Consulenza Privata - Associazione Micologica</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            color: #1a2721; 
        }
        
        .header { 
            background-color: #1a2721; 
            padding: 20px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 4px solid #cb8b22; 
        }
        .header h2 { font-family: 'Georgia', serif; color: #f4efe6; margin: 0; }
        
        .btn-back { background-color: #b91c1c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; border: 2px solid #7f1d1d; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { background-color: #991b1b; }

        .container { 
            max-width: 750px; 
            margin: 40px auto; 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 4px; 
            border: 3px solid #1a2721; 
            border-top: 6px solid #cb8b22; 
        }
        
        .form-section { 
            background-color: #f9fafb; 
            padding: 20px; 
            border-radius: 4px; 
            border: 2px solid #1a2721; 
            margin-bottom: 25px; 
        }
        .form-section.foto-section { background-color: #f4efe6; border-color: #cb8b22; }
        
        .form-section h3 { margin-top: 0; color: #1a2721; font-family: 'Georgia', serif; border-bottom: 2px solid #e3dccf; padding-bottom: 10px; }
        
        .form-group { margin-bottom: 20px; }
        label { font-weight: 900; display: block; margin-bottom: 8px; color: #1a2721; text-transform: uppercase; font-size: 0.85em; }
        
        input[type="text"], input[type="file"] { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #1a2721; 
            border-radius: 0; /* Squadrato */
            box-sizing: border-box; 
            font-weight: bold;
            background-color: #ffffff;
            transition: 0.2s;
        }
        input[type="text"]:focus { outline: none; border-color: #cb8b22; }
        
        button[type="submit"] { 
            width: 100%; 
            padding: 15px; 
            background-color: #cb8b22; 
            color: white; 
            border: 2px solid transparent; 
            border-radius: 0; 
            font-size: 1.1em; 
            font-weight: 900; 
            text-transform: uppercase; 
            cursor: pointer; 
            transition: 0.2s; 
        }
        button[type="submit"]:hover { background-color: #b3791d; }
        
        .msg { padding: 15px; border-radius: 4px; margin-bottom: 25px; font-weight: bold; text-align: center; border: 2px solid #1a2721; }
        .msg-success { background-color: #4ade80; color: #1a2721; }
        .msg-error { background-color: #f87171; color: #1a2721; }
        
        p.intro-text { color: #1a2721; font-weight: bold; font-size: 1.05em; margin-bottom: 25px; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Consulenza Micologica Privata</h2>
        <?php if(isset($_SESSION['ruolo']) && $_SESSION['ruolo'] == 'Utente Esterno'): ?>
            <a href="index.php" class="btn-back">Torna alla Home</a>
        <?php else: ?>
            <a href="index.php" class="btn-back">Indietro</a>
        <?php endif; ?>
    </div>

    <div class="container">
        
        <?php echo $messaggio; ?>

        <?php if($_SESSION['ruolo'] === 'Utente Premium'): ?>
            <p class="intro-text">Compila il questionario diagnostico. Più dettagli fornisci al nostro esperto, più precisa e rapida sarà l'identificazione.</p>
            
            <form action="richiedi_consulenza.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-section">
                    <h3>1. Dettagli Visivi ed Ecologici</h3>
                    <div class="form-group">
                        <label>Di che colore è il cappello?</label>
                        <input type="text" name="colore_cappello" placeholder="Es. Marrone scuro, liscio..." required>
                    </div>
                    <div class="form-group">
                        <label>Com'è fatto il fusto?</label>
                        <input type="text" name="colore_fusto" placeholder="Es. Bianco, tozzo, con reticolo..." required>
                    </div>
                    <div class="form-group">
                        <label>Habitat e Alberi vicini:</label>
                        <input type="text" name="habitat" placeholder="Es. Trovato sotto un castagno, terreno umido..." required>
                    </div>
                </div>
                
                <div class="form-section foto-section">
                    <h3>2. Foto dell'esemplare</h3>
                    <div class="form-group">
                        <input type="file" name="foto_fungo" accept=".jpg, .jpeg, .png" required>
                    </div>
                </div>
                
                <button type="submit">Invia Richiesta all'Esperto</button>
            </form>
        <?php endif; ?>

    </div>

</body>
</html>