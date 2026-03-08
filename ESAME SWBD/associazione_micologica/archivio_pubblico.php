<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggato']) || $_SESSION['loggato'] !== true) {
    header("Location: login.php");
    exit();
}

$filtro_categoria = "";
$query_where = "";

if (isset($_GET['categoria']) && $_GET['categoria'] != '') {
    $filtro_categoria = $conn->real_escape_string($_GET['categoria']);
    $query_where = " AND s.Categoria = '$filtro_categoria' ";
}


$query = "
    SELECT 
        r.Data_Raccolta, 
        r.Luogo, 
        r.URL_Foto, 
        r.Quantita_Peso,
        u.Nome AS Nome_Socio, 
        u.Cognome AS Cognome_Socio, 
        s.Nome_Scientifico, 
        s.Nome_Volgare, 
        s.Categoria,
        c.Esito,
        m.Nome AS Nome_Micologo,
        m.Cognome AS Cognome_Micologo
    FROM RACCOLTA r
    JOIN CERTIFICAZIONE c ON r.Id_Raccolta = c.Id_Raccolta
    JOIN UTENTE u ON r.Id_User_Socio = u.Id_User
    JOIN SPECIE_FUNGINA s ON c.Id_Specie_Reale = s.Id_Specie
    JOIN UTENTE m ON c.Id_User_Micologo = m.Id_User
    WHERE r.Stato_Lavorazione = 'Certificata'
    $query_where
    ORDER BY r.Data_Raccolta DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Archivio Pubblico - Associazione Micologica</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4efe6; margin: 0; padding: 20px; color: #1a2721; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #cb8b22; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { font-family: 'Georgia', serif; color: #1a2721; margin: 0; }
        
        .btn-back { background-color: #b91c1c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; border: 2px solid #7f1d1d; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { background-color: #991b1b; }
        
        .filter-box { background: white; padding: 15px; border-radius: 4px; border: 3px solid #1a2721; margin-bottom: 20px; }
        .filter-box select, .filter-box button { padding: 8px; border-radius: 0; border: 2px solid #1a2721; font-weight: bold; }
        .filter-box button { background-color: #cb8b22; color: white; cursor: pointer; transition: 0.2s; }
        .filter-box button:hover { background-color: #b3791d; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 4px; overflow: hidden; border: 3px solid #1a2721; transition: 0.2s; }
        .card:hover { transform: translateY(-3px); } 
        
        .card-img { width: 100%; height: 200px; background-color: #e3dccf; border-bottom: 3px solid #1a2721; object-fit: cover; display: flex; justify-content: center; align-items: center; color: #1a2721; font-weight: bold; }
        
        .card-body { padding: 15px; }
        .card-title { margin: 0 0 10px 0; color: #1a2721; font-size: 1.3em; font-family: 'Georgia', serif; border-bottom: 2px solid #e3dccf; padding-bottom: 5px; }
        
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; color: #1a2721; font-weight: 900; text-transform: uppercase; margin-bottom: 10px; border: 2px solid #1a2721; }
        .badge-commestibile { background-color: #4ade80; }
        .badge-velenosa { background-color: #f87171; }
        .badge-medicinale { background-color: #fcd34d; }
        .badge-ornamentale { background-color: #c084fc; }
        
        .card-text { margin: 5px 0; font-size: 0.9em; }
        
        .esito-box { margin-top: 15px; padding: 10px; background-color: #f4efe6; border: 2px solid #1a2721; border-left: 5px solid #cb8b22; font-size: 0.85em; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Archivio Ritrovamenti Ufficiali</h2>
            <a href="index.php" class="btn-back">⬅ Torna alla Dashboard</a>
        </div>

        <div class="filter-box">
            <form method="GET" action="archivio_pubblico.php">
                <label for="categoria"><strong>Filtra per Categoria:</strong></label>
                <select name="categoria" id="categoria">
                    <option value="">Tutte le categorie</option>
                    <option value="Commestibile" <?php if($filtro_categoria == 'Commestibile') echo 'selected'; ?>>Commestibile</option>
                    <option value="Velenosa" <?php if($filtro_categoria == 'Velenosa') echo 'selected'; ?>>Velenosa</option>
                    <option value="Medicinale" <?php if($filtro_categoria == 'Medicinale') echo 'selected'; ?>>Medicinale</option>
                    <option value="Ornamentale" <?php if($filtro_categoria == 'Ornamentale') echo 'selected'; ?>>Ornamentale</option>
                </select>
                <button type="submit">Applica Filtro</button>
                <a href="archivio_pubblico.php" style="margin-left: 10px; text-decoration: none; color: #e74c3c; font-size: 0.9em;">Resetta</a>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid">
                <?php while($row = $result->fetch_assoc()): 
                    $badge_class = 'badge-' . strtolower($row['Categoria']);
                ?>
                    <div class="card">
                        <?php if(!empty($row['URL_Foto']) && file_exists($row['URL_Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['URL_Foto']); ?>" class="card-img" alt="Foto Fungo">
                        <?php else: ?>
                            <div class="card-img">Nessuna foto disponibile</div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h3 class="card-title"><i><?php echo htmlspecialchars($row['Nome_Scientifico']); ?></i></h3>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['Categoria']); ?></span>
                            
                            <p class="card-text"><b>Nome comune:</b> <?php echo htmlspecialchars($row['Nome_Volgare']); ?></p>
                            <p class="card-text"><b>Trovato da:</b> <?php echo htmlspecialchars($row['Nome_Socio'] . " " . $row['Cognome_Socio']); ?></p>
                            <p class="card-text"><b>Data:</b> <?php echo date('d/m/Y', strtotime($row['Data_Raccolta'])); ?></p>
                            <p class="card-text"><b>Luogo:</b> <?php echo htmlspecialchars($row['Luogo']); ?> (<?php echo $row['Quantita_Peso']; ?> Kg)</p>
                            
                            <div class="esito-box">
                                <b>Esito Micologo:</b> <?php echo htmlspecialchars($row['Esito']); ?><br>
                                <i>Certificato da: <?php echo htmlspecialchars($row['Nome_Micologo'] . " " . $row['Cognome_Micologo']); ?></i>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; background: white; border-radius: 8px;">Nessun ritrovamento certificato corrisponde ai criteri di ricerca.</p>
        <?php endif; ?>

    </div>
</body>
</html>