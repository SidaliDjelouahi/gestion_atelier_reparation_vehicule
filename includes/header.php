<?php
// includes/header.php
require_once __DIR__ . "/config.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Atelier</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Ton CSS perso -->
    <link rel="stylesheet" href="<?php echo ROOT_URL; ?>/assets/css/style.css">

    <!-- Manifest PWA -->
    <link rel="manifest" href="<?php echo ROOT_URL; ?>/manifest.json">

    <!-- Theme color and icons -->
    <meta name="theme-color" content="#0b5a8a">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo ROOT_URL; ?>/icon-192.png">
    <link rel="apple-touch-icon" href="<?php echo ROOT_URL; ?>/icon-512.png">
</head>
<body>

<div class="container-fluid">
    <div class="row">
