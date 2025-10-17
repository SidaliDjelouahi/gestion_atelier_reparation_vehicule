<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

// ✅ Vérification que le formulaire a bien été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération sécurisée des champs
    $id_client   = isset($_POST['id_client']) ? (int)$_POST['id_client'] : 0;
    $matricule   = trim($_POST['matricule'] ?? '');
    $marque      = trim($_POST['marque'] ?? '');
    $modele      = trim($_POST['modele'] ?? '');
    $num_chassis = trim($_POST['num_chassis'] ?? '');
    $km_initial  = trim($_POST['km_initial'] ?? '');

    // ✅ Validation minimale
    if ($id_client <= 0 || $matricule === '') {
        $_SESSION['error'] = "Veuillez remplir les champs obligatoires.";
        header("Location: " . ROOT_URL . "/vehicules/table.php");
        exit;
    }

    try {
        // ✅ Insertion en base de données
        $stmt = $pdo->prepare("
            INSERT INTO vehicules (id_client, matricule, marque, modele, num_chassis, km_initial)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_client,
            $matricule,
            $marque,
            $modele,
            $num_chassis,
            $km_initial
        ]);

        $_SESSION['success'] = "Véhicule ajouté avec succès.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'ajout du véhicule : " . $e->getMessage();
    }

    // 🔁 Retour à la liste
    header("Location: " . ROOT_URL . "/vehicules/table.php");
    exit;
}

// Si quelqu’un accède à la page directement
header("Location: " . ROOT_URL . "/vehicules/table.php");
exit;
