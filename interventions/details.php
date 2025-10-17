<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 🔍 Récupération de l'intervention + infos véhicule
$stmt = $pdo->prepare("
    SELECT i.*, v.marque, v.modele, v.matricule
    FROM interventions i
    LEFT JOIN vehicules v ON i.id_vehicule = v.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$intervention = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$intervention) {
    echo "<div class='alert alert-danger text-center mt-5'>Intervention introuvable.</div>";
    exit;
}

// 🔍 Récupération du bon d’intervention associé
$stmtBon = $pdo->prepare("
    SELECT b.* 
    FROM bons_intervention b
    WHERE b.num_bon = ?
");
$stmtBon->execute(["BON-" . $intervention['id']]);
$bon = $stmtBon->fetch(PDO::FETCH_ASSOC);

// 🔍 Récupération des détails du bon (pièces)
$details = [];
$total_general = 0;
if ($bon) {
    $stmtDetails = $pdo->prepare("
        SELECT d.*, p.designation
        FROM bons_intervention_details d
        LEFT JOIN pieces p ON d.id_piece = p.id
        WHERE d.id_bon_intervention = ?
    ");
    $stmtDetails->execute([$bon['id']]);
    $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

    foreach ($details as $d) {
        $total_general += $d['quantite'] * $d['prix_vente'];
    }
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container py-4">
    <h4 class="text-center text-primary mb-4">
        <i class="bi bi-tools"></i> Détails de l’intervention #<?= htmlspecialchars($intervention['id']); ?>
    </h4>

    <div class="card shadow-sm rounded-3 mb-4">
        <div class="card-header bg-light fw-bold">Informations sur l’intervention</div>
        <div class="card-body">
            <p><strong>Véhicule :</strong> <?= htmlspecialchars($intervention['marque'] . " " . $intervention['modele'] . " (" . $intervention['matricule'] . ")"); ?></p>
            <p><strong>Date :</strong> <?= htmlspecialchars($intervention['date_intervention']); ?></p>
            <p><strong>Kilométrage :</strong> <?= htmlspecialchars($intervention['km']); ?> km</p>
            <p><strong>Description :</strong><br><?= nl2br(htmlspecialchars($intervention['description'])); ?></p>
        </div>
    </div>

    <?php if ($bon): ?>
    <div class="card shadow-sm rounded-3 mb-4">
        <div class="card-header bg-light fw-bold">Bon d’intervention associé</div>
        <div class="card-body">
            <p><strong>Numéro du bon :</strong> <?= htmlspecialchars($bon['num_bon']); ?></p>
            <p><strong>Date du bon :</strong> <?= htmlspecialchars($bon['date_bon']); ?></p>

            <?php if ($details): ?>
                <h6 class="mt-3 fw-bold">Pièces utilisées :</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mt-2">
                        <thead class="table-light">
                            <tr>
                                <th>Désignation</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Prix unitaire (DA)</th>
                                <th class="text-end">Total (DA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['designation']); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($d['quantite']); ?></td>
                                    <td class="text-end"><?= number_format($d['prix_vente'], 2, ',', ' '); ?></td>
                                    <td class="text-end"><?= number_format($d['quantite'] * $d['prix_vente'], 2, ',', ' '); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total général :</td>
                                <td class="text-end"><?= number_format($total_general, 2, ',', ' '); ?> DA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted fst-italic">Aucune pièce enregistrée pour ce bon.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning">Aucun bon d’intervention n’est associé à cette intervention.</div>
    <?php endif; ?>

    <div class="text-center mt-4 d-flex justify-content-center gap-2 flex-wrap">
        <a href="<?= ROOT_URL; ?>/interventions/table.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>

        <a href="<?= ROOT_URL; ?>/interventions/modifier.php?id=<?= $intervention['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil-square"></i> Modifier
        </a>

        <a href="<?= ROOT_URL; ?>/interventions/supprimer.php?id=<?= $intervention['id']; ?>" 
           class="btn btn-danger"
           onclick="return confirm('Voulez-vous vraiment supprimer cette intervention ?');">
            <i class="bi bi-trash"></i> Supprimer
        </a>

        <!-- 🔹 Nouveau bouton : convertir en bon de livraison -->
        <a href="<?= ROOT_URL; ?>/interventions/convertir_bon_livraison.php?id=<?= $intervention['id']; ?>" 
           class="btn btn-success">
            <i class="bi bi-truck"></i> Convertir en bon de livraison
        </a>

        <!-- 🔹 Nouveau bouton : convertir en facture -->
        <a href="<?= ROOT_URL; ?>/interventions/convertir_facture.php?id=<?= $intervention['id']; ?>" 
           class="btn btn-primary">
            <i class="bi bi-receipt"></i> Convertir en facture
        </a>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
