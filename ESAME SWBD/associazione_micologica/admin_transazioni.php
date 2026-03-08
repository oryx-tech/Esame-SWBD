<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Amministratore') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2>Accesso Negato</h2><p>Solo gli Amministratori possono accedere all'area contabile.</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$query_transazioni = "
    SELECT p.Id_Pagamento, p.Data_Transazione, p.Importo, p.Tipo_Servizio, u.Nome, u.Cognome, u.Email 
    FROM PAGAMENTO p
    JOIN UTENTE u ON p.Id_User_Cliente = u.Id_User
    ORDER BY p.Data_Transazione DESC
";
$result = $conn->query($query_transazioni);

$totale_fatturato = 0;
$transazioni = [];
while ($row = $result->fetch_assoc()) {
    $transazioni[] = $row;
    $totale_fatturato += $row['Importo']; 
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Storico Transazioni - Associazione Micologica</title>
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

        .box-totale { 
            background-color: #27ae60; 
            color: white; 
            padding: 30px; 
            border: 3px solid #1a2721;
            border-radius: 4px; 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .box-totale h3 { 
            margin: 0; 
            font-size: 1em; 
            font-weight: 900; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .box-totale .cifra { 
            font-size: 3.5em; 
            font-weight: 900; 
            margin: 10px 0 0 0; 
        }

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

        tr:hover { background-color: #f4efe6; }

        .badge-servizio { 
            background-color: #fcd34d; 
            color: #1a2721; 
            padding: 4px 10px; 
            border: 2px solid #1a2721;
            font-size: 0.75em; 
            font-weight: 900; 
            text-transform: uppercase;
        }

        .importo { 
            font-weight: 900; 
            color: #1a2721; 
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Storico Transazioni e Fatturato</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <div class="box-totale">
            <h3>Totale Fatturato Globale</h3>
            <p class="cifra">EUR <?php echo number_format($totale_fatturato, 2, ',', '.'); ?></p>
        </div>

        <div class="main-box">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Servizio</th>
                        <th>Importo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transazioni) > 0): ?>
                        <?php foreach ($transazioni as $t): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($t['Id_Pagamento']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($t['Data_Transazione'])); ?></td>
                                <td><?php echo htmlspecialchars($t['Nome'] . ' ' . $t['Cognome']); ?></td>
                                <td><?php echo htmlspecialchars($t['Email']); ?></td>
                                <td><span class="badge-servizio"><?php echo htmlspecialchars($t['Tipo_Servizio']); ?></span></td>
                                <td class="importo">€ <?php echo number_format($t['Importo'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; font-style: italic; opacity: 0.7;">Nessuna transazione registrata nel sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>