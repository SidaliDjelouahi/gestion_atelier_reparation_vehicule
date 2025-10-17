<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Récupération des pièces
$stmt = $pdo->query("SELECT * FROM pieces ORDER BY designation ASC");
$pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <h4 class="mb-0 text-center text-md-start">Liste des Pièces</h4>

        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
            <input id="pieceSearch" type="search" class="form-control flex-grow-1" placeholder="Rechercher (ref, désignation)">

            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end mt-2 mt-sm-0">
                <a href="<?= ROOT_URL; ?>/pieces/export_csv.php" class="btn btn-outline-primary">Export CSV</a>
                <a href="<?= ROOT_URL; ?>/pieces/export_pdf.php" class="btn btn-outline-secondary">Export PDF</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </button>
            </div>
        </div>
    </div>

    <div id="piecesContainer" class="table-responsive">
        <table id="piecesTable" class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Réf</th>
                    <th>Désignation</th>
                    <th>Quantité</th>
                    <th class="d-none d-md-table-cell">Prix Vente HT</th>
                    <th style="width:110px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($pieces)): ?>
                    <?php foreach($pieces as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['ref']); ?></td>
                            <td><?= htmlspecialchars($p['designation']); ?></td>
                            <td><?= htmlspecialchars($p['quantite']); ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($p['prix_vente_ht']); ?></td>
                            <td class="text-center">
                                <a href="<?= ROOT_URL; ?>/pieces/details.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Aucune pièce trouvée</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ➕ Modal Ajouter Pièce -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= ROOT_URL; ?>/pieces/ajouter.php" method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nouvelle Pièce</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Référence *</label>
              <input type="text" name="ref" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Désignation *</label>
              <input type="text" name="designation" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Quantité *</label>
              <input type="number" name="quantite" class="form-control" step="1" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Prix Achat HT</label>
              <input type="number" step="0.01" name="prix_achat_ht" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Prix Vente HT *</label>
              <input type="number" step="0.01" name="prix_vente_ht" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>

<!-- JS recherche simple -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('pieceSearch');
    searchInput.addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#piecesTable tbody tr').forEach(tr => {
            const ref = tr.cells[0].textContent.toLowerCase();
            const designation = tr.cells[1].textContent.toLowerCase();
            tr.style.display = (ref.includes(filter) || designation.includes(filter)) ? '' : 'none';
        });
    });
});
</script>
