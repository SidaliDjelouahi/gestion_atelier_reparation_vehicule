<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    $stmt = $pdo->query("
        SELECT v.*, c.nom AS client_nom
        FROM vehicules v
        JOIN clients c ON v.id_client = c.id
        ORDER BY v.id DESC
        LIMIT 100
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nom AS client_nom
        FROM vehicules v
        JOIN clients c ON v.id_client = c.id
        WHERE c.nom LIKE ? OR v.matricule LIKE ? OR v.marque LIKE ? OR v.modele LIKE ?
        ORDER BY v.id DESC
    ");
    $like = "%$q%";
    $stmt->execute([$like, $like, $like, $like]);
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
