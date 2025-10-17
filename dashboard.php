<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/index.php");
    exit;
}

require_once __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="container mt-5 pt-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Tableau de bord - Activité Atelier</h2>
        <p class="lead text-muted">Suivi global et détaillé par période</p>
    </div>

    <?php
    // Fonction utilitaire sécurisée
    function getCount($pdo, $table, $condition = '') {
        try {
            $query = "SELECT COUNT(*) FROM $table";
            if ($condition) $query .= " WHERE $condition";
            return $pdo->query($query)->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    // Périodes
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $monthStart = date('Y-m-01');

    // Table => Titre + Icône + Couleur
    $entities = [
        'clients' => ['title' => 'Clients', 'icon' => 'bi-people', 'color' => 'primary', 'date_col' => null],
        'vehicules' => ['title' => 'Véhicules', 'icon' => 'bi-truck', 'color' => 'success', 'date_col' => null],
        'interventions' => ['title' => 'Interventions', 'icon' => 'bi-wrench', 'color' => 'warning', 'date_col' => 'date_intervention'],
        'pieces' => ['title' => 'Pièces', 'icon' => 'bi-box-seam', 'color' => 'danger', 'date_col' => null],
    ];

    ?>

    <div class="row g-4">
        <?php foreach ($entities as $table => $info): ?>
            <?php
            $dateCol = $info['date_col'] ? $info['date_col'] : 'id'; // fallback
            $stats = [
                'total' => getCount($pdo, $table),
                'day' => getCount($pdo, $table, "$dateCol >= '$today'"),
                'week' => getCount($pdo, $table, "$dateCol >= '$weekStart'"),
                'month' => getCount($pdo, $table, "$dateCol >= '$monthStart'"),
            ];
            ?>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center position-relative">
                        <i class="bi <?= $info['icon']; ?> fs-1 text-<?= $info['color']; ?>"></i>
                        <h5 class="mt-2 fw-bold"><?= $stats['total']; ?></h5>
                        <p class="text-muted mb-0"><?= $info['title']; ?> totaux</p>

                        <button class="btn btn-sm btn-outline-<?= $info['color']; ?> mt-3 toggle-details" data-target="#details-<?= $table; ?>">
                            Voir les détails
                        </button>

                        <div class="details mt-3 d-none" id="details-<?= $table; ?>">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Entrées aujourd'hui</span>
                                    <strong><?= $stats['day']; ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Entrées cette semaine</span>
                                    <strong><?= $stats['week']; ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Entrées ce mois</span>
                                    <strong><?= $stats['month']; ?></strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.querySelector(btn.dataset.target);
                target.classList.toggle('d-none');
                target.classList.toggle('animate__animated');
                target.classList.toggle('animate__fadeInDown');
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
