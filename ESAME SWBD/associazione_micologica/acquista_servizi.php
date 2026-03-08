<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Utente Esterno') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2 style='color:red;'>Accesso non consentito</h2><p>Solo gli Utenti Esterni possono acquistare pacchetti.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$id_utente = $_SESSION['id_user'];
$messaggio = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_servizio = $_POST['pacchetto'];
    $importo = ($tipo_servizio === 'Abbonamento Annuale') ? 100.00 : 15.00;
    $data_oggi = date('Y-m-d');
    
    $conn->begin_transaction();
    
    try {
        $stmt_pag = $conn->prepare("INSERT INTO PAGAMENTO (Data_Transazione, Importo, Tipo_Servizio, Id_User_Cliente) VALUES (?, ?, ?, ?)");
        $stmt_pag->bind_param("sdsi", $data_oggi, $importo, $tipo_servizio, $id_utente);
        $stmt_pag->execute();
        
        $nuovo_ruolo = 'Utente Premium';
        $stmt_usr = $conn->prepare("UPDATE UTENTE SET Ruolo = ? WHERE Id_User = ?");
        $stmt_usr->bind_param("si", $nuovo_ruolo, $id_utente);
        $stmt_usr->execute();
        
        $conn->commit();
        
        $_SESSION['ruolo'] = 'Utente Premium';
        $messaggio = "<div class='msg msg-success'> Transazione autorizzata!<br>Sei stato promosso a Utente Premium. <br><br> <a href='richiedi_consulenza.php' style='color:#155724; font-weight:bold;'>Clicca qui per richiedere la consulenza</a></div>";
        
    } catch (Exception $e) {
        $conn->rollback();
        $messaggio = "<div class='msg msg-error'> Transazione fallita: Errore del server.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Acquista Servizi Premium - Associazione Micologica</title>
   <style>
        
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4efe6; margin: 0; padding: 20px; color: #1a2721; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #cb8b22; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { font-family: 'Georgia', serif; color: #1a2721; margin: 0; }
        
        .btn-back { background-color: #b91c1c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; border: 2px solid #7f1d1d; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { background-color: #991b1b; }
        
        .msg { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 2px solid #1a2721; }
        .msg-success { background-color: #4ade80; color: #1a2721; }
        .msg-error { background-color: #f87171; color: #1a2721; }

        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .pricing-card { background: white; border-radius: 4px; padding: 25px; text-align: center; border: 3px solid #1a2721; transition: 0.2s; }
        .pricing-card:hover { transform: translateY(-3px); }
        
        .pricing-card h3 { font-family: 'Georgia', serif; color: #1a2721; margin: 0 0 10px 0; border-bottom: 2px solid #e3dccf; padding-bottom: 10px; font-size: 1.5em; }
        .price { font-size: 2.2em; font-weight: bold; color: #1a2721; margin: 15px 0; }
        
        .payment-form { background-color: #f4efe6; padding: 15px; border-radius: 4px; border: 2px solid #1a2721; margin-top: 15px; text-align: left; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-size: 0.85em; font-weight: 900; margin-bottom: 5px; color: #1a2721; text-transform: uppercase; }
        .form-group input { width: 100%; padding: 8px; border-radius: 0; border: 2px solid #1a2721; box-sizing: border-box; font-weight: bold; }
        .cc-row { display: flex; gap: 10px; }
        
        .payment-form button { width: 100%; padding: 12px; border-radius: 0; border: 2px solid #1a2721; font-weight: bold; cursor: pointer; color: white; font-size: 1.1em; background-color: #cb8b22; margin-top: 10px; transition: 0.2s; text-transform: uppercase; }
        .payment-form button:hover { background-color: #b3791d; }
        
        .btn-green { background-color: #27ae60 !important; }
        .btn-green:hover { background-color: #219653 !important; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Checkout Sicuro</h2>
            <a href="index.php" class="btn-back">⬅ Torna alla Dashboard</a>
        </div>

        <?php echo $messaggio; ?>

        <?php if(isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'Utente Esterno'): ?>
            <p style="text-align: center; font-size: 1.1em; font-weight: bold; color: #1a2721;">Inserisci i dati di fatturazione per attivare il servizio desiderato.</p>
            
            <div class="pricing-grid">
                
                <div class="pricing-card">
                    <h3>Consulenza Singola</h3>
                    <div class="price">€ 15<span style="font-size: 0.4em; font-weight: normal;">/una tantum</span></div>
                    
                    <form method="POST" class="payment-form">
                        <input type="hidden" name="pacchetto" value="Consulenza Singola">
                        
                        <div class="form-group">
                            <label>Titolare Carta</label>
                            <input type="text" placeholder="Nome e Cognome" required>
                        </div>
                        <div class="form-group">
                            <label>Numero Carta</label>
                            <input type="text" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19" required>
                        </div>
                        <div class="cc-row">
                            <div class="form-group" style="flex: 1;">
                                <label>Scadenza</label>
                                <input type="text" placeholder="MM/AA" maxlength="5" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>CVV</label>
                                <input type="text" placeholder="123" maxlength="3" required>
                            </div>
                        </div>
                        
                        <button type="submit">Paga 15,00 €</button>
                    </form>
                </div>

                <div class="pricing-card">
                    <h3>Abbonamento Annuale</h3>
                    <div class="price">€ 100<span style="font-size: 0.4em; font-weight: normal;">/anno</span></div>
                    
                    <form method="POST" class="payment-form">
                        <input type="hidden" name="pacchetto" value="Abbonamento Annuale">
                        
                        <div class="form-group">
                            <label>Titolare Carta</label>
                            <input type="text" placeholder="Nome e Cognome" required>
                        </div>
                        <div class="form-group">
                            <label>Numero Carta</label>
                            <input type="text" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19" required>
                        </div>
                        <div class="cc-row">
                            <div class="form-group" style="flex: 1;">
                                <label>Scadenza</label>
                                <input type="text" placeholder="MM/AA" maxlength="5" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>CVV</label>
                                <input type="text" placeholder="123" maxlength="3" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-green">Paga 100,00 €</button>
                    </form>
                </div>

            </div>
        <?php endif; ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scadenzaInputs = document.querySelectorAll('input[placeholder="MM/AA"]');
            
            scadenzaInputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    let valore = e.target.value.replace(/\D/g, ''); 
                    if (valore.length > 2) {
                        valore = valore.substring(0, 2) + '/' + valore.substring(2, 4); 
                    }
                    e.target.value = valore;
                });
            });
        });
    </script>
</body>
</html>