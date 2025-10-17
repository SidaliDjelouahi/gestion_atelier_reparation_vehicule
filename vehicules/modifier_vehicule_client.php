<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editMode = $id > 0;

if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM vehicules WHERE id = ?");
    $stmt->execute([$id]);
    $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$vehicule) {
        die("<div class='alert alert-danger text-center mt-5'>Véhicule introuvable.</div>");
    }
} else {
    $vehicule = ['id_client' => '', 'matricule' => '', 'marque' => '', 'modele' => '', 'num_chassis' => '', 'km_initial' => ''];
}

// Récupérer la liste des clients
$clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// Enregistrement ou mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = $_POST['id_client'];
    $matricule = $_POST['matricule'];
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $num_chassis = $_POST['num_chassis'];
    $km_initial = $_POST['km_initial'];

    if ($editMode) {
        $stmt = $pdo->prepare("UPDATE vehicules SET id_client=?, matricule=?, marque=?, modele=?, num_chassis=?, km_initial=? WHERE id=?");
        $stmt->execute([$id_client, $matricule, $marque, $modele, $num_chassis, $km_initial, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO vehicules (id_client, matricule, marque, modele, num_chassis, km_initial) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$id_client, $matricule, $marque, $modele, $num_chassis, $km_initial]);
    }

    header("Location: " . ROOT_URL . "/vehicules/table.php?client_id=" . $id_client);
    exit;
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container py-4">
    <h4 class="text-center text-primary mb-4">
        <?= $editMode ? "Modifier le Véhicule #{$vehicule['id']}" : "Ajouter un Véhicule"; ?>
    </h4>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label fw-bold">Client :</label>
            <select name="id_client" class="form-select" required>
                <option value="">-- Sélectionner un client --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id']; ?>" <?= $vehicule['id_client'] == $c['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($c['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Matricule :</label>
            <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($vehicule['matricule']); ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Marque :</label>
                <input type="text" name="marque" class="form-control" value="<?= htmlspecialchars($vehicule['marque']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Modèle :</label>
                <input type="text" name="modele" class="form-control" value="<?= htmlspecialchars($vehicule['modele']); ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Numéro de Châssis :</label>
            <input type="text" name="num_chassis" class="form-control" value="<?= htmlspecialchars($vehicule['num_chassis']); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Kilométrage initial :</label>
            <input type="number" name="km_initial" class="form-control" value="<?= htmlspecialchars($vehicule['km_initial']); ?>">
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save"></i> Enregistrer
            </button>
            <a href="<?= ROOT_URL; ?>/vehicules/table.php?client_id=<?= $vehicule['id_client']; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
