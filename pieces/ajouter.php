<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = trim($_POST['ref']);
    $designation = trim($_POST['designation']);
    $quantite = (int)$_POST['quantite'];
    $prix_achat_ht = !empty($_POST['prix_achat_ht']) ? (float)$_POST['prix_achat_ht'] : null;
    $prix_vente_ht = (float)$_POST['prix_vente_ht'];

    if ($ref && $designation) {
        $stmt = $pdo->prepare("
            INSERT INTO pieces (ref, designation, quantite, prix_achat_ht, prix_vente_ht)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$ref, $designation, $quantite, $prix_achat_ht, $prix_vente_ht]);
    }

    header("Location: " . ROOT_URL . "/pieces/table.php");
    exit;
}
?>
