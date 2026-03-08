<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Socio Cercatore') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso Negato ⛔</h2><p>Solo i Soci Cercatori possono inserire nuove raccolte.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$id_socio = $_SESSION['id_user'];
$messaggio = "";

$query_specie = "SELECT Id_Specie, Nome_Scientifico, Nome_Volgare FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'Approvata' ORDER BY Nome_Scientifico ASC";
$result_specie = $conn->query($query_specie);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_raccolta = $_POST['data_raccolta'];
    $luogo = trim($_POST['luogo']);
    $peso = floatval($_POST['peso']);
    $id_specie = intval($_POST['id_specie']);
    

    $target_dir = "uploads/raccolte/";

    $estensione = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
    $nuovo_nome_file = uniqid() . "_" . basename($_FILES["foto"]["name"]);
    $target_file = $target_dir . $nuovo_nome_file;
    
    $uploadOk = 1;
    
    $check = getimagesize($_FILES["foto"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $messaggio = "<div class='msg msg-error'>Il file inviato non è un'immagine valida.</div>";
        $uploadOk = 0;
    }
    
    if($estensione != "jpg" && $estensione != "png" && $estensione != "jpeg") {
        $messaggio = "<div class='msg msg-error'>Spiacenti, sono permessi solo file JPG, JPEG e PNG.</div>";
        $uploadOk = 0;
    }
    
    if ($uploadOk == 1) {
    
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            
           
            $stato = 'In attesa';
            
            $stmt = $conn->prepare("INSERT INTO RACCOLTA (Data_Raccolta, Luogo, Quantita_Peso, URL_Foto, Stato_Lavorazione, Id_User_Socio, Id_Specie_Presunta) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssii", $data_raccolta, $luogo, $peso, $target_file, $stato, $id_socio, $id_specie);
            
            if ($stmt->execute()) {
                $messaggio = "<div class='msg msg-success'>Raccolta e foto registrate con successo! È ora in attesa di certificazione da parte di un Micologo.</div>";
            } else {
                $messaggio = "<div class='msg msg-error'>Errore durante il salvataggio nel database.</div>";
            }
        } else {
            $messaggio = "<div class='msg msg-error'>Si è verificato un errore tecnico durante il caricamento dell'immagine. Verifica che la cartella 'uploads/raccolte' esista.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserisci Raccolta - Associazione Micologica</title>
    <style>
       
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            padding: 20px; 
            color: #1a2721; 
        }
        
        .container { max-width: 750px; margin: 0 auto; }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 4px solid #cb8b22; 
            padding-bottom: 15px; 
            margin-bottom: 30px; 
        }
        
        .header h2 { 
            font-family: 'Georgia', serif; 
            color: #1a2721; 
            margin: 0; 
            font-size: 1.8em;
        }
        
        .btn-back { 
            background-color: #b91c1c; 
            color: white; 
            padding: 8px 15px; 
            text-decoration: none; 
            border-radius: 4px; 
            border: 2px solid #7f1d1d; 
            font-weight: bold; 
            transition: 0.2s; 
        }
        .btn-back:hover { background-color: #991b1b; }

        .main-box { 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 4px; 
            border: 3px solid #1a2721; 
        }
        
        p.intro-text { color: #1a2721; font-weight: bold; margin-bottom: 25px; }

        .form-group { margin-bottom: 20px; }
        label { font-weight: 900; display: block; margin-bottom: 8px; color: #1a2721; text-transform: uppercase; font-size: 0.85em; }
        
        input[type="text"], input[type="date"], input[type="number"], select, input[type="file"] { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #1a2721; 
            border-radius: 0; 
            box-sizing: border-box; 
            font-weight: bold;
            background-color: #ffffff;
        }

        .photo-upload {
            background-color: #f4efe6; 
            padding: 15px; 
            border: 2px solid #1a2721; 
            margin-bottom: 20px;
        }
        
        button[type="submit"] { 
            width: 100%; 
            padding: 15px; 
            background-color: #27ae60; 
            color: white; 
            border: 2px solid #1a2721; 
            border-radius: 0; 
            font-size: 1.1em; 
            font-weight: 900; 
            text-transform: uppercase;
            cursor: pointer; 
            transition: 0.2s; 
        }
        button[type="submit"]:hover { background-color: #219653; }
        
        .msg { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 2px solid #1a2721; }
        .msg-success { background-color: #4ade80; }
        .msg-error { background-color: #f87171; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Registra Ritrovamento</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <div class="main-box">
            <p class="intro-text">Compila il form con i dettagli del tuo ritrovamento. La documentazione fotografica è obbligatoria per permettere al Micologo di emettere il certificato.</p>

            <?php echo $messaggio; ?>

            <form action="inserisci_raccolta.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Data di raccolta:</label>
                    <input type="date" name="data_raccolta" required max="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Luogo del ritrovamento (Es. Bosco della Sila):</label>
                    <input type="text" name="luogo" placeholder="Inserisci il luogo..." required>
                </div>

                <div class="form-group">
                    <label>Quantità/Peso (in Kg):</label>
                    <input type="number" name="peso" step="0.01" min="0.01" placeholder="Es. 1.50" required>
                </div>

                <div class="form-group">
                    <label>Che fungo pensi di aver trovato? (Specie Presunta):</label>
                    <select name="id_specie" required>
                        <option value="">-- Seleziona una specie dal catalogo --</option>
                        <?php while($specie = $result_specie->fetch_assoc()): ?>
                            <option value="<?php echo $specie['Id_Specie']; ?>">
                                <?php echo htmlspecialchars($specie['Nome_Scientifico'] . " (" . $specie['Nome_Volgare'] . ")"); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="photo-upload">
                    <label>📸 Carica la foto del fungo (JPG, PNG):</label>
                    <input type="file" name="foto" accept=".jpg, .jpeg, .png" required>
                </div>

                <button type="submit">Salva e invia al Micologo</button>
            </form>
        </div>
    </div>

</body>
</html>