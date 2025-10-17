<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    die("Client introuvable");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $rc = trim($_POST['rc']);
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);
    $nif = trim($_POST['nif']);
    $nis = trim($_POST['nis']);
    $ia = trim($_POST['ia']);

    $update = $pdo->prepare("UPDATE clients SET nom=?, rc=?, adresse=?, telephone=?, nif=?, nis=?, ia=? WHERE id=?");
    $update->execute([$nom, $rc, $adresse, $telephone, $nif, $nis, $ia, $id]);

    header("Location: table.php");
    exit();
}
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container py-4">
    <h4>Modifier le Client</h4>
    <form method="post" class="border p-3 rounded">
        <div class="row">
            <div class="form-group col-md-6">
                <label>Nom *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($client['nom']); ?>" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label>RC *</label>
                <input type="text" name="rc" value="<?= htmlspecialchars($client['rc']); ?>" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label>Téléphone</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($client['telephone']); ?>" class="form-control">
            </div>
            <div class="form-group col-md-6">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($client['adresse']); ?>" class="form-control">
            </div>
            <div class="form-group col-md-4">
                <label>NIF</label>
                <input type="text" name="nif" value="<?= htmlspecialchars($client['nif']); ?>" class="form-control">
            </div>
            <div class="form-group col-md-4">
                <label>NIS</label>
                <input type="text" name="nis" value="<?= htmlspecialchars($client['nis']); ?>" class="form-control">
            </div>
            <div class="form-group col-md-4">
                <label>IA</label>
                <input type="text" name="ia" value="<?= htmlspecialchars($client['ia']); ?>" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer les modifications</button>
        <a href="table.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
