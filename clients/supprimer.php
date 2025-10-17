<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: table.php");
exit();
