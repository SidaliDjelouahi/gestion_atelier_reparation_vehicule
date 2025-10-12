<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config.php";
?>

<!-- ✅ Bouton flottant (en bas à droite) -->
<button id="sidebarToggle" class="sidebar-toggle-btn">
    <i class="bi bi-list"></i>
</button>

<!-- ✅ Barre latérale -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-tools"></i>
        <h5>Atelier</h5>
    </div>
    <ul class="sidebar-menu">
        <li><a href="<?php echo ROOT_URL; ?>/dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
        <li><a href="<?php echo ROOT_URL; ?>/clients/liste.php"><i class="bi bi-people"></i> Clients</a></li>
        <li><a href="<?php echo ROOT_URL; ?>/vehicules/liste.php"><i class="bi bi-truck"></i> Véhicules</a></li>
        <li><a href="<?php echo ROOT_URL; ?>/interventions/liste.php"><i class="bi bi-wrench"></i> Interventions</a></li>
        <li><a href="<?php echo ROOT_URL; ?>/pieces/liste.php"><i class="bi bi-box-seam"></i> Pièces</a></li>
        <li><a href="<?php echo ROOT_URL; ?>/bons/liste.php"><i class="bi bi-receipt"></i> Bons</a></li>
        <li><a href="<?php echo ROOT_URL; ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
    </ul>
</div>

<!-- ✅ Lien CSS -->
<link rel="stylesheet" href="<?php echo ROOT_URL; ?>/includes/sidebar.css">

<!-- ✅ Script d’ouverture/fermeture -->
<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    toggleBtn.classList.toggle('active');

    // ✅ Changement d’icône (menu ↔ fermer)
    const icon = toggleBtn.querySelector('i');
    if (sidebar.classList.contains('active')) {
        icon.classList.remove('bi-list');
        icon.classList.add('bi-x-lg');
    } else {
        icon.classList.remove('bi-x-lg');
        icon.classList.add('bi-list');
    }
});
</script>
