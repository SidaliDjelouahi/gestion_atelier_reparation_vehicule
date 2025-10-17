<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($q === "") {
    $stmt = $pdo->query("SELECT * FROM pieces ORDER BY ref ASC LIMIT 50");
} else {
    $stmt = $pdo->prepare("
        SELECT * FROM pieces
        WHERE ref LIKE :q OR designation LIKE :q
        ORDER BY ref ASC
    ");
    $stmt->execute(['q' => "%$q%"]);
}
$pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="table-responsive">
<table class="table table-bordered table-striped align-middle text-center">
    <thead class="table-light">
        <tr>
            <th>Référence</th>
            <th>Désignation</th>
            <th>Quantité</th>
            <th>Prix Achat</th>
            <th>Prix Vente</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($pieces): ?>
        <?php foreach ($pieces as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['ref']); ?></td>
            <td><?= htmlspecialchars($p['designation']); ?></td>
            <td><?= htmlspecialchars($p['quantite']); ?></td>
            <td><?= number_format($p['prix_achat_ht'], 2); ?> DA</td>
            <td><?= number_format($p['prix_vente_ht'], 2); ?> DA</td>
            <td><?= htmlspecialchars($p['stock']); ?></td>
            <td>
                <button class="btn btn-sm btn-info btn-details" data-ref="<?= htmlspecialchars($p['ref']); ?>">
                    <i class="bi bi-eye"></i>
                </button>
                <a href="modifier.php?ref=<?= urlencode($p['ref']); ?>" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil"></i>
                </a>
                <a href="supprimer.php?ref=<?= urlencode($p['ref']); ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Supprimer cette pièce ?')">
                    <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" class="text-muted">Aucune pièce trouvée.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
