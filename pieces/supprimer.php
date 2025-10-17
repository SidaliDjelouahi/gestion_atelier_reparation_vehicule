<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Vérifier si la référence est passée
if (!isset($_GET['id'])) {
    die("ID manquante !");
}

$id = $_GET['id'];

// 🔍 Vérifier si la pièce est utilisée dans un bon d'intervention
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bons_intervention_details WHERE id_piece = ?");
$stmt->execute([$id]);
$usedCount = $stmt->fetchColumn();

if ($usedCount > 0) {
    // ⚠️ Si utilisée, message d’erreur
    echo "<script>
        alert('Impossible de supprimer cette pièce : elle est utilisée dans un ou plusieurs bons d\\'intervention.');
        window.location.href = '" . ROOT_URL . "/pieces/table.php';
    </script>";
    exit;
}

// ✅ Sinon, suppression
$stmt = $pdo->prepare("DELETE FROM pieces WHERE id = ?");
$stmt->execute([$id]);

header("Location: " . ROOT_URL . "/pieces/table.php");
exit;
?>
