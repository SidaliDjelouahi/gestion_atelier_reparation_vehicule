<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$params = [];
$where = 'WHERE 1=1';

if ($client_id) {
    $where .= " AND v.id_client = ?";
    $params[] = $client_id;
}

if ($q !== '') {
    $where .= " AND (v.matricule LIKE ? OR v.marque LIKE ? OR v.modele LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$stmt = $pdo->prepare("
    SELECT v.*, c.nom AS client_nom
    FROM vehicules v
    LEFT JOIN clients c ON v.id_client = c.id
    $where
    ORDER BY v.id DESC
");
$stmt->execute($params);
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ✅ Affichage Desktop -->
<div class="table-responsive d-none d-md-block">
    <table class="table table-striped align-middle">
        <thead class="table-primary">
            <tr>
                <th>Matricule</th>
                <th>Marque</th>
                <th>Modèle</th>
                <th>Client</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($vehicules): ?>
            <?php foreach ($vehicules as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['matricule']); ?></td>
                    <td><?= htmlspecialchars($v['marque']); ?></td>
                    <td><?= htmlspecialchars($v['modele']); ?></td>
                    <td><?= htmlspecialchars($v['client_nom']); ?></td>
                    <td class="text-end">
                        <a href="<?= ROOT_URL; ?>/vehicules/details_vehicule_client.php?id=<?= $v['id']; ?>" class="btn btn-sm btn-info text-white">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= ROOT_URL; ?>/vehicules/modifier_vehicule_client.php?id=<?= $v['id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= ROOT_URL; ?>/vehicules/supprimer_vehicule_client.php?id=<?= $v['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Confirmer la suppression de ce véhicule ?');">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">Aucun véhicule trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ✅ Affichage Mobile -->
<div class="d-md-none">
    <?php if ($vehicules): ?>
        <div class="row row-cols-1 g-3">
            <?php foreach ($vehicules as $v): ?>
                <div class="col">
                    <div class="border rounded-3 p-3 shadow-sm h-100">
                        <h6 class="fw-bold text-primary mb-2"><?= htmlspecialchars($v['matricule']); ?></h6>
                        <div><strong>Marque :</strong> <?= htmlspecialchars($v['marque']); ?></div>
                        <div><strong>Modèle :</strong> <?= htmlspecialchars($v['modele']); ?></div>
                        <div><strong>Client :</strong> <?= htmlspecialchars($v['client_nom']); ?></div>
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
