<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Amministratore') {
    die("<h2 style='color:red; text-align:center;'>Accesso Negato</h2>");
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_target = intval($_POST['id_user']);
    $azione = $_POST['azione'];

    if ($azione === 'AggiornaRuolo') {
        $nuovo_ruolo = $_POST['nuovo_ruolo'];
        $stmt = $conn->prepare("UPDATE UTENTE SET Ruolo = ? WHERE Id_User = ?");
        $stmt->bind_param("si", $nuovo_ruolo, $id_target);
        if ($stmt->execute()) {
            $messaggio = "<div class='msg-success'>Ruolo aggiornato con successo!</div>";
        }
    } elseif ($azione === 'Elimina') {
        try {
           
            $password_bruciata = '***ACCOUNT_ELIMINATO***';
            
            $stmt = $conn->prepare("UPDATE UTENTE SET Password = ? WHERE Id_User = ?");
            $stmt->bind_param("si", $password_bruciata, $id_target);
            $stmt->execute();
            $messaggio = "<div class='msg-success'>Utente disabilitato. I suoi dati e pagamenti sono stati conservati a fini statistici.</div>";
        } catch (Exception $e) {
            $messaggio = "<div class='msg-error'>Impossibile disabilitare l'utente. Riprova.</div>";
        }
    }
}

$query = "SELECT * FROM UTENTE ORDER BY Ruolo, Cognome ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - Admin</title>
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
            text-transform: uppercase;
            font-size: 0.85em;
        }
        .btn-back:hover { background-color: #991b1b; }

        .main-box {
            background: white;
            border: 3px solid #1a2721;
            padding: 20px;
            border-radius: 4px;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white; 
            margin-top: 10px;
        }
        
        th, td { 
            padding: 15px; 
            text-align: left; 
            border: 1px solid #1a2721; 
        }
        
        th { 
            background-color: #1a2721; 
            color: white; 
            text-transform: uppercase; 
            font-size: 0.8em; 
            letter-spacing: 1px; 
        }

        select {
            padding: 8px;
            border: 2px solid #1a2721;
            background: #ffffff;
            font-weight: bold;
            font-family: inherit;
        }

        .btn {
            padding: 8px 15px;
            border: 2px solid #1a2721;
            font-weight: 900;
            text-transform: uppercase;
            cursor: pointer;
            font-size: 0.75em;
            transition: 0.2s;
        }
        
        .btn-salva { background-color: #27ae60; color: white; }
        .btn-salva:hover { background-color: #219653; }
        
        .btn-elimina { background-color: #b91c1c; color: white; }
        .btn-elimina:hover { background-color: #991b1b; }

        .riga-disabilitata { 
            background-color: #e3dccf; 
            color: #7f8c8d; 
        }
        .riga-disabilitata td {
            text-decoration: line-through;
        }
        .status-tag {
            text-decoration: none !important;
            font-weight: 900;
            color: #b91c1c;
            text-transform: uppercase;
            font-size: 0.8em;
        }

        .msg-flat { 
            padding: 15px; 
            border: 2px solid #1a2721; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 25px; 
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Gestione Utenti e Ruoli</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <?php if ($messaggio != ""): ?>
            <div class="msg-flat" style="background-color: <?php echo (strpos($messaggio, 'successo') !== false || strpos($messaggio, 'disabilitato') !== false) ? '#4ade80' : '#f87171'; ?>;">
                <?php echo strip_tags($messaggio); ?>
            </div>
        <?php endif; ?>

        <div class="main-box">
            <table>
                <thead>
                    <tr>
                        <th>Nome e Cognome</th>
                        <th>Email</th>
                        <th>Ruolo Attuale</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        
                        <?php $is_disabilitato = ($row['Password'] === '***ACCOUNT_ELIMINATO***'); ?>
                        
                        <tr class="<?php echo $is_disabilitato ? 'riga-disabilitata' : ''; ?>">
                            <td><strong><?php echo htmlspecialchars($row['Nome'] . " " . $row['Cognome']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            
                            <?php if($is_disabilitato): ?>
                                <td colspan="2" style="text-align: center;">
                                    <span class="status-tag">Account Disabilitato (Ex: <?php echo htmlspecialchars($row['Ruolo']); ?>)</span>
                                </td>
                            <?php else: ?>
                                <td>
                                    <form method="POST" style="display:inline-flex; gap:10px; align-items: center;">
                                        <input type="hidden" name="id_user" value="<?php echo $row['Id_User']; ?>">
                                        <input type="hidden" name="azione" value="AggiornaRuolo">
                                        <select name="nuovo_ruolo">
                                            <option value="Utente Esterno" <?php if($row['Ruolo']=='Utente Esterno') echo 'selected'; ?>>Utente Esterno</option>
                                            <option value="Utente Premium" <?php if($row['Ruolo']=='Utente Premium') echo 'selected'; ?>>Utente Premium</option>
                                            <option value="Socio Cercatore" <?php if($row['Ruolo']=='Socio Cercatore') echo 'selected'; ?>>Socio Cercatore</option>
                                            <option value="Micologo Esperto" <?php if($row['Ruolo']=='Micologo Esperto') echo 'selected'; ?>>Micologo Esperto</option>
                                            <option value="Amministratore" <?php if($row['Ruolo']=='Amministratore') echo 'selected'; ?>>Amministratore</option>
                                        </select>
                                        <button type="submit" class="btn btn-salva">Salva</button>
                                    </form>
                                </td>
                                <td>
                                    <?php if($row['Id_User'] != $_SESSION['id_user']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_user" value="<?php echo $row['Id_User']; ?>">
                                        <input type="hidden" name="azione" value="Elimina">
                                        <button type="submit" class="btn btn-elimina" onclick="return confirm('Sicuro di voler disabilitare questo utente?');">Disabilita</button>
                                    </form>
                                    <?php else: ?>
                                        <span style="font-size: 0.7em; font-weight: bold; text-transform: uppercase;">Sessione Attiva</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>