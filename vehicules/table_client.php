<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de la session
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

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">

    <!-- 🔹 En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-primary mb-3">
            <i class="bi bi-car-front"></i> Véhicules de <?= htmlspecialchars($client_nom); ?>
        </h4>

        <div class="d-flex flex-wrap gap-2">
            <input type="text" id="search" class="form-control" placeholder="Rechercher un véhicule..." style="max-width: 250px;">
            <?php if ($client_id): ?>
                <a href="<?= ROOT_URL; ?>/vehicules/modifier.php?client_id=<?= $client_id; ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter un véhicule
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 🔹 Conteneur principal -->
    <div id="vehicules-container">
        <!-- Le contenu (tableau ou cartes) sera chargé ici par AJAX -->
        <div class="text-center text-muted py-4">Chargement des véhicules...</div>
    </div>

    <!-- 🔹 Bouton retour -->
    <div class="mt-4 text-center">
        <a href="<?= ROOT_URL; ?>/clients/details.php?id=<?= $client_id ?? ''; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour au client
        </a>
    </div>
</div>

<!-- 🔹 Script AJAX -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('search');
    const container = document.getElementById('vehicules-container');
    const clientId = <?= $client_id ? $client_id : 'null' ?>;

    // 🔄 Fonction de chargement AJAX
    function loadVehicules(query = '') {
        fetch('<?= ROOT_URL; ?>/vehicules/ajax_vehicules_client.php?client_id=' + (clientId ?? '') + '&q=' + encodeURIComponent(query))
            .then(response => {
                if (!response.ok) throw new Error('Erreur HTTP ' + response.status);
                return response.text();
            })
            .then(html => container.innerHTML = html)
            .catch(err => {
                console.error('Erreur AJAX:', err);
                container.innerHTML = "<div class='alert alert-danger text-center'>Erreur lors du chargement des véhicules.</div>";
            });
    }

    // 🔹 Chargement initial
    loadVehicules();

    // 🔍 Recherche instantanée
    searchInput.addEventListener('input', () => loadVehicules(searchInput.value));
});
</script>

<?php include("../includes/footer.php"); ?>
