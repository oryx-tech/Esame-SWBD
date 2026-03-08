<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Socio Cercatore') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso Negato</h2><p>Questa sezione è riservata ai Soci Cercatori tesserati.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}
$id_socio = $_SESSION['id_user'];

$query = "
    SELECT 
        r.Id_Raccolta, r.Data_Raccolta, r.Luogo, r.Quantita_Peso, r.URL_Foto, r.Stato_Lavorazione,
        s_presunta.Nome_Scientifico AS Specie_Presunta, s_presunta.Nome_Volgare AS Volgare_Presunta,
        c.Data_Emissione, c.Esito, c.Motivazione_Note,
        s_reale.Nome_Scientifico AS Specie_Reale, s_reale.Categoria AS Categoria_Reale,
        m.Nome AS Nome_Micologo, m.Cognome AS Cognome_Micologo
    FROM RACCOLTA r
    JOIN SPECIE_FUNGINA s_presunta ON r.Id_Specie_Presunta = s_presunta.Id_Specie
    LEFT JOIN CERTIFICAZIONE c ON r.Id_Raccolta = c.Id_Raccolta
    LEFT JOIN SPECIE_FUNGINA s_reale ON c.Id_Specie_Reale = s_reale.Id_Specie
    LEFT JOIN UTENTE m ON c.Id_User_Micologo = m.Id_User
    WHERE r.Id_User_Socio = ?
    ORDER BY r.Data_Raccolta DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_socio);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Stato Mie Raccolte - Associazione Micologica</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            padding: 20px; 
            color: #1a2721; 
        }
        
        .container { max-width: 950px; margin: 0 auto; }
        
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

        .card { 
            background: white; 
            border-radius: 4px; 
            border: 3px solid #1a2721; 
            margin-bottom: 25px; 
            display: flex; 
            overflow: hidden; 
            transition: 0.2s;
        }
        .card:hover { transform: translateY(-3px); }
        
        .card-img { 
            width: 220px; 
            background-color: #e3dccf; 
            border-right: 3px solid #1a2721;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            flex-shrink: 0;
        }
        .card-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .card-content { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        
        .raccolta-info { font-size: 0.95em; font-weight: bold; margin-bottom: 15px; color: #1a2721; }
        
        .presunta-box { 
            background-color: #f4efe6; 
            padding: 10px; 
            border: 2px solid #1a2721; 
            margin-bottom: 15px; 
            font-size: 0.9em; 
        }
        
        .badge { 
            display: inline-block; 
            padding: 5px 10px; 
            border: 2px solid #1a2721; 
            font-size: 0.8em; 
            font-weight: 900; 
            color: #1a2721; 
            margin-bottom: 10px; 
            text-transform: uppercase;
        }
        .status-attesa { background-color: #fcd34d; }
        .status-certificata { background-color: #4ade80; }
        .status-bocciata { background-color: #f87171; }
        .status-admin { background-color: #c084fc; }
        
        .verdetto-box { padding: 15px; border: 2px solid #1a2721; margin-top: 10px; font-size: 0.95em; font-weight: bold; }
        .verdetto-ok { background-color: #f0fdf4; border-left: 6px solid #27ae60; }
        .verdetto-ko { background-color: #fef2f2; border-left: 6px solid #e74c3c; }
        
        .msg-empty {
            text-align: center;
            padding: 40px;
            background: #ffffff;
            border: 3px solid #1a2721;
        }
        .btn-empty {
            display: inline-block; 
            margin-top: 15px; 
            padding: 10px 20px; 
            background-color: #27ae60; 
            color: white; 
            text-decoration: none; 
            border: 2px solid #1a2721; 
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Monitoraggio Raccolte Personali</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <p style="font-weight: bold; margin-bottom: 25px;">Tieni traccia di tutte le tue uscite nel bosco e controlla l'esito delle validazioni.</p>
            
            <?php while($row = $result->fetch_assoc()): 
                $badge_class = '';
                if ($row['Stato_Lavorazione'] == 'In attesa') $badge_class = 'status-attesa';
                elseif ($row['Stato_Lavorazione'] == 'Certificata') $badge_class = 'status-certificata';
                elseif ($row['Stato_Lavorazione'] == 'Bocciata') $badge_class = 'status-bocciata';
                elseif ($row['Stato_Lavorazione'] == 'Inoltrata all Admin') $badge_class = 'status-admin';
            ?>
                <div class="card">
                    <div class="card-img">
                        <?php if(!empty($row['URL_Foto']) && file_exists($row['URL_Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['URL_Foto']); ?>" alt="La tua raccolta">
                        <?php else: ?>
                            <span style="font-weight: bold;">Nessuna Foto</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-content">
                        <div>
                            <span class="badge <?php echo $badge_class; ?>">
                                <?php echo strtoupper($row['Stato_Lavorazione']); ?>
                            </span>
                            
                            <div class="raccolta-info">
                                Data: <?php echo date('d/m/Y', strtotime($row['Data_Raccolta'])); ?> | 
                                Luogo: <?php echo htmlspecialchars($row['Luogo']); ?> | 
                                Peso: <?php echo $row['Quantita_Peso']; ?> Kg
                            </div>
                            
                            <div class="presunta-box">
                                <b>Tu avevi dichiarato:</b> <i><?php echo htmlspecialchars($row['Specie_Presunta']); ?> (<?php echo htmlspecialchars($row['Volgare_Presunta']); ?>)</i>
                            </div>
                        </div>
                        
                        <?php if($row['Stato_Lavorazione'] == 'Certificata' || $row['Stato_Lavorazione'] == 'Bocciata'): ?>
                            <div class="verdetto-box <?php echo ($row['Stato_Lavorazione'] == 'Certificata') ? 'verdetto-ok' : 'verdetto-ko'; ?>">
                                <div style="margin-bottom: 8px;">
                                    Verdetto Micologo: Dott. <?php echo htmlspecialchars($row['Cognome_Micologo']); ?> 
                                    (il <?php echo date('d/m/Y', strtotime($row['Data_Emissione'])); ?>)
                                </div>
                                
                                <?php if($row['Stato_Lavorazione'] == 'Certificata'): ?>
                                    <div style="margin-bottom: 8px;">
                                        <b>Specie Effettiva:</b> <i><?php echo htmlspecialchars($row['Specie_Reale']); ?></i> 
                                        [<?php echo htmlspecialchars($row['Categoria_Reale']); ?>]
                                    </div>
                                    <?php if($row['Specie_Presunta'] != $row['Specie_Reale']): ?>
                                        <div style="color: #b91c1c; font-size: 0.9em; margin-bottom: 8px;">
                                            <i>Attenzione: Errore di identificazione rilevato.</i>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div style="background: white; padding: 8px; border: 1px solid #1a2721; font-weight: normal;">
                                    <b>Note:</b> "<?php echo nl2br(htmlspecialchars($row['Motivazione_Note'])); ?>"
                                </div>
                            </div>
                        <?php elseif($row['Stato_Lavorazione'] == 'Inoltrata all Admin'): ?>
                            <div class="verdetto-box" style="background-color: #f5f3ff; border-left: 6px solid #7c3aed;">
                                <b>Analisi Sospesa:</b> Specie non censita. Pratica inoltrata alla Direzione per l'aggiornamento del catalogo.
                            </div>
                        <?php else: ?>
                            <p style="color: #1a2721; font-style: italic; font-size: 0.9em; margin: 0; font-weight: bold;">In attesa di valutazione tecnica.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="msg-empty">
                <h3>Il tuo cesto è vuoto!</h3>
                <p>Non hai ancora registrato nessun ritrovamento nel sistema.</p>
                <a href="inserisci_raccolta.php" class="btn-empty">Registra la prima raccolta</a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>