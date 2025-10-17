<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "
    SELECT i.*, v.marque, v.modele, v.matricule
    FROM interventions i
    LEFT JOIN vehicules v ON i.id_vehicule = v.id
    WHERE v.marque LIKE :search 
       OR v.modele LIKE :search 
       OR v.matricule LIKE :search 
       OR i.description LIKE :search
    ORDER BY i.date_intervention DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['search' => "%$search%"]);
$interventions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card shadow-sm">
    <div class="card-body">
        <!-- 🖥️ Table Desktop -->
        <div class="table-responsive desktop-table">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Véhicule</th>
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
                                <td><?= htmlspecialchars($i['marque'] . " " . $i['modele'] . " (" . $i['matricule'] . ")"); ?></td>
                                <td><?= htmlspecialchars(date("d/m/Y H:i", strtotime($i['date_intervention']))); ?></td>
                                <td><?= htmlspecialchars($i['km']); ?></td>
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
                        <tr><td colspan="6" class="text-center text-muted">Aucune intervention trouvée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 📱 Table Mobile -->
        <table class="mobile-table w-100">
            <thead>
                <tr>
                    <th>Véhicule</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($interventions): ?>
                    <?php foreach ($interventions as $i): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-primary"><?= htmlspecialchars($i['marque'] . " " . $i['modele']); ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($i['matricule']); ?></div>
                                <div class="small"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars(date("d/m/Y", strtotime($i['date_intervention']))); ?></div>
                            </td>
                            <td>
                                <a href="<?= ROOT_URL; ?>/interventions/details.php?id=<?= $i['id']; ?>" 
                                   class="btn btn-sm btn-info text-white w-100">
                                    <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center text-muted">Aucune intervention trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
