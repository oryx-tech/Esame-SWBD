<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Micologo Esperto') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso Negato ⛔</h2><p>Solo i Micologi Esperti possono accedere a quest'area.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$id_micologo = $_SESSION['id_user'];
$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_raccolta = intval($_POST['id_raccolta']);
    $azione = $_POST['azione']; 
    $motivazione = trim($_POST['motivazione']);
    $data_emissione = date('Y-m-d');
    
    $conn->begin_transaction();

    try {
        $id_specie_reale = intval($_POST['id_specie_reale']);
        

        $nuovo_stato = ($azione === 'Certifica') ? 'Certificata' : 'Bocciata';
        $esito_testo = ($azione === 'Certifica') ? 'Idoneo / Riconosciuto' : 'Bocciata';
        
        $stmt_upd = $conn->prepare("UPDATE RACCOLTA SET Stato_Lavorazione = ? WHERE Id_Raccolta = ?");
        $stmt_upd->bind_param("si", $nuovo_stato, $id_raccolta);
        $stmt_upd->execute();
        
        $stmt_cert = $conn->prepare("INSERT INTO CERTIFICAZIONE (Data_Emissione, Esito, Motivazione_Note, Id_User_Micologo, Id_Specie_Reale, Id_Raccolta) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_cert->bind_param("ssssii", $data_emissione, $esito_testo, $motivazione, $id_micologo, $id_specie_reale, $id_raccolta);
        $stmt_cert->execute();
        
        if ($azione === 'Certifica') {
            $messaggio = "<div class='msg msg-success'>Raccolta Certificata con successo! Inserita nell'archivio pubblico.</div>";
        } else {
            $messaggio = "<div class='msg msg-error'>Pratica Bocciata. Il socio riceverà la tua motivazione.</div>";
        }
        
        $conn->commit();
        
    } catch (Exception $e) {
        $conn->rollback(); 
        $messaggio = "<div class='msg msg-error'>Errore di sistema durante la registrazione: " . $e->getMessage() . "</div>";
    }
}

$query_attesa = "
    SELECT r.Id_Raccolta, r.Data_Raccolta, r.Luogo, r.Quantita_Peso, r.URL_Foto, r.Id_Specie_Presunta,
           u.Nome, u.Cognome, s.Nome_Scientifico AS Specie_Presunta
    FROM RACCOLTA r
    JOIN UTENTE u ON r.Id_User_Socio = u.Id_User
    JOIN SPECIE_FUNGINA s ON r.Id_Specie_Presunta = s.Id_Specie
    WHERE r.Stato_Lavorazione = 'In attesa'
    ORDER BY r.Data_Raccolta ASC
";
$result_attesa = $conn->query($query_attesa);

