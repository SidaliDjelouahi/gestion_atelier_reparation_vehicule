<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . ROOT_URL . "/vehicules/table.php");
    exit;
}

$id = (int)$_GET['id'];

// Récupérer le client avant suppression
$stmt = $pdo->prepare("SELECT id_client FROM vehicules WHERE id = ?");
$stmt->execute([$id]);
$id_client = $stmt->fetchColumn();

if ($id_client) {
    $pdo->prepare("DELETE FROM vehicules WHERE id = ?")->execute([$id]);
}

header("Location: " . ROOT_URL . "/vehicules/table_client.php?client_id=" . $id_client);
exit;
