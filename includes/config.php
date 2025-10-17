<?php
// Détection automatique de l’environnement
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    define('ROOT_URL', '/gestion_atelier_reparation_vehicule');
} else {
    define('ROOT_URL', 'https://gmi.unisoft-dz.com');
}

// Chemin racine du projet
define('ROOT_PATH', __DIR__);
?>
