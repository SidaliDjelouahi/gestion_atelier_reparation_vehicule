<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// ✅ Vérifier session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// ✅ Vérifier ID véhicule
if (!isset($_GET['id_vehicule']) || !is_numeric($_GET['id_vehicule'])) {
    header("Location: " . ROOT_URL . "/vehicules/table.php");
    exit;
}

$id_vehicule = (int)$_GET['id_vehicule'];

// ✅ Récupérer les infos du véhicule
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE id = ?");
$stmt->execute([$id_vehicule]);
$vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicule) {
    echo "<div class='alert alert-danger text-center mt-5'>Véhicule introuvable.</div>";
    exit;
}

// ✅ Recherche et récupération des interventions
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "
    SELECT * FROM interventions
    WHERE id_vehicule = :id_vehicule
      AND (description LIKE :search OR date_intervention LIKE :search)
    ORDER BY date_intervention DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'id_vehicule' => $id_vehicule,
    'search' => "%$search%"
]);
$interventions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">

    <!-- 🧭 En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-tools"></i> Interventions du véhicule :
            <span class="text-dark">
                <?= htmlspecialchars($vehicule['marque'] . " " . $vehicule['modele']); ?>
                (<?= htmlspecialchars($vehicule['matricule']); ?>)
            </span>
        </h4>
        
    </div>

    <!-- 🔍 Moteur de recherche -->
    <form method="GET" class="mb-3">
        <input type="hidden" name="id_vehicule" value="<?= $id_vehicule; ?>">
        <div class="input-group">
            <input type="text" name="search" class="form-control"
                   placeholder="Rechercher dans les interventions..."
                   value="<?= htmlspecialchars($search); ?>">
            <button class="btn btn-outline-secondary" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>

    <!-- 📋 Tableau des interventions -->
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Kilométrage</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($interventions): ?>
                        <?php foreach ($interventions as $i): ?>
                            <tr>
                                <td><?= $i['id']; ?></td>
                                <td><?= htmlspecialchars(date("d/m/Y H:i", strtotime($i['date_intervention']))); ?></td>
                                <td><?= htmlspecialchars($i['km']); ?> km</td>
                                <td><?= htmlspecialchars(substr($i['description'], 0, 50)); ?>...</td>
                                <td>
                                    <a href="<?= ROOT_URL; ?>/interventions/details.php?id=<?= $i['id']; ?>"
                                       class="btn btn-sm btn-info text-white">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted text-center">Aucune intervention trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?= ROOT_URL; ?>/vehicules/details.php?id=<?= $vehicule['id']; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour au véhicule
        </a>
    </div>
</div>



<?php include("../includes/footer.php"); ?>
