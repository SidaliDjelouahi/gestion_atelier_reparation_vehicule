<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier si l’utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . ROOT_URL . "/clients/table.php");
    exit;
}

$id = (int)$_GET['id'];

// Récupérer les infos du client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "<div class='alert alert-danger text-center mt-5'>Client introuvable.</div>";
    exit;
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">

    <!-- ✅ Titre principal sur une ligne séparée -->
    <div class="mb-4 text-center">
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-circle"></i> Détails du Client #<?= htmlspecialchars($client['id']); ?>
        </h4>
    </div>

    <!-- ✅ Boutons d’action -->
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
        <a href="<?= ROOT_URL; ?>/clients/modifier.php?id=<?= $client['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="<?= ROOT_URL; ?>/clients/supprimer.php?id=<?= $client['id']; ?>" 
           class="btn btn-danger" onclick="return confirm('Confirmer la suppression de ce client ?');">
            <i class="bi bi-trash"></i> Supprimer
        </a>
        <a href="<?= ROOT_URL; ?>/vehicules/table_client.php?client_id=<?= $client['id']; ?>" class="btn btn-info text-white">
            <i class="bi bi-car-front"></i> Véhicules
        </a>
        <a href="<?= ROOT_URL; ?>/clients/situations.php?id=<?= $client['id']; ?>" class="btn btn-secondary">
            <i class="bi bi-graph-up"></i> Situation
        </a>
    </div>

    <!-- ✅ Informations client (chaque info sur une seule ligne) -->
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="mb-3 text-primary">Informations générales</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="fw-bold">Nom :</label>
                    <div><?= htmlspecialchars($client['nom']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">RC :</label>
                    <div><?= htmlspecialchars($client['rc']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">Téléphone :</label>
                    <div><?= htmlspecialchars($client['telephone']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">Adresse :</label>
                    <div><?= htmlspecialchars($client['adresse']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">NIF :</label>
                    <div><?= htmlspecialchars($client['nif']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">NIS :</label>
                    <div><?= htmlspecialchars($client['nis']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">IA :</label>
                    <div><?= htmlspecialchars($client['ia']); ?></div>
                </div>
                <div class="col-12">
                    <label class="fw-bold">Date d’ajout :</label>
                    <div><?= htmlspecialchars($client['created_at'] ?? '—'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="<?= ROOT_URL; ?>/clients/table.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
