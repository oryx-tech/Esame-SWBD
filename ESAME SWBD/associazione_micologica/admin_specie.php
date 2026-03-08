<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Amministratore') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2>Accesso Negato</h2><p>Area riservata alla Direzione.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_specie = intval($_POST['id_specie']);
    $azione = $_POST['azione'];
    
    if ($azione === 'Approva') {
        $stmt = $conn->prepare("UPDATE SPECIE_FUNGINA SET Stato_Approvazione = 'Approvata' WHERE Id_Specie = ?");
        $stmt->bind_param("i", $id_specie);
        if ($stmt->execute()) {
            $messaggio = "Specie validata! Ora e disponibile nel catalogo ufficiale.";
        }
    } elseif ($azione === 'Rifiuta') {
$stmt = $conn->prepare("UPDATE SPECIE_FUNGINA SET Stato_Approvazione = 'Rifiutata' WHERE Id_Specie = ?");      
 $stmt->bind_param("i", $id_specie);
        if ($stmt->execute()) {
            $messaggio = "Specie rifiutata. I micologi vedranno l'esito negativo.";
        }
    }
}

$query_attesa = "SELECT * FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'In attesa' ORDER BY Nome_Scientifico ASC";
$result_attesa = $conn->query($query_attesa);

$query_catalogo = "SELECT * FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'Approvata' ORDER BY Nome_Scientifico ASC";
$result_catalogo = $conn->query($query_catalogo);

$query_rifiutate = "SELECT * FROM SPECIE_FUNGINA WHERE Stato_Approvazione = 'Rifiutata' ORDER BY Nome_Scientifico ASC";
$result_rifiutate = $conn->query($query_rifiutate);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Specie - Associazione Micologica</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4efe6; 
            margin: 0; 
            padding: 20px; 
            color: #1a2721; 
        }
        
        .container { max-width: 1000px; margin: 0 auto; }
        
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
        

        .cat-badge { 
            display: inline-block; 
            padding: 4px 8px; 
            border: 2px solid #1a2721; 
            font-size: 0.75em; 
            font-weight: 900; 
            color: #1a2721; 
            text-transform: uppercase;
        }
        .cat-commestibile { background-color: #4ade80; }
        .cat-velenosa { background-color: #f87171; }
        .cat-medicinale { background-color: #fcd34d; }
        .cat-ornamentale { background-color: #c084fc; }


        .btn { 
            padding: 6px 12px; 
            border: 2px solid #1a2721; 
            font-weight: 900; 
            cursor: pointer; 
            color: white; 
            font-size: 0.8em; 
            text-transform: uppercase;
            transition: 0.2s;
        }
        .btn-approva { background-color: #27ae60; }
        .btn-approva:hover { background-color: #219653; }
        .btn-rifiuta { background-color: #b91c1c; }
        .btn-rifiuta:hover { background-color: #991b1b; }


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
            <h2>Gestione Catalogo Specie Fungine</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <?php if ($messaggio != ""): ?>
            <div class="msg-flat" style="background-color: <?php echo (strpos($messaggio, 'validata') !== false) ? '#4ade80' : '#f87171'; ?>;">
                <?php echo $messaggio; ?>
            </div>
        <?php endif; ?>

        <h3 class="section-title">Specie in attesa di approvazione</h3>
        <?php if ($result_attesa->num_rows > 0): ?>
            <table>
                <thead><tr><th>Nome Scientifico</th><th>Nome Volgare</th><th>Categoria</th><th>Azioni</th></tr></thead>
                <tbody>
                    <?php while($row = $result_attesa->fetch_assoc()): $cat_class = 'cat-' . strtolower($row['Categoria']); ?>
                        <tr>
                            <td><strong><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></strong></td>
                            <td><?php echo htmlspecialchars($row['Nome_Volgare']); ?></td>
                            <td><span class="cat-badge <?php echo $cat_class; ?>"><?php echo $row['Categoria']; ?></span></td>
                            <td style="min-width: 200px;">
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id_specie" value="<?php echo $row['Id_Specie']; ?>">
                                    <input type="hidden" name="azione" value="Approva">
                                    <button type="submit" class="btn btn-approva">Approva</button>
                                </form>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id_specie" value="<?php echo $row['Id_Specie']; ?>">
                                    <input type="hidden" name="azione" value="Rifiuta">
                                    <button type="submit" class="btn btn-rifiuta" onclick="return confirm('Vuoi respingere questa specie?');">Rifiuta</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="font-style: italic; opacity: 0.7;">Nessuna specie in attesa di revisione.</p>
        <?php endif; ?>

        <h3 class="section-title">Catalogo Ufficiale Attivo</h3>
        <?php if ($result_catalogo->num_rows > 0): ?>
            <table>
                <thead><tr><th>Nome Scientifico</th><th>Nome Volgare</th><th>Categoria</th><th>Stato</th></tr></thead>
                <tbody>
                    <?php while($row = $result_catalogo->fetch_assoc()): $cat_class = 'cat-' . strtolower($row['Categoria']); ?>
                        <tr>
                            <td><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></td>
                            <td><strong><?php echo htmlspecialchars($row['Nome_Volgare']); ?></strong></td>
                            <td><span class="cat-badge <?php echo $cat_class; ?>"><?php echo $row['Categoria']; ?></span></td>
                            <td><strong style="color: #27ae60; text-transform: uppercase; font-size: 0.8em;">Approvata</strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h3 class="section-title" style="color: #b91c1c;">Archivio Proposte Rifiutate</h3>
        <?php if ($result_rifiutate->num_rows > 0): ?>
            <table style="opacity: 0.8;">
                <thead><tr><th>Nome Scientifico</th><th>Nome Volgare</th><th>Categoria</th><th>Stato</th></tr></thead>
                <tbody>
                    <?php while($row = $result_rifiutate->fetch_assoc()): $cat_class = 'cat-' . strtolower($row['Categoria']); ?>
                        <tr>
                            <td><del><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></del></td>
                            <td><del><?php echo htmlspecialchars($row['Nome_Volgare']); ?></del></td>
                            <td><span class="cat-badge" style="background-color:#e3dccf; color:#1a2721; border-color:#1a2721;"><?php echo $row['Categoria']; ?></span></td>
                            <td><strong style="color: #b91c1c; text-transform: uppercase; font-size: 0.8em;">Rifiutata</strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
             <p style="font-style: italic; opacity: 0.7;">Nessuna proposta rifiutata.</p>
        <?php endif; ?>

    </div>
</body>
</html>