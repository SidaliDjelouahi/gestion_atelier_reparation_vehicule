<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') exit;

$stmt = $pdo->prepare("
    SELECT id, ref, designation, quantite
    FROM pieces
    WHERE ref LIKE ? OR designation LIKE ?
    ORDER BY designation ASC
    LIMIT 10
");
$stmt->execute(["%$q%", "%$q%"]);

$pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$pieces) {
    echo "<div class='list-group-item text-muted'>Aucun résultat</div>";
} else {
    foreach ($pieces as $p) {
        echo "<button type='button' class='list-group-item list-group-item-action' data-id='{$p['id']}'>
                {$p['designation']} ({$p['quantite']} en stock)
              </button>";
    }
}
?>
