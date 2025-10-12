<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<!-- Contenu principal -->
<main class="container mt-5 pt-4">

    <div class="text-center mb-5">
        <h2 class="fw-bold">Tableau de bord</h2>
        <p class="lead text-muted">Bienvenue dans votre atelier de gestion. Voici un aperçu global de votre activité.</p>
    </div>

    <!-- ✅ Cartes de statistiques -->
    <div class="row text-center mb-4">
        <?php
        $stats = [
            'clients' => $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
            'vehicules' => $pdo->query("SELECT COUNT(*) FROM vehicules")->fetchColumn(),
            'interventions' => $pdo->query("SELECT COUNT(*) FROM interventions")->fetchColumn(),
            'pieces' => $pdo->query("SELECT COUNT(*) FROM pieces")->fetchColumn(),
        ];
        ?>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <i class="bi bi-people fs-1 text-primary"></i>
                    <h5 class="mt-2 fw-bold"><?= $stats['clients']; ?></h5>
                    <p class="text-muted mb-0">Clients enregistrés</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <i class="bi bi-truck fs-1 text-success"></i>
                    <h5 class="mt-2 fw-bold"><?= $stats['vehicules']; ?></h5>
                    <p class="text-muted mb-0">Véhicules suivis</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <i class="bi bi-wrench fs-1 text-warning"></i>
                    <h5 class="mt-2 fw-bold"><?= $stats['interventions']; ?></h5>
                    <p class="text-muted mb-0">Interventions effectuées</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <i class="bi bi-box-seam fs-1 text-danger"></i>
                    <h5 class="mt-2 fw-bold"><?= $stats['pieces']; ?></h5>
                    <p class="text-muted mb-0">Pièces en stock</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Section messages / rappels -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-chat-left-text me-2"></i> Derniers messages & notifications
        </div>
        <div class="card-body">
            <?php
            $messages = [
                "N'oubliez pas de vérifier les interventions en attente de validation.",
                "Le stock de certaines pièces est bas, pensez à réapprovisionner.",
                "Un nouveau client a été ajouté récemment.",
                "Sauvegardez régulièrement la base de données pour éviter toute perte."
            ];
            ?>

            <ul class="list-group list-group-flush">
                <?php foreach ($messages as $msg): ?>
                    <li class="list-group-item">
                        <i class="bi bi-info-circle text-primary me-2"></i> <?= htmlspecialchars($msg); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- ✅ Section aperçu rapide -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-graph-up me-2"></i> Activité récente
        </div>
        <div class="card-body">
            <p class="text-muted">Aperçu des 5 dernières interventions :</p>
            <?php
            $recent = $pdo->query("SELECT description, date_intervention FROM interventions ORDER BY date_intervention DESC LIMIT 5");
            if ($recent->rowCount() > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($row = $recent->fetch(PDO::FETCH_ASSOC)): ?>
                        <li class="list-group-item">
                            <i class="bi bi-wrench-adjustable text-success me-2"></i>
                            <?= htmlspecialchars($row['description']); ?>
                            <span class="float-end text-muted"><?= htmlspecialchars($row['date_intervention']); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">Aucune intervention récente.</p>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
