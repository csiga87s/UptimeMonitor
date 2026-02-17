<?php
require_once('db-connect.php');

$query = "SELECT s.url, s.id,
        COUNT(i.id) as total_checks,
        SUM(i.is_up) as up_count,        
        MIN(i.response_time) as min_response_time,
        MAX(i.response_time) as max_response_time,
        ROUND(AVG(i.response_time), 3) as avg_response_time,
        MAX(i.checked_at) as last_check
        FROM sites s
        LEFT JOIN incidents i ON s.id = i.site_id
        WHERE i.checked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR i.id IS NULL
        GROUP BY s.id ";

$stmt = $pdo->query($query);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <h2>Statisztikák (az elmúlt 30 nap)</h2>
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Weboldal neve</th>
                        <th>Uptime (%)</th>
                        <th>Átlag válaszidő</th>
                        <th>Min / Max</th>
                        <th>Összes mérés / Hiba</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $row): 
                        $uptime = ($row['total_checks'] > 0) ? round(($row['up_count'] / $row['total_checks']) * 100, 2) : 0;  
                        $fails = $row['total_checks'] - $row['up_count'];                  
                        // Színkódolás az uptime alapján
                        $bg_class = ($uptime >= 99) ? 'bg-success' : ($uptime >= 95 ? 'bg-warning text-dark' : 'bg-danger');?>
                    <tr>
                        <td> <!-- Ha nem 100% az elérhetőség, akkor az URL egy link legyen, ahol meg 
                                lehet nézni, mikor volt az és milyen hibakóddal -->
                        <?php if($uptime<100): ?> 
                            <a href="downtime.php?id=<?=$row['id'] ?>"
                            class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">
                            <?= htmlspecialchars($row['url']) ?></a>
                        <?php else: ?>
                            <span class="text"><?= htmlspecialchars($row['url']) ?></span>
                        <?php endif; ?>
                        </td>
                        <td class="align-middle">
                            <span class="badge <?= $bg_class ?> p-2" style="font-size: 0.9rem;">
                                <?= $uptime ?> %
                            </span>
                        </td>
                        <td class="align-middle"><?= $row['avg_response_time'] ?> s</td>
                        <td class="align-middle small">
                            <span class="text-success"><?= $row['min_response_time'] ?> s</span> / 
                            <span class="text-danger"><?= $row['max_response_time'] ?> s</span>
                        </td>
                        <td class="align-middle ">
                            <span class="text"><?= $row['total_checks'] ?></span> / 
                            <span class="text-danger"><?= $fails; ?></span>                   
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    </div>  
    <br />
    <a href="../index.php" class="btn btn-secondary">Vissza a Dashboardra</a>  
</div>
</body>
</html>