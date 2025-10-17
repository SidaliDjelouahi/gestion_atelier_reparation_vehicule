<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Vérifier si ID véhicule fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . ROOT_URL . "/vehicules/table.php");
    exit;
}

$id = (int)$_GET['id'];

// Charger les infos du véhicule (colonnes selon ta DB)
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE id = ?");
$stmt->execute([$id]);
$vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicule) {
    echo "<div class='alert alert-danger text-center mt-5'>Véhicule introuvable.</div>";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération sécurisée
    $matricule   = trim($_POST['matricule'] ?? '');
    $marque      = trim($_POST['marque'] ?? '');
    $modele      = trim($_POST['modele'] ?? '');
    $num_chassis = trim($_POST['num_chassis'] ?? '');
    $km_initial  = $_POST['km_initial'] !== '' ? trim($_POST['km_initial']) : null;
    $id_client   = isset($_POST['id_client']) ? (int)$_POST['id_client'] : 0;

    // Validation minimale
    $errors = [];
    if ($id_client <= 0) $errors[] = "Veuillez sélectionner un client.";
    if ($matricule === '') $errors[] = "La matricule est requise.";
    // add more validations if needed

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE vehicules
                SET id_client = ?, matricule = ?, marque = ?, modele = ?, num_chassis = ?, km_initial = ?
                WHERE id = ?
            ");
            // Convert km_initial to null or numeric
            $km_val = ($km_initial === null || $km_initial === '') ? null : $km_initial;
            $stmt->execute([
                $id_client,
                $matricule,
                $marque,
                $modele,
                $num_chassis,
                $km_val,
                $id
            ]);

            // rediriger vers détails
            header("Location: " . ROOT_URL . "/vehicules/details.php?id=" . $id);
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Charger la liste des clients
$clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container py-4">
    <h4 class="text-center text-primary mb-4"><i class="bi bi-pencil"></i> Modifier le véhicule</h4>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="card shadow-sm p-4 rounded-3">
        <div class="mb-3">
            <label class="form-label">Client *</label>
            <select name="id_client" class="form-select" required>
                <option value="">-- Sélectionner un client --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id']; ?>" <?= ($vehicule['id_client'] ?? $vehicule['id_client'] ?? '') == $client['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($client['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Matricule *</label>
            <input type="text" name="matricule" class="form-control" required
                   value="<?= htmlspecialchars($vehicule['matricule'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Marque</label>
            <input type="text" name="marque" class="form-control"
                   value="<?= htmlspecialchars($vehicule['marque'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Modèle</label>
            <input type="text" name="modele" class="form-control"
                   value="<?= htmlspecialchars($vehicule['modele'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Numéro de châssis</label>
            <input type="text" name="num_chassis" class="form-control"
                   value="<?= htmlspecialchars($vehicule['num_chassis'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">KM initial</label>
            <input type="number" step="1" name="km_initial" class="form-control"
                   value="<?= htmlspecialchars($vehicule['km_initial'] ?? ''); ?>">
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Enregistrer les modifications
            </button>
            <a href="<?= ROOT_URL; ?>/vehicules/details.php?id=<?= $id; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Annuler
            </a>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
