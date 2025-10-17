<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// ✅ Vérification stricte de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || $_GET['id'] <= 0) {
    echo "<div class='alert alert-danger text-center mt-5'>ID invalide.</div>";
    exit;
}

$id = (int)$_GET['id'];

// 🔍 Vérifier si l'intervention existe
$stmt = $pdo->prepare("
    SELECT i.*, v.marque, v.modele, v.matricule, b.id AS id_bon
    FROM interventions i
    LEFT JOIN vehicules v ON i.id_vehicule = v.id
    LEFT JOIN bons_intervention b ON b.num_bon = CONCAT('BON-', i.id)
    WHERE i.id = ?
");
$stmt->execute([$id]);
$intervention = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$intervention) {
    echo "<div class='alert alert-danger text-center mt-5'>Intervention introuvable.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'oui') {
        try {
            $pdo->beginTransaction();

            // 🔹 Si un bon existe
            if (!empty($intervention['id_bon'])) {

                // 🔄 Rendre le stock pour chaque détail
                $stmtDetails = $pdo->prepare("SELECT id_piece, quantite FROM bons_intervention_details WHERE id_bon_intervention = ?");
                $stmtDetails->execute([$intervention['id_bon']]);
                $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

                foreach ($details as $d) {
                    $pdo->prepare("UPDATE pieces SET quantite = quantite + ? WHERE id = ?")
                        ->execute([$d['quantite'], $d['id_piece']]);
                }

                // ❌ Supprimer les détails
                $pdo->prepare("DELETE FROM bons_intervention_details WHERE id_bon_intervention = ?")
                    ->execute([$intervention['id_bon']]);

                // ❌ Supprimer le bon
                $pdo->prepare("DELETE FROM bons_intervention WHERE id = ?")
                    ->execute([$intervention['id_bon']]);
            }

            // ❌ Supprimer l'intervention
            $stmt = $pdo->prepare("DELETE FROM interventions WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);

            $pdo->commit();

            echo "<div class='alert alert-success text-center mt-4'>✅ Intervention et bons associés supprimés avec succès.</div>";
            echo "<script>setTimeout(()=>window.location='table.php',1500);</script>";
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger text-center mt-4'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<script>window.location='table.php';</script>";
        exit;
    }
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container py-4">
    <h4 class="text-center text-danger mb-4">
        <i class="bi bi-exclamation-triangle"></i> Confirmation de suppression
    </h4>

    <div class="card border-danger shadow-sm rounded-3">
        <div class="card-body text-center">
            <p class="mb-3">Êtes-vous sûr de vouloir supprimer cette intervention et tous les bons associés ?</p>

            <div class="alert alert-light border text-start">
                <p><strong>ID :</strong> <?= htmlspecialchars($intervention['id']); ?></p>
                <p><strong>Véhicule :</strong> <?= htmlspecialchars($intervention['marque'] . " " . $intervention['modele'] . " (" . $intervention['matricule'] . ")"); ?></p>
                <p><strong>Date :</strong> <?= htmlspecialchars($intervention['date_intervention']); ?></p>
                <p><strong>Kilométrage :</strong> <?= htmlspecialchars($intervention['km']); ?> km</p>
                <p><strong>Description :</strong><br><?= nl2br(htmlspecialchars($intervention['description'])); ?></p>
            </div>

            <form method="POST" class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                <button type="submit" name="confirm" value="oui" class="btn btn-danger px-4">
                    <i class="bi bi-trash"></i> Oui, supprimer
                </button>
                <button type="submit" name="confirm" value="non" class="btn btn-secondary px-4">
                    <i class="bi bi-x-circle"></i> Annuler
                </button>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
