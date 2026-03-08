<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || ($_SESSION['ruolo'] !== 'Utente Premium' && $_SESSION['ruolo'] !== 'Utente Esterno')) {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso Negato</h2><p>Servizio riservato ai clienti.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$id_utente = $_SESSION['id_user'];

$query = "
    SELECT c.Id_Consulenza, c.Data_Richiesta, c.URL_Foto, c.Colore_Cappello, c.Colore_Fusto, c.Habitat_Alberi, c.Esito_Risposta,
           m.Nome AS Nome_Micologo, m.Cognome AS Cognome_Micologo
    FROM CONSULENZA_PRIVATA c
    LEFT JOIN UTENTE m ON c.Id_User_Micologo = m.Id_User
    WHERE c.Id_User_Premium = ?
    ORDER BY c.Data_Richiesta DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le Mie Consulenze - Associazione Micologica</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            padding: 20px; 
            color: #1a2721; 
        }
        
        .container { max-width: 900px; margin: 0 auto; }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 4px solid #cb8b22; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header h2 { font-family: 'Georgia', serif; color: #1a2721; margin: 0; }
        
        .btn-back { background-color: #b91c1c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; border: 2px solid #7f1d1d; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { background-color: #991b1b; }

        .subtitle { font-size: 1.1em; font-weight: bold; color: #1a2721; margin-bottom: 25px; }
        
        .card { 
            background: white; 
            border-radius: 4px; 
            border: 3px solid #1a2721; 
            margin-bottom: 25px; 
            display: flex; 
            transition: 0.2s; 
            overflow: hidden;
        }
        .card:hover { transform: translateY(-3px); }
        
        .card-img { 
            width: 250px; 
            background-color: #e3dccf; 
            border-right: 3px solid #1a2721; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #1a2721; 
            font-weight: bold; 
            flex-shrink: 0; 
        }
        .card-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .card-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        
        .card-title { font-family: 'Georgia', serif; font-size: 1.4em; margin: 0 0 10px 0; color: #1a2721; }
        
        .dati-utente { 
            margin-bottom: 15px; 
            font-size: 0.95em; 
            background-color: #f4efe6; 
            padding: 12px; 
            border: 2px solid #1a2721; 
            line-height: 1.5;
        }
        .dati-utente b { text-transform: uppercase; font-size: 0.9em; }
        
        .esito-box { margin-top: 15px; padding: 15px; font-size: 0.9em; font-weight: bold; border: 2px solid #1a2721; }
        .esito-attesa { background-color: #fef9e7; border-left: 6px solid #f39c12; }
        .esito-risolto { background-color: #f0fdf4; border-left: 6px solid #27ae60; }
        
        .badge { 
            padding: 4px 10px; 
            font-size: 0.8em; 
            font-weight: 900; 
            color: #1a2721; 
            display: inline-block; 
            margin-bottom: 10px; 
            border: 2px solid #1a2721; 
            text-transform: uppercase; 
        }
        .bg-warning { background-color: #fcd34d; }
        .bg-success { background-color: #4ade80; }
        
        .msg-empty {
            text-align: center;
            padding: 40px;
            background: #ffffff;
            border: 3px solid #1a2721;
            font-weight: bold;
        }
        .msg-empty h3 { font-family: 'Georgia', serif; font-size: 1.5em; margin-top: 0; }
        
        .btn-action { 
            display: inline-block; 
            margin-top: 15px; 
            padding: 12px 20px; 
            color: white; 
            text-decoration: none; 
            font-weight: bold; 
            text-transform: uppercase; 
            border: 2px solid #1a2721; 
            transition: 0.2s; 
        }
        .btn-premium { background-color: #cb8b22; }
        .btn-premium:hover { background-color: #b3791d; }
        .btn-buy { background-color: #27ae60; }
        .btn-buy:hover { background-color: #219653; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Storico delle mie Consulenze</h2>
            <a href="index.php" class="btn-back">⬅ Torna alla Dashboard</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="subtitle">Qui puoi consultare l'esito delle tue richieste passate e controllare lo stato di quelle in corso.</div>
            
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-img">
                        <?php if(!empty($row['URL_Foto']) && file_exists($row['URL_Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['URL_Foto']); ?>" alt="Foto Fungo">
                        <?php else: ?>
                            <span>Nessuna Foto</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <div>
                            <?php if(empty($row['Esito_Risposta'])): ?>
                                <span class="badge bg-warning">In Lavorazione</span>
                            <?php else: ?>
                                <span class="badge bg-success">Completata</span>
                            <?php endif; ?>
                            
                            <h3 class="card-title">Richiesta del <?php echo date('d/m/Y', strtotime($row['Data_Richiesta'])); ?></h3>
                            
                            <div class="dati-utente">
                                <b>I tuoi appunti:</b><br>
                                Cappello: <i><?php echo htmlspecialchars($row['Colore_Cappello']); ?></i> | 
                                Fusto: <i><?php echo htmlspecialchars($row['Colore_Fusto']); ?></i> | 
                                Habitat: <i><?php echo htmlspecialchars($row['Habitat_Alberi']); ?></i>
                            </div>
                        </div>
                        
                        <?php if(empty($row['Esito_Risposta'])): ?>
                            <div class="esito-box esito-attesa">
                                <b>Stato:</b> Un nostro esperto prenderà in carico la tua richiesta il prima possibile. Riceverai qui il referto.
                            </div>
                        <?php else: ?>
                            <div class="esito-box esito-risolto">
                                <b>Risposta del Micologo (Dott. <?php echo htmlspecialchars($row['Cognome_Micologo']); ?>):</b>
                                <p style="margin: 8px 0 0 0; line-height: 1.4; font-weight: normal;">
                                    "<?php echo nl2br(htmlspecialchars($row['Esito_Risposta'])); ?>"
                                </p>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="msg-empty">
                <h3>Nessuna consulenza richiesta</h3>
                <p>Non hai ancora inviato nessuna foto ai nostri esperti.</p>
                
                <?php if($_SESSION['ruolo'] === 'Utente Premium'): ?>
                    <a href="richiedi_consulenza.php" class="btn-action btn-premium">Richiedi Consulenza</a>
                <?php else: ?>
                    <a href="acquista_servizi.php" class="btn-action btn-buy">Acquista Consulenza</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>