$query_specie = "SELECT Id_Specie, Nome_Scientifico FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'Approvata' ORDER BY Nome_Scientifico ASC";
$result_specie = $conn->query($query_specie);
$lista_specie = [];
while($row = $result_specie->fetch_assoc()) {
    $lista_specie[] = $row;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Valida Raccolte Soci - Associazione Micologica</title>
    <style>
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            padding: 20px; 
            color: #1a2721; 
        }
        
        .container { max-width: 1100px; margin: 0 auto; }
        
        
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

        
        .valuta-card { 
            background: white; 
            border: 3px solid #1a2721; 
            border-radius: 4px;
            margin-bottom: 30px; 
            display: flex; 
            overflow: hidden;
            transition: 0.2s;
        }
        .valuta-card:hover { transform: translateY(-3px); }

        
        .valuta-img { 
            width: 300px; 
            background-color: #e3dccf; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-right: 3px solid #1a2721;
            flex-shrink: 0;
        }
        .valuta-img img { width: 100%; height: 100%; object-fit: cover; }
        
        
        .valuta-info { 
            padding: 20px; 
            flex-grow: 1; 
            border-right: 3px solid #1a2721;
            background-color: #ffffff;
        }
        .valuta-info h3 { font-family: 'Georgia', serif; margin-top: 0; color: #1a2721; border-bottom: 2px solid #f4efe6; padding-bottom: 10px; }
        
        .info-tag { background-color: #f4efe6; padding: 10px; border-left: 4px solid #cb8b22; margin-top: 15px; font-weight: bold; font-size: 0.9em; }

        .valuta-form { 
            padding: 20px; 
            width: 350px; 
            background-color: #fbfaff; 
            flex-shrink: 0;
        } 
        
        .form-group { margin-bottom: 15px; }
        label { font-weight: 900; display: block; margin-bottom: 5px; color: #1a2721; text-transform: uppercase; font-size: 0.75em; }
        select, textarea { 
            width: 100%; 
            padding: 10px; 
            border: 2px solid #1a2721; 
            border-radius: 0; 
            box-sizing: border-box; 
            font-weight: bold;
            font-family: inherit;
        }
        
        
        .btn { 
            padding: 12px; 
            border: 2px solid #1a2721; 
            color: white; 
            font-weight: 900; 
            text-transform: uppercase;
            cursor: pointer; 
            width: 100%; 
            margin-bottom: 10px; 
            transition: 0.2s; 
        }
        .btn-certifica { background-color: #27ae60; }
        .btn-certifica:hover { background-color: #219653; }
        
        .btn-boccia { background-color: #b91c1c; }
        .btn-boccia:hover { background-color: #991b1b; }
        
        .btn-inoltra { 
            background-color: #f4efe6; 
            color: #1a2721; 
            text-decoration: none; 
            display: block; 
            text-align: center;
            font-size: 0.85em;
            padding: 10px;
            border: 2px solid #1a2721; 
            font-weight: bold;
            box-sizing: border-box; 
            transition: 0.2s;
        }
        .btn-inoltra:hover { background-color: #e3dccf; }
        
        .msg { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 2px solid #1a2721; }
        .msg-success { background-color: #4ade80; }
        .msg-error { background-color: #f87171; }
        
        .empty-state { text-align: center; padding: 50px; background: white; border: 3px solid #1a2721; }
        .empty-state h3 { font-family: 'Georgia', serif; color: #27ae60; font-size: 2em; }
    </style>
    
    <script>
        function impostaAzione(idForm, azione) {
            document.getElementById('azione_' + idForm).value = azione;
            if(azione === 'Boccia' && document.getElementById('mot_' + idForm).value.trim() === '') {
                alert("Per bocciare la raccolta devi inserire una motivazione tecnica!");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Coda di Validazione (Raccolte Soci)</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <?php echo $messaggio; ?>

        <?php if ($result_attesa->num_rows > 0): ?>
            <?php while($row = $result_attesa->fetch_assoc()): ?>
                <div class="valuta-card">
                    
                    <div class="valuta-img">
                        <?php if(!empty($row['URL_Foto']) && file_exists($row['URL_Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['URL_Foto']); ?>" alt="Foto Raccolta">
                        <?php else: ?>
                            <span style="font-weight: bold;">FOTO MANCANTE</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="valuta-info">
                        <h3>Dati Ritrovamento</h3>
                        <p><b>Socio:</b> <?php echo htmlspecialchars($row['Nome'] . " " . $row['Cognome']); ?></p>
                        <p><b>Data:</b> <?php echo date('d/m/Y', strtotime($row['Data_Raccolta'])); ?></p>
                        <p><b>Luogo:</b> <?php echo htmlspecialchars($row['Luogo']); ?> (<?php echo $row['Quantita_Peso']; ?> Kg)</p>
                        
                        <div class="info-tag">
                            DICHIARAZIONE SOCIO:<br>
                            <i><?php echo htmlspecialchars($row['Specie_Presunta']); ?></i>
                        </div>
                    </div>
                    
                    <div class="valuta-form">
                        <form method="POST" action="certifica_soci.php">
                            <input type="hidden" name="id_raccolta" value="<?php echo $row['Id_Raccolta']; ?>">
                            <input type="hidden" name="azione" id="azione_<?php echo $row['Id_Raccolta']; ?>" value="">
                            
                            <div class="form-group">
                                <label>1. Identificazione Specie:</label>
                                <select name="id_specie_reale" required>
                                    <?php foreach($lista_specie as $specie): ?>
                                        <option value="<?php echo $specie['Id_Specie']; ?>" <?php echo ($specie['Id_Specie'] == $row['Id_Specie_Presunta']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($specie['Nome_Scientifico']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>2. Note Botaniche / Diagnosi:</label>
                                <textarea name="motivazione" id="mot_<?php echo $row['Id_Raccolta']; ?>" rows="3" placeholder="Inserisci i dettagli tecnici della validazione..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-certifica" onclick="return impostaAzione(<?php echo $row['Id_Raccolta']; ?>, 'Certifica')">Valida e Pubblica</button>
                            <button type="submit" class="btn btn-boccia" onclick="return impostaAzione(<?php echo $row['Id_Raccolta']; ?>, 'Boccia')">Respingi Pratica</button>
                            
                            <a href="proponi_specie.php" class="btn-inoltra">Fungo non in lista? Proponi Specie</a>
                            </div>
                        </form>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Ottimo lavoro!</h3>
                <p>Non ci sono raccolte in attesa di validazione. Hai smaltito tutta la coda.</p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>