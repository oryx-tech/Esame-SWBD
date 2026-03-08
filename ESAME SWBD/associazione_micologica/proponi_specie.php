<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Micologo Esperto') {
    die("Accesso Negato");
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_sci = trim($_POST['nome_sci']);
    $nome_vol = trim($_POST['nome_vol']);
    $categoria = $_POST['categoria'];
    $descrizione = trim($_POST['descrizione']);
    
    $stmt = $conn->prepare("INSERT INTO SPECIE_FUNGINA (Nome_Scientifico, Nome_Volgare, Categoria, Descrizione, Stato_Approvazione) VALUES (?, ?, ?, ?, 'In attesa')");
    $stmt->bind_param("ssss", $nome_sci, $nome_vol, $categoria, $descrizione);
    
    if ($stmt->execute()) {
        $messaggio = "Richiesta inviata! L'Amministratore valutera l'inserimento nel dizionario.";
    } else {
        $messaggio = "Errore durante l'invio.";
    }
}


$query_attesa = "SELECT * FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'In attesa' ORDER BY Nome_Scientifico ASC";
$result_attesa = $conn->query($query_attesa);


$query_approvate = "SELECT * FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'Approvata' ORDER BY Nome_Scientifico ASC";
$result_approvate = $conn->query($query_approvate);


$query_rifiutate = "SELECT * FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'Rifiutata' ORDER BY Nome_Scientifico ASC";
$result_rifiutate = $conn->query($query_rifiutate);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Proponi Nuova Specie</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            padding: 20px; 
            color: #1a2721; 
        }
        
        .container { max-width: 850px; margin: 0 auto; }
        
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
            background: #ffffff; 
            padding: 30px; 
            border-radius: 4px; 
            border: 3px solid #1a2721; 
            margin-bottom: 40px;
        }
        
        .main-box p { font-weight: bold; margin-bottom: 25px; line-height: 1.5; }

        .form-group { margin-bottom: 20px; }
        label { font-weight: 900; display: block; margin-bottom: 8px; color: #1a2721; text-transform: uppercase; font-size: 0.8em; }
        
        input[type="text"], select, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #1a2721; 
            border-radius: 0; 
            box-sizing: border-box; 
            font-weight: bold;
            background-color: #ffffff;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #cb8b22; }
        
        button[type="submit"] { 
            width: 100%; 
            padding: 15px; 
            background-color: #1a2721; 
            color: white; 
            border: 2px solid transparent; 
            border-radius: 0; 
            font-size: 1.1em; 
            font-weight: 900; 
            text-transform: uppercase;
            cursor: pointer; 
            transition: 0.2s; 
        }
        button[type="submit"]:hover { background-color: #2c3e50; }

        .section-title { 
            font-family: 'Georgia', serif; 
            font-size: 1.4em; 
            color: #1a2721; 
            border-bottom: 3px solid #1a2721; 
            padding-bottom: 10px; 
            margin-top: 40px; 
            margin-bottom: 20px;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white; 
            border: 3px solid #1a2721; 
            margin-bottom: 30px; 
        }
        th, td { padding: 12px 15px; text-align: left; border: 1px solid #1a2721; }
        th { background-color: #1a2721; color: white; text-transform: uppercase; font-size: 0.8em; letter-spacing: 1px; }
        tr:hover { background-color: #f4efe6; }
        
        .badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border: 2px solid #1a2721; 
            font-size: 0.75em; 
            font-weight: 900; 
            color: #1a2721; 
            text-transform: uppercase;
        }
        .status-attesa { background-color: #fcd34d; }
        .status-approvata { background-color: #4ade80; } 
        .status-bocciata { background-color: #f87171; }

        .msg-flat { 
            padding: 15px; 
            border: 2px solid #1a2721; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Proponi Nuova Specie</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <div class="main-box">
            <p>Hai individuato un fungo non presente nel catalogo durante una validazione? Compila i dati per richiederne l'aggiunta ufficiale.</p>
            
            <?php if ($messaggio != ""): ?>
                <div class="msg-flat" style="background-color: <?php echo (strpos($messaggio, 'inviata') !== false) ? '#4ade80' : '#f87171'; ?>;">
                    <?php echo $messaggio; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nome Scientifico (Latino):</label>
                    <input type="text" name="nome_sci" placeholder="es. Amanita muscaria" required>
                </div>
                
                <div class="form-group">
                    <label>Nome Volgare (Comune):</label>
                    <input type="text" name="nome_vol" placeholder="es. Ovolo malefico" required>
                </div>
                
                <div class="form-group">
                    <label>Categoria:</label>
                    <select name="categoria" required>
                        <option value="Commestibile">Commestibile</option>
                        <option value="Velenosa">Velenosa</option>
                        <option value="Medicinale">Medicinale</option>
                        <option value="Ornamentale">Ornamentale</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Breve Descrizione / Note Diagnostiche:</label>
                    <textarea name="descrizione" rows="4" placeholder="Caratteristiche morfologiche principali..." required></textarea>
                </div>
                
                <button type="submit">Invia Proposta alla Direzione</button>
            </form>
        </div>

        <h3 class="section-title">Proposte in attesa di giudizio</h3>
        <?php if ($result_attesa->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome Scientifico</th>
                        <th>Categoria</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result_attesa->fetch_assoc()): ?>
                        <tr>
                            <td><strong><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></strong></td>
                            <td><?php echo htmlspecialchars($row['Categoria']); ?></td>
                            <td><span class="badge status-attesa">In attesa</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="font-style: italic; opacity: 0.7;">Nessuna proposta in sospeso.</p>
        <?php endif; ?>

        <h3 class="section-title" style="color: #27ae60;">Proposte Approvate (Ora a Catalogo)</h3>
        <?php if ($result_approvate->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome Scientifico</th>
                        <th>Categoria</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result_approvate->fetch_assoc()): ?>
                        <tr>
                            <td><strong><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></strong></td>
                            <td><?php echo htmlspecialchars($row['Categoria']); ?></td>
                            <td><span class="badge status-approvata">Approvata</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="font-style: italic; opacity: 0.7;">Nessuna proposta approvata finora.</p>
        <?php endif; ?>

        <h3 class="section-title" style="color: #b91c1c;">Proposte Bocciate</h3>
        <?php if ($result_rifiutate->num_rows > 0): ?>
            <table style="opacity: 0.8;">
                <thead>
                    <tr>
                        <th>Nome Scientifico</th>
                        <th>Categoria</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result_rifiutate->fetch_assoc()): ?>
                        <tr>
                            <td><del><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></del></td>
                            <td><?php echo htmlspecialchars($row['Categoria']); ?></td>
                            <td><span class="badge status-bocciata">Rifiutata</span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="font-style: italic; opacity: 0.7;">Nessuna proposta bocciata finora.</p>
        <?php endif; ?>

    </div>
</body>
</html>