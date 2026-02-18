<?php
require_once('./php/db-connect.php');

// lekérem minden URL-hez a legfrissebb mérést
$query = "SELECT s.url, i.status_code, i.response_time, i.checked_at, i.is_up 
            FROM sites s
            LEFT JOIN incidents i ON i.site_id = s.id 
            WHERE i.id IN (SELECT MAX(id) FROM incidents WHERE site_id = s.id)
            OR i.id IS NULL";
    
$stmt = $pdo->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);    
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szerver Monitor Dashboard</title>
<!-- Bootstrap CSS a gyors és szép kinézethez -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding-top: 50px; }
        .status-up { color: #198754; font-weight: bold; }
        .status-down { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4 text-center">Szerver Elérhetőség</h2>        
    <div class="card shadow">        
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Weboldal</th>
                        <th>Állapot</th>
                        <th>Válaszkód</th>
                        <th>Válaszidő</th>
                        <th>Utolsó ellenőrzés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($results): foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['url']); ?></td>
                        <td>
                            <?php if ($row['is_up']): ?>
                                <span class="status-up">● ONLINE</span>
                            <?php else: ?>
                                <span class="status-down">● OFFLINE</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['status_code']; ?></td>
                        <td><?php echo $row['response_time']; ?> mp</td>
                        <td><?php echo $row['checked_at']; ?></td>
                    </tr>
                    <?php 
                        endforeach;
                    else:?>
                        <td colspan="5" class="align-middle text-center link-danger">
                            Jelenleg nincs figyelt oldal! <br />
                            Az oldalak szerkesztésénél tudsz hozzáadni.
                        </td>
                    <?php 
                    endif;?>
                </tbody>
            </table>
        </div>
    </div>        
    <br />
   <div class="text-center mb-3">               
        <a href="./php/monitor.php" class="btn btn-secondary shadow-sm"> Kézi futtatás</a>    
        <a href="./php/statistics.php" class="btn btn-secondary  shadow-sm">Statisztikák megnézése</a>        
        <a href="./php/settings.php" class="btn btn-secondary  shadow-sm">Oldalak szerkesztése</a>                 
        <a href="./php/archive.php" class="btn btn-secondary  shadow-sm">Korábbi (törölt) mérések</a>
    </div>
</div>
</body>
</html>