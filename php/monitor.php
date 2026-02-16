<?php
require_once('db-connect.php');

$stmt = $pdo->query("SELECT id, url FROM sites");
$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);   

foreach($sites as $site) {
    $site_id = $site['id'];    
    $url = $site['url'];
           
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Kövesse az átirányításokat
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);          // Maximum 5 ugrást engedélyezzen
    //  valódi böngészőnek tettetés
    $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

    $start = microtime(true);
    curl_exec($ch);
    $duration = round(microtime(true) - $start, 3);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $is_up = ($status_code == 200) ? 1 : 0;     

    $stmt = $pdo->prepare("INSERT INTO incidents (site_id, status_code, response_time, is_up) VALUES (?, ?, ?, ?)");
    $stmt->execute([$site_id, $status_code, $duration, $is_up]);
    curl_close($ch);
}
// 30 napnál régebbi adatok törlése
try {
// eredeti mérések: 30 napnál régebbiek törlése
$stmt1 = $pdo->prepare("DELETE FROM incidents WHERE checked_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt1->execute();

// archivált mérések: 60 napnál régebbiek törlése
$stmt2 = $pdo->prepare("DELETE FROM incidents_old WHERE checked_at < DATE_SUB(NOW(), INTERVAL 60 DAY)");
$stmt2->execute();

// Opcionális: Táblák optimalizálása (felszabadítja a lefoglalt, de üres helyet)        
// $pdo->query("OPTIMIZE TABLE incidents, incidents_old");
} catch (PDOException $e) {
    // Hiba esetén naplózzuk a cron.log-ba
    echo "[" . date('Y-m-d H:i:s') . "] Takarítási hiba: " . $e->getMessage() . "\n";
}   
header("Location: ../index.php");
exit;
?>