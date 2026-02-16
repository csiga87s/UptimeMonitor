<?php
$host = '';
$db   = 'uptime_monitor';
$user = ''; 
$password = '';

try{
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8",$user,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){    
    die(date('Y-m-d H:i:s') ."Hiba az adatbázis kapcsolódáskor: " . $e->getMessage());    
}
?>