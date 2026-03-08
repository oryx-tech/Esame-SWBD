<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['loggato'] !== true) {
    header("Location: login.php");
    exit();
}

$ruolo = $_SESSION['ruolo'];
$nome_completo = $_SESSION['nome'] . " " . $_SESSION['cognome'];

$messaggio_abbonamento = "";

if ($ruolo === 'Utente Premium') {
    $query_abbonamento = "SELECT Data_Transazione FROM PAGAMENTO WHERE Id_User_Cliente = ? AND Tipo_Servizio = 'Abbonamento Annuale' ORDER BY Data_Transazione DESC LIMIT 1";
    $stmt_abb = $conn->prepare($query_abbonamento);
    $stmt_abb->bind_param("i", $_SESSION['id_user']);
    $stmt_abb->execute();
    $result_abb = $stmt_abb->get_result();
    
    if ($row_abb = $result_abb->fetch_assoc()) {
        $data_acquisto = new DateTime($row_abb['Data_Transazione']);
        
        $data_scadenza = clone $data_acquisto;
        $data_scadenza->modify('+1 year'); 
        
        $oggi = new DateTime();
        
        if ($oggi < $data_scadenza) {
            $giorni_rimanenti = $oggi->diff($data_scadenza)->days;
            $data_formattata = $data_scadenza->format('d/m/Y');
            $messaggio_abbonamento = "Abbonamento Premium attivo. Scade il <strong>$data_formattata</strong> (Mancano $giorni_rimanenti giorni).";
        } else {
            $messaggio_abbonamento = "Il tuo Abbonamento Premium è scaduto. Rinnovalo per continuare a usare i servizi.";
        }
    }
    $stmt_abb->close();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Associazione Micologica</title>
    <style>
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            color: #1a2721; 
            margin: 0; 
            padding: 0;
        }

        
        .header {
            background-color: #1a2721; 
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #cb8b22; 
        }

        .header-logo {
            color: #cb8b22;
            font-size: 1.2em;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.95em;
        }

        .badge-ruolo {
            background-color: #f3f4f6;
            color: #1a2721;
            padding: 4px 10px;
            font-size: 0.85em;
            font-weight: bold;
            border-radius: 4px;
            border: 2px solid #1a2721;
        }
        
        .badge-esterno { background-color: #e5e7eb; border-color: #9ca3af; }

        .btn-esci {
            background-color: #b91c1c; 
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            font-weight: bold;
            border: 2px solid #7f1d1d;
            border-radius: 4px;
            box-shadow: 2px 2px 0px #7f1d1d; 
            transition: all 0.1s; 
        }
        .btn-esci:hover {
            transform: translate(2px, 2px); 
            box-shadow: 0px 0px 0px #7f1d1d; 
        }

       
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .titolo-sezione {
            font-family: 'Georgia', serif; 
            font-size: 2em;
            color: #1a2721;
            border-bottom: 3px solid #cb8b22;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        
        .grid-bottoni {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 30px;
        }

        .card-btn {
            background-color: #ffffff;
            border: 3px solid #1a2721; 
            border-radius: 6px;
            padding: 40px 20px;
            text-align: center;
            text-decoration: none;
            color: #1a2721;
            box-shadow: 6px 6px 0px #1a2721; 
            transition: all 0.1s; 
            display: block;
        }

        .card-btn:hover {
            transform: translate(3px, 3px);
            box-shadow: 3px 3px 0px #1a2721;
            background-color: #fbfbfb;
        }

        .card-btn:active {
            transform: translate(6px, 6px);
            box-shadow: 0px 0px 0px #1a2721;
        }

        .card-icona { font-size: 3em; display: block; margin-bottom: 15px; }
        .card-titolo { font-size: 1.4em; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; color: #1a2721; }
        .card-desc { font-size: 0.95em; color: #4b5563; }
        
        
        .card-premium { border-color: #cb8b22; box-shadow: 6px 6px 0px #cb8b22; }
        .card-premium .card-titolo { color: #cb8b22; }
        .card-premium:hover { box-shadow: 3px 3px 0px #cb8b22; }
        .card-premium:active { box-shadow: 0px 0px 0px #cb8b22; }
    </style>
</head>

    <div class="header">
        <div class="header-logo">Associazione Micologica</div>
        <div class="header-user">
            <span>Ciao, <strong><?php echo htmlspecialchars($nome_completo); ?></strong></span>
            
            <span class="badge-ruolo"><?php echo htmlspecialchars($ruolo); ?></span>
            
            <a href="logout.php" class="btn-esci">Esci</a>
        </div>
    </div>

    <div class="container">
        
        <h1 class="titolo-sezione">Pannello di Controllo</h1>
        
        <?php if ($messaggio_abbonamento != ""): ?>
            <div style="background-color: #fffaf0; border: 3px solid #cb8b22; padding: 15px; margin-bottom: 30px; border-radius: 4px; color: #1a2721; font-weight: bold; box-shadow: 4px 4px 0px #cb8b22;">
                ⏳ <?php echo $messaggio_abbonamento; ?>
            </div>
        <?php endif; ?>
        
        <div class="grid-bottoni">
            
            <a href="archivio_pubblico.php" class="card-btn">
                <span class="card-icona"></span>
                <div class="card-titolo">Archivio Pubblico</div>
                <div class="card-desc">Consulta il database ufficiale delle specie certificate.</div>
            </a>

            <?php if ($ruolo === 'Utente Esterno'): ?>
                <a href="acquista_servizi.php" class="card-btn card-premium">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Diventa Premium</div>
                    <div class="card-desc">Acquista l'abbonamento o singole consulenze.</div>
                </a>
                <a href="mie_consulenze.php" class="card-btn">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Mie Consulenze</div>
                    <div class="card-desc">Vedi lo storico delle tue richieste.</div>
                </a>
            <?php endif; ?>

            <?php if ($ruolo === 'Utente Premium'): ?>
                <a href="richiedi_consulenza.php" class="card-btn card-premium">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Richiedi Consulenza</div>
                    <div class="card-desc">Invia una foto per valutazione privata.</div>
                </a>
                <a href="mie_consulenze.php" class="card-btn">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Storico Consulenze</div>
                    <div class="card-desc">Controlla le risposte dei micologi.</div>
                </a>
            <?php endif; ?>

            <?php if ($ruolo === 'Socio Cercatore'): ?>
                <a href="inserisci_raccolta.php" class="card-btn" style="border-color: #27ae60; box-shadow: 6px 6px 0px #27ae60;">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Registra Raccolta</div>
                    <div class="card-desc">Invia i tuoi ritrovamenti per la gamification.</div>
                </a>
                <a href="mie_raccolte.php" class="card-btn">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Le mie Raccolte</div>
                    <div class="card-desc">Controlla lo stato delle certificazioni.</div>
                </a>
            <?php endif; ?>

            <?php if ($ruolo === 'Micologo Esperto'): ?>
                <a href="certifica_soci.php" class="card-btn" style="border-color: #9b59b6; box-shadow: 6px 6px 0px #9b59b6;">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Valida Raccolte</div>
                    <div class="card-desc">Certifica i funghi trovati dai Soci.</div>
                </a>
                <a href="consulenze_premium.php" class="card-btn card-premium">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Consulenze Premium</div>
                    <div class="card-desc">Rispondi alle richieste private degli utenti.</div>
                </a>
                <a href="proponi_specie.php" class="card-btn">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Nuove Specie</div>
                    <div class="card-desc">Proponi aggiunte all'enciclopedia.</div>
                </a>
            <?php endif; ?>

            <?php if ($ruolo === 'Amministratore'): ?>
                <a href="admin_utenti.php" class="card-btn" style="border-color: #b91c1c; box-shadow: 6px 6px 0px #b91c1c;">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Utenti & Ruoli</div>
                    <div class="card-desc">Gestisci i permessi e gli accessi.</div>
                </a>
                <a href="admin_specie.php" class="card-btn">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Specie Fungine</div>
                    <div class="card-desc">Approva o rifiuta le specie proposte.</div>
                </a>
                <a href="statistiche.php" class="card-btn">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Gamification</div>
                    <div class="card-desc">Vedi le classifiche e le statistiche dei soci.</div>
                </a>
                <a href="admin_transazioni.php" class="card-btn" style="border-color: #047857; box-shadow: 6px 6px 0px #047857;">
                    <span class="card-icona"></span>
                    <div class="card-titolo">Transazioni</div>
                    <div class="card-desc">Controlla il fatturato dell'Associazione.</div>
                </a>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>