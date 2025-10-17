<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search === '') {
    echo json_encode([]);
    exit;
}

// Recherche polyvalente : marque, modèle, matricule
$stmt = $pdo->prepare("SELECT id, marque, modele, matricule 
                       FROM vehicules 
                       WHERE marque LIKE ? OR modele LIKE ? OR matricule LIKE ?
                       ORDER BY marque, modele, matricule
                       LIMIT 10");
$like = "%$search%";
$stmt->execute([$like, $like, $like]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
