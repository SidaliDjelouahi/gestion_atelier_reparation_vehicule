<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $rc = trim($_POST['rc']);
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);
    $nif = trim($_POST['nif']);
    $nis = trim($_POST['nis']);
    $ia = trim($_POST['ia']);

    $stmt = $pdo->prepare("INSERT INTO clients (nom, rc, adresse, telephone, nif, nis, ia) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $rc, $adresse, $telephone, $nif, $nis, $ia]);
}

header("Location: table.php");
exit();
