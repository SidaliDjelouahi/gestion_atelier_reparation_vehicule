<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// Récupération initiale (liste limitée)
$stmt = $pdo->query("
    SELECT v.*, c.nom AS client_nom 
    FROM vehicules v
    JOIN clients c ON v.id_client = c.id
    ORDER BY v.id DESC
    LIMIT 100
");
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../includes/header.php"); ?>
<?php include("../includes/sidebar.php"); ?>

<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
        <h4 class="mb-0 text-center text-md-start">Liste des Véhicules</h4>

        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
            <!-- 🔍 Barre de recherche -->
            <input id="vehiculeSearch" type="search" class="form-control flex-grow-1"
                   placeholder="Rechercher (client, matricule, marque, modèle...)">

            <!-- Groupe de boutons -->
            <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-end mt-2 mt-sm-0">
                <a href="<?= ROOT_URL; ?>/vehicules/export_csv.php" class="btn btn-outline-primary">Export CSV</a>
                <a href="<?= ROOT_URL; ?>/vehicules/export_pdf.php" class="btn btn-outline-secondary">Export PDF</a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVehiculeModal">
                    <i class="bi bi-plus-lg"></i> Ajouter
                </button>
            </div>
        </div>
    </div>

    <!-- ✅ Tableau principal -->
    <div id="vehiculesContainer" class="table-responsive">
        <table id="vehiculesTable" class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Matricule</th>
                    <th class="d-none d-md-table-cell">Marque</th>
                    <th class="d-none d-md-table-cell">Modèle</th>
                    <th class="d-none d-md-table-cell">Numéro de châssis</th>
                    <th class="d-none d-md-table-cell">KM initial</th>
                    <th style="width:110px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vehicules)): ?>
                    <?php foreach ($vehicules as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['id']); ?></td>
                            <td><?= htmlspecialchars($v['client_nom']); ?></td>
                            <td><?= htmlspecialchars($v['matricule']); ?></td>

                            <!-- Colonnes cachées sur mobile -->
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($v['marque']); ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($v['modele']); ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($v['num_chassis']); ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($v['km_initial']); ?></td>

                            <td class="text-center">
                                <a href="<?= ROOT_URL; ?>/vehicules/details.php?id=<?= $v['id']; ?>" 
                                   class="btn btn-sm btn-primary text-white">
                                    <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">Aucun véhicule trouvé</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ Modal d’ajout -->
<div class="modal fade" id="addVehiculeModal" tabindex="-1" aria-labelledby="addVehiculeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= ROOT_URL; ?>/vehicules/ajouter.php" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addVehiculeModalLabel">Ajouter un véhicule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client *</label>
                        <select name="id_client" class="form-select" required>
                            <option value="">-- Sélectionner un client --</option>
                            <?php
                            $clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom ASC")->fetchAll();
                            foreach ($clients as $c) {
                                echo "<option value='{$c['id']}'>" . htmlspecialchars($c['nom']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Matricule *</label>
                        <input type="text" name="matricule" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Marque</label>
                        <input type="text" name="marque" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modèle</label>
                        <input type="text" name="modele" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro de châssis</label>
                        <input type="text" name="num_chassis" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">KM initial</label>
                        <input type="number" name="km_initial" class="form-control">
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

<!-- ✅ JS : moteur de recherche AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('vehiculeSearch');
    let timer = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = this.value.trim();
            fetch('<?= ROOT_URL; ?>/vehicules/search.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    const tbody = document.querySelector('#vehiculesTable tbody');
                    tbody.innerHTML = '';

                    if (!data.length) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun résultat</td></tr>';
                        return;
                    }

                    data.forEach(v => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(v.id)}</td>
                            <td>${escapeHtml(v.client_nom)}</td>
                            <td>${escapeHtml(v.matricule ?? '')}</td>
                            <td class="d-none d-md-table-cell">${escapeHtml(v.marque ?? '')}</td>
                            <td class="d-none d-md-table-cell">${escapeHtml(v.modele ?? '')}</td>
                            <td class="d-none d-md-table-cell">${escapeHtml(v.num_chassis ?? '')}</td>
                            <td class="d-none d-md-table-cell">${escapeHtml(v.km_initial ?? '')}</td>
                            <td class="text-center">
                                <a href="<?= ROOT_URL; ?>/vehicules/details.php?id=${v.id}" 
                                   class="btn btn-sm btn-primary text-white">
                                   <i class="bi bi-eye"></i> Détails
                                </a>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error('Erreur AJAX recherche véhicules:', err));
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
