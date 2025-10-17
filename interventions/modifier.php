<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

$id_intervention = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 🔍 Récupération de l'intervention et du bon
$stmt = $pdo->prepare("
    SELECT i.*, v.marque, v.modele, v.matricule, b.id AS id_bon, b.num_bon, b.date_bon
    FROM interventions i
    LEFT JOIN vehicules v ON i.id_vehicule = v.id
    LEFT JOIN bons_intervention b ON b.num_bon = CONCAT('BON-', i.id)
    WHERE i.id = ?
");
$stmt->execute([$id_intervention]);
$intervention = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$intervention) {
    echo "<div class='alert alert-danger text-center mt-5'>Intervention introuvable.</div>";
    exit;
}

// 🔍 Récupérer les détails des pièces du bon
$stmt = $pdo->prepare("
    SELECT d.id, d.id_piece, d.quantite, d.prix_vente, p.designation, p.ref
    FROM bons_intervention_details d
    JOIN pieces p ON p.id = d.id_piece
    WHERE d.id_bon_intervention = ?
");
$stmt->execute([$intervention['id_bon']]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🧾 Mise à jour intervention
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_intervention'])) {
    $date_intervention = $_POST['date_intervention'];
    $km = !empty($_POST['km']) ? (int)$_POST['km'] : null;
    $description = trim($_POST['description']);

    $stmt = $pdo->prepare("
        UPDATE interventions
        SET date_intervention = ?, km = ?, description = ?
        WHERE id = ?
    ");
    $stmt->execute([$date_intervention, $km, $description, $id_intervention]);

    echo "<div class='alert alert-success text-center mt-4'>✅ Intervention mise à jour avec succès.</div>";
    echo "<script>setTimeout(() => { window.location.reload(); }, 1500);</script>";
    exit;
}

// ➕ Ajouter une pièce
if (isset($_POST['add_piece'])) {
    $id_piece = (int)$_POST['id_piece'];
    $qte = (int)$_POST['quantite'];

    try {
        $pdo->beginTransaction();

        $p = $pdo->prepare("SELECT quantite, prix_vente_ht FROM pieces WHERE id=?");
        $p->execute([$id_piece]);
        $piece = $p->fetch(PDO::FETCH_ASSOC);

        if (!$piece) throw new Exception("Pièce introuvable !");
        if ($piece['quantite'] < $qte) throw new Exception("Stock insuffisant.");

        $stmt = $pdo->prepare("
            INSERT INTO bons_intervention_details (id_bon_intervention, id_piece, quantite, prix_vente)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$intervention['id_bon'], $id_piece, $qte, $piece['prix_vente_ht']]);

        $pdo->prepare("UPDATE pieces SET quantite = quantite - ? WHERE id = ?")
            ->execute([$qte, $id_piece]);

        $pdo->commit();
        header("Location: modifier.php?id={$id_intervention}");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger text-center mt-4'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// ❌ Supprimer une pièce
if (isset($_GET['del_detail'])) {
    $id_detail = (int)$_GET['del_detail'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id_piece, quantite FROM bons_intervention_details WHERE id=?");
        $stmt->execute([$id_detail]);
        $detail = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($detail) {
            $pdo->prepare("UPDATE pieces SET quantite = quantite + ? WHERE id = ?")
                ->execute([$detail['quantite'], $detail['id_piece']]);
            $pdo->prepare("DELETE FROM bons_intervention_details WHERE id=?")->execute([$id_detail]);
        }

        $pdo->commit();
        header("Location: modifier.php?id={$id_intervention}");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger text-center mt-4'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container py-4">
    <h4 class="text-center text-primary mb-4">
        <i class="bi bi-pencil-square"></i> Modifier l’intervention #<?= htmlspecialchars($intervention['id']); ?>
    </h4>

    <div class="card shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="update_intervention" value="1">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Véhicule</label>
                        <input type="text" class="form-control" disabled
                            value="<?= htmlspecialchars($intervention['marque'] . ' ' . $intervention['modele'] . ' (' . $intervention['matricule'] . ')'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date</label>
                        <input type="datetime-local" name="date_intervention" class="form-control"
                            value="<?= date('Y-m-d\TH:i', strtotime($intervention['date_intervention'])); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kilométrage</label>
                        <input type="number" name="km" class="form-control"
                            value="<?= htmlspecialchars($intervention['km']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Numéro du bon</label>
                        <input type="text" class="form-control" disabled
                            value="<?= htmlspecialchars($intervention['num_bon']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($intervention['description']); ?></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des pièces -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5><i class="bi bi-gear"></i> Pièces utilisées</h5>
        </div>
        <div class="card-body">
            <?php if (count($details) > 0): ?>
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Réf</th>
                            <th>Désignation</th>
                            <th>Quantité</th>
                            <th>Prix vente</th>
                            <th>Total</th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['ref']); ?></td>
                            <td><?= htmlspecialchars($d['designation']); ?></td>
                            <td><?= htmlspecialchars($d['quantite']); ?></td>
                            <td><?= number_format($d['prix_vente'], 2, ',', ' '); ?> DA</td>
                            <td><?= number_format($d['prix_vente'] * $d['quantite'], 2, ',', ' '); ?> DA</td>
                            <td class="text-center">
                                <a href="?id=<?= $id_intervention; ?>&del_detail=<?= $d['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Supprimer cette pièce ?')">
                                   <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info text-center">Aucune pièce associée.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ajouter une pièce (avec recherche AJAX) -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5><i class="bi bi-plus-circle"></i> Ajouter une pièce</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="formAddPiece">
                <input type="hidden" name="add_piece" value="1">
                <input type="hidden" name="id_piece" id="id_piece">

                <div class="row g-3">
                    <div class="col-md-6 position-relative">
                        <label class="form-label">Rechercher une pièce</label>
                        <input type="text" id="searchPiece" class="form-control" placeholder="Nom ou référence...">
                        <div id="resultsPiece" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantité</label>
                        <input type="number" name="quantite" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check2"></i> Ajouter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AJAX Recherche -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("searchPiece");
    const results = document.getElementById("resultsPiece");
    const hiddenId = document.getElementById("id_piece");

    input.addEventListener("keyup", () => {
        const q = input.value.trim();
        if (q.length < 2) {
            results.innerHTML = "";
            return;
        }

        fetch("interventions/search_piece.php?q=" + encodeURIComponent(q))
            .then(res => res.text())
            .then(data => results.innerHTML = data);
    });

    results.addEventListener("click", e => {
        if (e.target.classList.contains("list-group-item")) {
            hiddenId.value = e.target.dataset.id;
            input.value = e.target.textContent.trim();
            results.innerHTML = "";
        }
    });
});
</script>

<?php include("../includes/footer.php"); ?>
