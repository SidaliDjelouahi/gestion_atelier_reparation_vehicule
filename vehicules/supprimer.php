<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// ✅ Vérifier la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// ✅ Vérifier l’ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . ROOT_URL . "/vehicules/table.php");
    exit;
}

$id = (int)$_GET['id'];

// ✅ Vérifier si le véhicule existe
$stmt = $pdo->prepare("SELECT id, id_client, marque, modele FROM vehicules WHERE id = ?");
$stmt->execute([$id]);
$vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/sidebar.php");

if (!$vehicule) {
    echo "
    <div class='container py-5'>
        <div class='alert alert-danger text-center shadow-sm p-4 rounded-3'>
            <h5><i class='bi bi-x-circle'></i> Véhicule introuvable</h5>
            <p>Le véhicule demandé n’existe pas ou a déjà été supprimé.</p>
            <a href='" . ROOT_URL . "/vehicules/table.php' class='btn btn-secondary mt-3'>
                <i class='bi bi-arrow-left'></i> Retour à la liste
            </a>
        </div>
    </div>";
    include("../includes/footer.php");
    exit;
}

// ✅ Vérifier s’il a des interventions liées
$stmt = $pdo->prepare("SELECT COUNT(*) FROM interventions WHERE id_vehicule = ?");
$stmt->execute([$id]);
$nb_interventions = $stmt->fetchColumn();

if ($nb_interventions > 0) {
    echo "
    <div class='container py-5 text-center'>
        <div class='alert alert-warning text-start d-inline-block shadow-sm p-4 rounded-3'>
            <h5 class='text-danger'><i class='bi bi-exclamation-triangle'></i> Suppression impossible</h5>
            <p>Ce véhicule est lié à <strong>$nb_interventions intervention(s)</strong>.</p>
            <p>Veuillez d’abord <strong>supprimer ou transférer</strong> les interventions avant de supprimer ce véhicule.</p>
            <a href='" . ROOT_URL . "/vehicules/details.php?id=$id' class='btn btn-outline-secondary mt-3'>
                <i class='bi bi-arrow-left'></i> Retour aux détails
            </a>
        </div>
    </div>";
    include("../includes/footer.php");
    exit;
}

// ✅ Si aucune intervention, suppression autorisée
$stmt = $pdo->prepare("DELETE FROM vehicules WHERE id = ?");
$stmt->execute([$id]);

// ✅ Message de succès
echo "
<div class='container py-5 text-center'>
    <div class='alert alert-success d-inline-block shadow-sm p-4 rounded-3'>
        <h5><i class='bi bi-check-circle'></i> Véhicule supprimé avec succès</h5>
        <p>Le véhicule <strong>" . htmlspecialchars($vehicule['marque'] . " " . $vehicule['modele']) . "</strong> a été supprimé définitivement.</p>
        <a href='" . ROOT_URL . "/vehicules/table.php' class='btn btn-success mt-3'>
            <i class='bi bi-list'></i> Retour à la liste des véhicules
        </a>
    </div>
</div>";

include("../includes/footer.php");
exit;
?>
