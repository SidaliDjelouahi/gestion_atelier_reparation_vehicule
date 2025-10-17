<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

// Sécurité basique : vérifier session si nécessaire
// session_start(); if (!isset($_SESSION['user_id'])) { http_response_code(403); exit; }

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    // retourner les 100 derniers par défaut
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY id DESC LIMIT 100");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    exit;
}

// Requête paramétrée (LIKE)
$like = "%$q%";
$sql = "SELECT * FROM clients WHERE nom LIKE :q OR rc LIKE :q OR telephone LIKE :q OR adresse LIKE :q ORDER BY id DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => $like]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows);
exit;
