<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
$client_nom = "Tous les véhicules";

// 🔹 Récupérer le nom du client si client_id est spécifié
if ($client_id) {
    $stmtClient = $pdo->prepare("SELECT nom FROM clients WHERE id = ?");
    $stmtClient->execute([$client_id]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        $client_nom = $client['nom'];
    }
}

// 🔹 Récupérer les véhicules
if ($client_id) {
    $stmt = $pdo->prepare("
        SELECT v.* FROM vehicules v
        WHERE v.id_client = ?
        ORDER BY v.id DESC
    ");
    $stmt->execute([$client_id]);
} else {
    $stmt = $pdo->query("
        SELECT v.*, c.nom AS client_nom
        FROM vehicules v
        JOIN clients c ON v.id_client = c.id
        ORDER BY v.id DESC
    ");
}

$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">

    <!-- 🔹 Titre -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-primary mb-3">
            <i class="bi bi-car-front"></i> Véhicules de <?= htmlspecialchars($client_nom); ?>
        </h4>
        <?php if ($client_id): ?>
            <a href="<?= ROOT_URL; ?>/vehicules/modifier.php?client_id=<?= $client_id; ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Ajouter un véhicule
            </a>
        <?php endif; ?>
    </div>

    <!-- 🔹 Liste responsive -->
    <div class="card shadow-sm rounded-3">
        <div class="card-body">

            <!-- ✅ Affichage mobile-friendly -->
            <?php if (count($vehicules) > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($vehicules as $v): ?>
                        <div class="col">
                            <div class="border rounded-3 p-3 shadow-sm h-100">
                                <h6 class="text-uppercase fw-bold text-secondary mb-2">
                                    <?= htmlspecialchars($v['matricule']); ?>
                                </h6>
                                <div><strong>Modèle :</strong> <?= htmlspecialchars($v['modele']); ?></div>
                                <div><strong>Marque :</strong> <?= htmlspecialchars($v['marque']); ?></div>

                                <div class="mt-3 d-flex justify-content-between">
                                    <a href="<?= ROOT_URL; ?>/vehicules/details_vehicule_client.php?id=<?= $v['id']; ?>" class="btn btn-sm btn-info text-white">
                                        <i class="bi bi-eye"></i> Détails
                                    </a>
                                    <div>
                                        <a href="<?= ROOT_URL; ?>/vehicules/modifier_vehicule_client.php?id=<?= $v['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= ROOT_URL; ?>/vehicules/supprimer_vehicule_client.php?id=<?= $v['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Confirmer la suppression de ce véhicule ?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-muted mb-0">Aucun véhicule trouvé.</p>
            <?php endif; ?>

        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="<?= ROOT_URL; ?>/clients/details.php?id=<?= $client_id ?? ''; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour au client
        </a>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
