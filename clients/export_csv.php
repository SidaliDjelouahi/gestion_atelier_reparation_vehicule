<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

// Optionnel : vérif session
// session_start(); if (!isset($_SESSION['user_id'])) { header('HTTP/1.1 403 Forbidden'); exit; }

$filename = 'clients_' . date('Ymd_His') . '.csv';

// En-têtes pour téléchargement
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF"; // BOM utf-8 pour excel

$out = fopen('php://output', 'w');

// Entêtes colonnes
fputcsv($out, ['ID', 'Nom', 'RC', 'Téléphone', 'Adresse', 'NIF', 'NIS', 'IA']);

// Récupérer tous les clients
$stmt = $pdo->query("SELECT id, nom, rc, telephone, adresse, nif, nis, ia FROM clients ORDER BY id DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [
        $row['id'],
        $row['nom'],
        $row['rc'],
        $row['telephone'],
        $row['adresse'],
        $row['nif'],
        $row['nis'],
        $row['ia']
    ]);
}
fclose($out);
exit;
