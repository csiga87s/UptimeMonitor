<?php
require_once('db-connect.php');

$error_message="";

if (isset($_POST['add_site'])) {
    #URL megtisztítása mielőbb kiírom az adatbázisba
    $url = trim($_POST['url']);
    $url = rtrim($url, '/');
    # tényeg érvényes url-e
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error_message = "Hiba: Érvénytelen URL formátum!";
    } else {
        try{
            $stmt = $pdo->prepare("INSERT INTO sites (url) VALUES (?)");
            $stmt->execute([$_POST['url']]);
            header("Location: settings.php?success=1");
            exit;
        }catch(PDOException $e){
            # ha az oldal már szerepel, akkor hibát dob mivel az url mező UNIQUE az adatbázisban
            if ($e->getCode() == 23000) {
                $error_message = "Hiba: Ez a weboldal már szerepel a listában!";
            } else {
                $error_message = "Váratlan hiba történt: " . $e->getMessage();
            }
        }
    }    
}

if (isset($_GET['delete_site'])) {
    $id = (int)$_GET['delete_site'];
    try {
        $pdo->beginTransaction();

        // 1. Átmásoljuk az alapinfókat
        $stmt1 = $pdo->prepare("INSERT INTO sites_old (id, url) 
                                SELECT id, url FROM sites WHERE id = ?");
        $stmt1->execute([$id]);

        // 2. Átmásoljuk az összes mérést (INSERT INTO ... SELECT)
        $stmt2 = $pdo->prepare("INSERT INTO incidents_old (site_id, status_code, response_time, checked_at, is_up) 
                                SELECT site_id, status_code, response_time, checked_at, is_up 
                                FROM incidents WHERE site_id = ?");
        $stmt2->execute([$id]);

        // 3. Törlés (A CASCADE miatt az incidents-ből magától kimegy, ha beállítottad a Foreign Key-t)
        $stmt3 = $pdo->prepare("DELETE FROM sites WHERE id = ?");
        $stmt3->execute([$id]);

        $pdo->commit();
        header("Location: settings.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Hiba az archiválás során: " . $e->getMessage());
    }
}
$sites = $pdo->query("SELECT * FROM sites")->fetchAll();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <title>Beállítások</title>
</head>
<body class="container mt-5">
    <h2>Monitorozott oldalak kezelése</h2>    
    <div class="card p-4 mb-4 shadow-sm">
        <form method="POST" class="row g-3">
            <div class="col-md-5">
                <input type="url" name="url" class="form-control" placeholder="https://pelda.hu" required>
            </div>            
            <div class="col-md-2">
                <button type="submit" name="add_site" class="btn btn-success w-100">Hozzáadás</button>
            </div>
        </form>
    </div>

    <?php if ($error_message): ?>
      <div class="alert alert-danger shadow-sm"><?= $error_message ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success shadow-sm">Weboldal sikeresen hozzáadva!</div>
    <?php endif; ?>                    

    <table class="table table-hover shadow-sm">
        <thead class="table-dark">
            <tr>                
                <th>URL</th>
                <th>Művelet</th>
            </tr>
        </thead>
        <tbody>
            <?php if($sites): foreach ($sites as $site): ?>
            <tr>                
                <td><?= htmlspecialchars($site['url']) ?></td>
                <td>
                    <a href="?delete_site=<?= $site['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Biztos törlöd? Az oldalhoz tartozó statisztika archiválásra kerül!')">Törlés</a>
                </td>
            </tr>
            <?php endforeach;
            else:?>
            <td colspan="5" class="align-middle text-center link-danger">
                    Jelenleg nincs figyelt oldal!                    
            </td>
            <?php endif;?>
        </tbody>
    </table>
    <a href="../index.php" class="btn btn-secondary">Vissza a Dashboardra</a>
</body>
</html>