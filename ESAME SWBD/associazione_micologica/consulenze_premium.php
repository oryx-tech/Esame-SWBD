<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Micologo Esperto') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso Negato</h2><p>Solo i Micologi Esperti possono accedere a quest'area.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$id_micologo = $_SESSION['id_user'];
$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_consulenza = intval($_POST['id_consulenza']);
    $risposta = trim($_POST['risposta']);
    
    $stmt = $conn->prepare("UPDATE CONSULENZA_PRIVATA SET Esito_Risposta = ?, Id_User_Micologo = ? WHERE Id_Consulenza = ?");
    $stmt->bind_param("sii", $risposta, $id_micologo, $id_consulenza);
    
    if ($stmt->execute()) {
        $messaggio = "<div class='msg msg-success'>Consulenza evasa con successo! Il cliente riceverà la tua perizia.</div>";
    } else {
        $messaggio = "<div class='msg msg-error'>Errore durante il salvataggio della risposta.</div>";
    }
}

$query_premium = "
    SELECT c.Id_Consulenza, c.Data_Richiesta, c.URL_Foto, c.Colore_Cappello, c.Colore_Fusto, c.Habitat_Alberi,
           u.Nome, u.Cognome, u.Email
    FROM CONSULENZA_PRIVATA c
    JOIN UTENTE u ON c.Id_User_Premium = u.Id_User
    WHERE c.Id_User_Micologo IS NULL
    ORDER BY c.Data_Richiesta ASC
";
$result_premium = $conn->query($query_premium);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Consulenze Premium - Associazione Micologica</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            color: #1a2721; 
        }
        
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        
        .header { 
            background-color: #1a2721; 
            padding: 20px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 4px solid #cb8b22; 
        }
        
        .header h2 { 
            font-family: 'Georgia', serif; 
            color: #f4efe6; 
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
            text-transform: uppercase;
            font-size: 0.85em;
        }
        .btn-back:hover { background-color: #991b1b; }

        .msg { padding: 15px; border-radius: 4px; margin-bottom: 25px; font-weight: bold; text-align: center; border: 3px solid #1a2721; }
        .msg-success { background-color: #4ade80; }
        .msg-error { background-color: #f87171; }

        .premium-card { 
            background: white; 
            border: 3px solid #1a2721; 
            margin-bottom: 30px; 
            display: flex; 
            overflow: hidden; 
            transition: 0.2s;
        }
        .premium-card:hover { transform: translateY(-3px); }

        .premium-img { 
            width: 320px; 
            background-color: #e3dccf; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            position: relative; 
            border-right: 3px solid #1a2721;
            flex-shrink: 0;
        }
        .premium-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .badge-vip { 
            position: absolute; 
            top: 10px; 
            left: 10px; 
            background: #cb8b22; 
            color: white; 
            padding: 5px 10px; 
            font-weight: 900; 
            font-size: 0.75em; 
            text-transform: uppercase;
            border: 2px solid #1a2721;
        }
        
        .premium-info { 
            padding: 25px; 
            flex-grow: 1; 
            background-color: #ffffff; 
            border-right: 3px solid #1a2721; 
        }
        .premium-info h3 { 
            font-family: 'Georgia', serif; 
            margin-top: 0; 
            color: #1a2721; 
            border-bottom: 2px solid #f4efe6; 
            padding-bottom: 10px;
        }
        
        .data-label { font-size: 0.75em; color: #1a2721; text-transform: uppercase; margin-bottom: 4px; font-weight: 900; display: block; }
        .data-value { font-size: 1em; color: #1a2721; margin-bottom: 15px; padding: 8px; background-color: #f4efe6; border-left: 4px solid #cb8b22; font-weight: bold; }
        
        .premium-form { 
            padding: 25px; 
            width: 380px; 
            background-color: #fbfaff; 
            flex-shrink: 0;
        }
        .premium-form h3 { font-family: 'Georgia', serif; margin-top: 0; color: #1a2721; }
        
        textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #1a2721; 
            border-radius: 0; 
            box-sizing: border-box; 
            font-family: inherit; 
            margin-bottom: 15px; 
            resize: none; 
            font-weight: bold;
        }
        
        button[type="submit"] { 
            padding: 15px; 
            border: 2px solid #1a2721; 
            color: white; 
            font-weight: 900; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1em; 
            background-color: #cb8b22; 
            transition: 0.2s; 
            text-transform: uppercase;
        }
        button[type="submit"]:hover { background-color: #b3791d; }

        .empty-state { text-align: center; padding: 50px; background: white; border: 3px solid #1a2721; }
        .empty-state h3 { font-family: 'Georgia', serif; color: #27ae60; font-size: 2em; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Coda Consulenze Premium</h2>
        <a href="index.php" class="btn-back">Indietro</a>
    </div>

    <div class="container">

        <?php echo $messaggio; ?>

        <?php if ($result_premium->num_rows > 0): ?>
            <p style="font-weight: bold; margin-bottom: 30px;">Analizza i dati morfologici e l'habitat per fornire un referto professionale ai clienti Premium.</p>
            
            <?php while($row = $result_premium->fetch_assoc()): ?>
                <div class="premium-card">
                    
                    <div class="premium-img">
                        <span class="badge-vip">Servizio Premium</span>
                        <?php if(!empty($row['URL_Foto']) && file_exists($row['URL_Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['URL_Foto']); ?>" alt="Foto cliente">
                        <?php else: ?>
                            <span style="font-weight: bold;">Foto non disponibile</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="premium-info">
                        <h3>Dati Diagnostici</h3>
                        <p style="font-size: 0.9em;">
                            Cliente: <strong><?php echo htmlspecialchars($row['Nome'] . " " . $row['Cognome']); ?></strong><br>
                            Data richiesta: <?php echo date('d/m/Y', strtotime($row['Data_Richiesta'])); ?>
                        </p>
                        
                        <div style="margin-top: 20px;">
                            <span class="data-label">Colore del Cappello:</span>
                            <div class="data-value"><?php echo htmlspecialchars($row['Colore_Cappello']); ?></div>
                            
                            <span class="data-label">Colore del Fusto:</span>
                            <div class="data-value"><?php echo htmlspecialchars($row['Colore_Fusto']); ?></div>
                            
                            <span class="data-label">Habitat riscontrato:</span>
                            <div class="data-value"><?php echo htmlspecialchars($row['Habitat_Alberi']); ?></div>
                        </div>
                    </div>
                    
                    <div class="premium-form">
                        <h3>Perizia Tecnica</h3>
                        <form method="POST" action="consulenze_premium.php">
                            <input type="hidden" name="id_consulenza" value="<?php echo $row['Id_Consulenza']; ?>">
                            
                            <label style="font-size: 0.75em; font-weight: 900; color: #1a2721; margin-bottom: 8px; display: block; text-transform: uppercase;">Responso professionale:</label>
                            <textarea name="risposta" rows="8" placeholder="Inserisci qui l'analisi dettagliata..." required></textarea>
                            
                            <button type="submit">Invia Referto</button>
                        </form>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Coda di lavoro evasa</h3>
                <p>Non ci sono consulenze Premium in attesa di risposta.</p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>