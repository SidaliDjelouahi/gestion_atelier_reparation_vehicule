<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Page accessible seulement si connecté (tu peux adapter selon ta logique)
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Récupération initiale
$stmt = $pdo->query("SELECT * FROM clients ORDER BY id DESC LIMIT 100");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <h4 class="mb-0 text-center text-md-start">Liste des Clients</h4>

        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
            <!-- Barre de recherche : pleine largeur sur mobile -->
            <input id="clientSearch" type="search" class="form-control flex-grow-1" placeholder="Rechercher (nom, RC, téléphone...)">

            <!-- Groupe de boutons -->
            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end mt-2 mt-sm-0">
                <a href="<?= ROOT_URL; ?>/clients/export_csv.php" class="btn btn-outline-primary">Export CSV</a>
                <a href="<?= ROOT_URL; ?>/clients/export_pdf.php" class="btn btn-outline-secondary">Export PDF</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </button>
            </div>
        </div>
    </div>


    <div id="clientsContainer" class="table-responsive">
    <table id="clientsTable" class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th class="d-none d-md-table-cell">RC</th>
                <th class="d-none d-md-table-cell">Adresse</th>
                <th class="d-none d-md-table-cell">NIF</th>
                <th class="d-none d-md-table-cell">NIS</th>
                <th class="d-none d-md-table-cell">IA</th>
                <th style="width:110px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clients)): ?>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?= htmlspecialchars($client['id']); ?></td>
                        <td><?= htmlspecialchars($client['nom']); ?></td>
                        <td><?= htmlspecialchars($client['telephone']); ?></td>
                        
                        <!-- Colonnes cachées sur mobile -->
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($client['rc']); ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($client['adresse']); ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($client['nif']); ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($client['nis']); ?></td>
                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($client['ia']); ?></td>

                        <td class="text-center">
                            <!-- Un seul bouton "Détails" -->
                            <a href="<?= ROOT_URL; ?>/clients/details.php?id=<?= $client['id']; ?>" 
                               class="btn btn-sm btn-info text-white">
                                <i class="bi bi-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">Aucun client trouvé</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

<!-- Modal d’ajout -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= ROOT_URL; ?>/clients/ajouter.php" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addClientModalLabel">Ajouter un Client</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">RC *</label>
                        <input type="text" name="rc" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIF</label>
                        <input type="text" name="nif" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIS</label>
                        <input type="text" name="nis" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IA</label>
                        <input type="text" name="ia" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

<!-- JS: recherche AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('clientSearch');
    let timer = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = this.value.trim();
            fetch('<?= ROOT_URL; ?>/clients/search.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    const tbody = document.querySelector('#clientsTable tbody');
                    tbody.innerHTML = '';

                    if (!data.length) {
                        tbody.innerHTML = '<tr><td colspan="9" class="text-center">Aucun résultat</td></tr>';
                        return;
                    }

                    data.forEach(c => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(c.id)}</td>
                            <td>${escapeHtml(c.nom)}</td>
                            <td>${escapeHtml(c.telephone ?? '')}</td>
                            <td colspan="6" class="text-center">
                                <a href="<?= ROOT_URL; ?>/clients/details.php?id=${c.id}" 
                                   class="btn btn-sm btn-info text-white">
                                   <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error('Erreur AJAX recherche clients:', err));
        }, 300);
    });

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>

</script>
