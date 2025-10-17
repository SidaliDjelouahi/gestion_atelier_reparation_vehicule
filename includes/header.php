<?php
// includes/header.php

// --- Charger la configuration ---
require_once __DIR__ . "/config.php";

// --- Déterminer le bon chemin de base ---
$baseURL = rtrim(ROOT_URL, '/'); // 🔥 supprime le / final pour éviter les doubles //
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Atelier</title>

    <!-- ✅ Balise <base> pour uniformiser tous les liens relatifs -->
    <base href="<?= htmlspecialchars($baseURL); ?>/">

    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- ✅ Manifest PWA -->
    <link rel="manifest" href="manifest.json">

    <!-- ✅ Couleur et icônes -->
    <meta name="theme-color" content="#0b5a8a">
    <link rel="icon" type="image/png" sizes="192x192" href="icon-192.png">
    <link rel="apple-touch-icon" href="icon-512.png">

    <!-- ✅ Style interne (si tu n’as pas de dossier assets/css) -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar, .card-header {
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
