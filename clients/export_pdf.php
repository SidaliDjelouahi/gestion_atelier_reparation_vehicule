<?php
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once __DIR__ . '/../includes/fpdf186/fpdf.php';


// session_start(); if (!isset($_SESSION['user_id'])) { header('HTTP/1.1 403 Forbidden'); exit; }

// Check FPDF
$fpdfPath = __DIR__ . '/../includes/lib/fpdf.php';
if (!file_exists($fpdfPath)) {
    // fallback -> redirect to CSV download
    header("Location: export_csv.php");
    exit;
}

require_once($fpdfPath);

// Récupérer les clients
$stmt = $pdo->query("SELECT id, nom, rc, telephone, adresse, nif, nis, ia FROM clients ORDER BY id DESC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création du PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'Liste des clients', 0, 1, 'C');
$pdf->Ln(4);

// En-têtes de tableau
$pdf->SetFont('Arial', 'B', 10);
$w = [10, 50, 30, 30, 40, 20]; // colonnes: id, nom, rc, tel, adresse, nif/nis+ia combined
$pdf->Cell($w[0], 8, 'ID', 1);
$pdf->Cell($w[1], 8, 'Nom', 1);
$pdf->Cell($w[2], 8, 'RC', 1);
$pdf->Cell($w[3], 8, 'Téléphone', 1);
$pdf->Cell($w[4], 8, 'Adresse', 1);
$pdf->Cell($w[5], 8, 'NIF/NIS/IA', 1);
$pdf->Ln();

// Contenu
$pdf->SetFont('Arial', '', 9);
foreach ($clients as $c) {
    // préparation des cellules (troncature si trop long)
    $addr = (strlen($c['adresse']) > 40) ? substr($c['adresse'],0,37).'...' : $c['adresse'];
    $ids = trim($c['nif'].' '.$c['nis'].' '.$c['ia']);
    $pdf->Cell($w[0], 7, $c['id'], 1);
    $pdf->Cell($w[1], 7, utf8_decode($c['nom']), 1);
    $pdf->Cell($w[2], 7, utf8_decode($c['rc']), 1);
    $pdf->Cell($w[3], 7, utf8_decode($c['telephone']), 1);
    $pdf->Cell($w[4], 7, utf8_decode($addr), 1);
    $pdf->Cell($w[5], 7, utf8_decode($ids), 1);
    $pdf->Ln();
}

// Envoyer au navigateur
$filename = 'clients_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $filename);
exit;
