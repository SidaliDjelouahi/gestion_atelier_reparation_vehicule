<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) {
    header("Location: " . ROOT_URL . "/pieces/table.php");
    exit;
}

// Récupération de la pièce
$stmt = $pdo->prepare("SELECT * FROM pieces WHERE id = ?");
$stmt->execute([$id]);
$piece = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$piece) {
    die("Pièce introuvable !");
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">
    <h4>Détails de la pièce : <?= htmlspecialchars($piece['designation']); ?></h4>
    <table class="table table-bordered mt-3 w-50">
        <tr><th>Référence</th><td><?= htmlspecialchars($piece['ref']); ?></td></tr>
        <tr><th>Désignation</th><td><?= htmlspecialchars($piece['designation']); ?></td></tr>
        <tr><th>Quantité</th><td><?= htmlspecialchars($piece['quantite']); ?></td></tr>
        <tr><th>Prix Achat HT</th><td><?= htmlspecialchars($piece['prix_achat_ht']); ?></td></tr>
        <tr><th>Prix Vente HT</th><td><?= htmlspecialchars($piece['prix_vente_ht']); ?></td></tr>
    </table>

    <div class="mt-3">
        <a href="<?= ROOT_URL; ?>/pieces/modifier.php?id=<?= $piece['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="<?= ROOT_URL; ?>/pieces/supprimer.php?id=<?= $piece['id']; ?>" class="btn btn-danger" onclick="return confirm('Confirmer la suppression ?');">
            <i class="bi bi-trash"></i> Supprimer
        </a>
        <a href="<?= ROOT_URL; ?>/pieces/table.php" class="btn btn-secondary">Retour</a>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
