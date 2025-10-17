<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search === '') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, ref, designation, prix_vente_ht FROM pieces 
                       WHERE ref LIKE ? OR designation LIKE ? 
                       ORDER BY designation LIMIT 10");
$stmt->execute(["%$search%", "%$search%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
