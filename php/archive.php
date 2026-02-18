<?php
require_once ('db-connect.php');

$query = " SELECT s.id, s.url, s.deleted_at,
        COUNT(i.id) as total_checks,
        SUM(i.is_up) as up_count,
        MIN(i.checked_at) as monitor_start,
        MAX(i.checked_at) as monitor_end,                    
        ROUND(AVG(i.response_time), 3) as avg_response_time
        FROM sites_old s
        LEFT JOIN incidents_old i ON s.id = i.site_id
        GROUP BY s.id, s.url, s.deleted_at  -- Itt az ID a kulcs, így minden törlési esemény külön sor
        ORDER BY s.url ASC, s.deleted_at DESC -- URL szerint csoportosítva, időrendben csökkenve"; 

$archived = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Archiv mérések</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<!-- HTML / Bootstrap Táblázat -->
<div class="container mt-5">
    <h2>Korábbi, törölt mérések (az elmúlt 60 nap)</h2>
    <table class="table shadow">
        <thead class="table-dark">
            <tr>
                <th>Név / URL</th>
                <th>Időszak</th>
                <th>Elérhetőség</th>                
                <th>Átlagos válaszidő</th>
                <th>Mérések / Hibák</th>
                <th>Törölve</th>
            </tr>
        </thead>
        <tbody>                                           
            <?php 
                if($archived):
                $current_url = "";
                $form_index=0;
                foreach ($archived as $row): 
                    $form_index++;
                    $uptime = ($row['total_checks'] > 0) ? round(($row['up_count'] / $row['total_checks']) * 100, 2) : 0;
                    $fails = $row['total_checks'] - $row['up_count'];
                    // Színkódolás az uptime alapján
                    $bg_class = ($uptime >= 99) ? 'bg-success' : ($uptime >= 95 ? 'bg-warning text-dark' : 'bg-danger');                    
                      if ($current_url != $row['url']):              
                        $current_url = $row['url'];?>
                    <tr class="table-light">
                        <td colspan="6"><?= htmlspecialchars($current_url) ?></td>                         
                    </tr>                
                    <?php endif; ?>   
                    <tr>              
                        <td></td>     
                        <td><?= $row['monitor_start'] ?> - <?= $row['monitor_end'] ?></td>                
                        <td>                        
                        <span class="badge <?= $bg_class ?> p-2" style="font-size: 0.9rem;">
                                <?= $uptime ?> %
                            </span>
                        </td>
                        <td><?= $row['avg_response_time'] ?> s </td>
                        <td class="align-middle ">                            
                            <span class="text"><?= $row['total_checks'] ?></span> / 
                            <?php if($uptime<100): ?>                              
                                <!--a javasrcriopt ami kattintás esetén megkeresi a post-form nevű formot 
                                ami elküldi az adatokat -->
                                <a href="javascript:void(0);" 
                                class="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" 
                                onclick="document.getElementById('form-<?=$form_index?>').submit();">
                                <?=$fails?></a>
                                <!-- A rejtett űrlap az adatokkal -->
                                <form id="form-<?=$form_index?>" action="downtime.php" method="POST" style="display: none;">
                                    <input type="hidden" name="id" value="<?=$row['id']?>">
                                    <input type="hidden" name="archive" value="1">
                                </form>
                            <?php else: ?>
                                <span class="text"><?= $fails ?></span>
                            <?php endif; ?>                         
                        </td>                       
                        <td><?= $row['deleted_at']?></td>                        
                    </tr>
                <?php endforeach;
                else:?>
                    <td colspan="5" class="align-middle text-center link-danger">
                        Jelenleg nincs archív/törölt oldal! <br />                        
                    </td>            
                <?php endif;?>
        </tbody>
    </table>
    <a href="../index.php" class="btn btn-secondary">Vissza a Dashboardra</a>
</div>
</body>
</html>