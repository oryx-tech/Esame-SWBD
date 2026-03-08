<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['ruolo'] !== 'Amministratore') {
    die("<div style='text-align:center; margin-top:50px; font-family:Arial;'><h2>Accesso Negato</h2><p>Area riservata alla Direzione (Amministratore).</p><a href='index.php'>Torna alla Dashboard</a></div>");
}

$stats_categorie = [
    'Commestibile' => ['kg' => 0, 'num' => 0, 'bg' => '#4ade80', 'color' => '#1a2721'],
    'Velenosa'     => ['kg' => 0, 'num' => 0, 'bg' => '#f87171', 'color' => '#1a2721'],
    'Medicinale'   => ['kg' => 0, 'num' => 0, 'bg' => '#fcd34d', 'color' => '#1a2721'],
    'Ornamentale'  => ['kg' => 0, 'num' => 0, 'bg' => '#c084fc', 'color' => '#1a2721']
];

$query_categorie = "
    SELECT s.Categoria, SUM(r.Quantita_Peso) AS Totale_Kg, COUNT(r.Id_Raccolta) AS Numero_Ritrovamenti
    FROM RACCOLTA r
    JOIN CERTIFICAZIONE c ON r.Id_Raccolta = c.Id_Raccolta
    JOIN SPECIE_FUNGINA s ON c.Id_Specie_Reale = s.Id_Specie
    WHERE r.Stato_Lavorazione = 'Certificata' 
    GROUP BY s.Categoria
";
$result_categorie = $conn->query($query_categorie);

while($row = $result_categorie->fetch_assoc()) {
    $cat = $row['Categoria'];
    if(isset($stats_categorie[$cat])) {
        $stats_categorie[$cat]['kg'] = $row['Totale_Kg'];
        $stats_categorie[$cat]['num'] = $row['Numero_Ritrovamenti'];
    }
}

$query_micologi = "
    SELECT u.Nome, u.Cognome, COUNT(c.Id_Certificato) AS Totale_Certificazioni
    FROM UTENTE u
    JOIN CERTIFICAZIONE c ON u.Id_User = c.Id_User_Micologo
    GROUP BY u.Id_User
    ORDER BY Totale_Certificazioni DESC
";
$result_micologi = $conn->query($query_micologi);

$query_soci = "
    SELECT 
        u.Nome, 
        u.Cognome,
        COUNT(r.Id_Raccolta) AS Totale_Raccolte,
        SUM(CASE WHEN r.Id_Specie_Presunta != c.Id_Specie_Reale THEN 1 ELSE 0 END) AS Totale_Errori
    FROM UTENTE u
    JOIN RACCOLTA r ON u.Id_User = r.Id_User_Socio
    JOIN CERTIFICAZIONE c ON r.Id_Raccolta = c.Id_Raccolta
    WHERE r.Stato_Lavorazione = 'Certificata'
    GROUP BY u.Id_User
    HAVING Totale_Raccolte > 0
    ORDER BY Totale_Errori ASC, Totale_Raccolte DESC
";
$result_soci = $conn->query($query_soci);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Report e Statistiche - Associazione Micologica</title>
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

        .cat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .cat-box { 
            padding: 25px; 
            border: 3px solid #1a2721; 
            border-radius: 4px; 
            text-align: center; 
        }
        .cat-box h3 { margin: 0; font-size: 0.8em; text-transform: uppercase; letter-spacing: 1px; font-weight: 900; }
        .cat-box h1 { margin: 10px 0; font-size: 2.2em; font-weight: 900; }
        .cat-box p { margin: 0; font-size: 0.85em; font-weight: bold; }

        .grid-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { 
            background: white; 
            padding: 25px; 
            border: 3px solid #1a2721; 
            border-radius: 4px; 
        }
        .card h3 { 
            font-family: 'Georgia', serif; 
            margin-top: 0; 
            color: #1a2721; 
            border-bottom: 3px solid #f4efe6; 
            padding-bottom: 10px; 
            font-size: 1.4em;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border: 1px solid #1a2721; }
        th { background-color: #1a2721; color: white; text-transform: uppercase; font-size: 0.75em; letter-spacing: 1px; }
        tr:hover { background-color: #f4efe6; }
        
        .bravo { color: #27ae60; font-weight: 900; }
        .da-migliorare { color: #b91c1c; font-weight: 900; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Centro Statistiche e Reportistica</h2>
            <a href="index.php" class="btn-back">Indietro</a>
        </div>

        <h3 style="font-family: 'Georgia', serif; color: #1a2721; margin-bottom: 20px;">Riepilogo Ritrovamenti Certificati</h3>
        
        <div class="cat-grid">
            <?php foreach($stats_categorie as $nome_cat => $dati): ?>
                <div class="cat-box" style="background-color: <?php echo $dati['bg']; ?>; color: <?php echo $dati['color']; ?>;">
                    <h3><?php echo strtoupper($nome_cat); ?></h3>
                    <h1><?php echo number_format($dati['kg'] ?? 0, 2, ',', '.'); ?> Kg</h1>
                    <p>Totale: <?php echo $dati['num']; ?> certificazioni</p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="grid-stats">
            <div class="card">
                <h3>Produttivita Micologi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Micologo Esperto</th>
                            <th>Pratiche Evase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_micologi->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['Nome'] . " " . $row['Cognome']); ?></strong></td>
                            <td><?php echo $row['Totale_Certificazioni']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Precisione Soci</h3>
                <p style="font-size: 0.85em; font-weight: bold; color: #1a2721; margin-bottom: 15px;">Accuratezza dei riconoscimenti confermati.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Socio</th>
                            <th>Raccolte</th>
                            <th>Errori</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_soci->fetch_assoc()): 
                            $errori = $row['Totale_Errori'];
                            $totale = $row['Totale_Raccolte'];
                            $perc_errore = ($totale > 0) ? round(($errori / $totale) * 100, 1) : 0;
                            $perc_esatta = 100 - $perc_errore;
                            $classe_colore = ($perc_esatta >= 80) ? 'bravo' : 'da-migliorare';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['Nome'] . " " . $row['Cognome']); ?></strong></td>
                            <td><?php echo $totale; ?></td>
                            <td style="color: #b91c1c; font-weight: bold;"><?php echo $errori; ?></td>
                            <td class="<?php echo $classe_colore; ?>"><?php echo $perc_esatta; ?>%</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>