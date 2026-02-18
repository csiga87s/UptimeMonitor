<?php
require_once ('db-connect.php');

$id=$_POST['id'];
$is_archive=$_POST['archive'];

// Lekérjük az összes hibás mérést, ha a statisticsból kérték akkor incidents és sites-ból
// ha az acvhiv oldalról, akkor az archiv adatok kellenek, akkor a incidens_old és a site_old-ból
if($is_archive==0){
    $query = "SELECT s.url, i.status_code, i.checked_at 
            FROM incidents i
            JOIN sites s ON i.site_id = s.id
            WHERE i.is_up = 0 AND s.id = ?
            ORDER BY s.url, i.checked_at ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $errors = $stmt->fetchAll(PDO::FETCH_ASSOC);    
}else{
    $query = "SELECT s.url, i.status_code, i.checked_at 
            FROM incidents_old i
            JOIN sites_old s ON i.site_id = s.id
            WHERE i.is_up = 0 AND s.id = ?
            ORDER BY s.url, i.checked_at ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $errors = $stmt->fetchAll(PDO::FETCH_ASSOC);   
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>30 napos statisztika</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up"></i> Hibás kérések</h2>        
    </div>
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-danger">
                    <tr>
                        <th>Weboldal</th>
                        <th>Időpont</th>
                        <th>Hiba kód</th>                        
                    </tr>
                </thead>
                <tbody>                    
                        <?php if($errors):
                        foreach ($errors as $row): ?>
                        <tr>
                            <td><?= $row['url'] ?></td>
                            <td><?= $row['checked_at'] ?></td>  
                            <td><?= $row['status_code'] ?></td>                                                        
                        </tr>
                        <?php endforeach;
                        else: echo "<td> Nem volt még mérés.</td>";
                        endif;?>                    
                </tbody>
            </table>
        </div>
    </div>
    <br />
    <?php if($is_archive==0):?>
    <a href="statistics.php" class="btn btn-secondary">Vissza </a>
    <?php else: ?>
    <a href="archive.php" class="btn btn-secondary">Vissza az archiv adatokhoz </a>  
    <?php endif; ?>
</div>
</body>