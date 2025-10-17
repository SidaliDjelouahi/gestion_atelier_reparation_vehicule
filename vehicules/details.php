<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// ✅ Vérifier si un ID est passé dans l’URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . ROOT_URL . "/vehicules/table.php");
    exit;
}

$id = (int)$_GET['id'];

// ✅ Récupérer les infos du véhicule et du client
$stmt = $pdo->prepare("
    SELECT v.*, c.nom AS client_nom
    FROM vehicules v
    JOIN clients c ON v.id_client = c.id
    WHERE v.id = ?
");
$stmt->execute([$id]);
$vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicule) {
    echo "<div class='alert alert-danger text-center mt-5'>Véhicule introuvable.</div>";
    exit;
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">

    <!-- ✅ Titre principal -->
    <div class="mb-4 text-center">
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-truck"></i> Détails du véhicule #<?= htmlspecialchars($vehicule['id']); ?>
        </h4>
    </div>

    <!-- ✅ Boutons d’action -->
    <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
        <a href="<?= ROOT_URL; ?>/vehicules/modifier.php?id=<?= $vehicule['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="<?= ROOT_URL; ?>/vehicules/supprimer.php?id=<?= $vehicule['id']; ?>" 
           class="btn btn-danger" onclick="return confirm('Confirmer la suppression de ce véhicule ?');">
            <i class="bi bi-trash"></i> Supprimer
        </a>
        <a href="<?= ROOT_URL; ?>/interventions/table_vehicule.php?id_vehicule=<?= $vehicule['id']; ?>" class="btn btn-info text-white">
            <i class="bi bi-wrench"></i> Interventions
        </a>
        
    </div>

    <!-- ✅ Carte d’informations du véhicule -->
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="mb-3 text-primary">Informations générales</h5>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="fw-bold">Client :</label>
                    <div><?= htmlspecialchars($vehicule['client_nom']); ?></div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="fw-bold">Matricule :</label>
                    <div><?= htmlspecialchars($vehicule['matricule']); ?></div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="fw-bold">Marque :</label>
                    <div><?= htmlspecialchars($vehicule['marque']); ?></div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="fw-bold">Modèle :</label>
                    <div><?= htmlspecialchars($vehicule['modele']); ?></div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="fw-bold">Numéro de châssis :</label>
                    <div><?= htmlspecialchars($vehicule['num_chassis']); ?></div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="fw-bold">Kilométrage initial :</label>
                    <div><?= htmlspecialchars($vehicule['km_initial']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Bouton retour -->
    <div class="mt-4 text-center">
        <a href="<?= ROOT_URL; ?>/vehicules/table.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
