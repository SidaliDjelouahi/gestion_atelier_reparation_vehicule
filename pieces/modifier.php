<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Vérification si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = $_POST['ref'];
    $designation = trim($_POST['designation']);
    $quantite = (float) $_POST['quantite'];
    $prix_achat_ht = $_POST['prix_achat_ht'] !== '' ? (float) $_POST['prix_achat_ht'] : null;
    $prix_vente_ht = (float) $_POST['prix_vente_ht'];

    if ($designation && $quantite >= 0) {
        $stmt = $pdo->prepare("UPDATE pieces 
                               SET designation = ?, quantite = ?, prix_achat_ht = ?, prix_vente_ht = ? 
                               WHERE ref = ?");
        $stmt->execute([$designation, $quantite, $prix_achat_ht, $prix_vente_ht, $ref]);
    }

    header("Location: " . ROOT_URL . "/pieces/table.php");
    exit;
}

// Si on veut modifier une pièce
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM pieces WHERE id = ?");
    $stmt->execute([$id]);
    $piece = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$piece) {
        die("Pièce non trouvée !");
    }
} else {
    die("ID manquante !");
}
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container py-4">
    <h4>Modifier la pièce : <?= htmlspecialchars($piece['designation']); ?></h4>
    <form action="" method="POST" class="mt-3">
        <input type="hidden" name="ref" value="<?= htmlspecialchars($piece['ref']); ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Désignation *</label>
                <input type="text" name="designation" class="form-control" required value="<?= htmlspecialchars($piece['designation']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité *</label>
                <input type="number" name="quantite" class="form-control" required value="<?= htmlspecialchars($piece['quantite']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix Vente HT *</label>
                <input type="number" step="0.01" name="prix_vente_ht" class="form-control" required value="<?= htmlspecialchars($piece['prix_vente_ht']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix Achat HT</label>
                <input type="number" step="0.01" name="prix_achat_ht" class="form-control" value="<?= htmlspecialchars($piece['prix_achat_ht']); ?>">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="<?= ROOT_URL; ?>/pieces/table.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
