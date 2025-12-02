<?php
require_once("../includes/config.php");
require_once("../includes/db.php");

header('Content-Type: text/plain; charset=utf-8');

// 🔹 Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("❌ Méthode non autorisée.");
}

// 🔹 Lecture des données JSON envoyées
$data = isset($_POST['data']) ? json_decode($_POST['data'], true) : null;
if (!$data || !isset($data['id_intervention'])) {
    http_response_code(400);
    exit("❌ Données invalides ou incomplètes.");
}

$id_intervention = (int)$data['id_intervention'];
$versement = (float)$data['versement'];
$pieces = $data['pieces'] ?? [];

try {
    $pdo->beginTransaction();

    // 🔹 Vérifier que l’intervention existe
    $sql = "SELECT id_client FROM bons_intervention WHERE id_intervention = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_intervention]);
    $intervention = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$intervention) {
        throw new Exception("Aucune intervention trouvée pour ID $id_intervention");
    }

    // 🔹 Création du bon
    $sql = "INSERT INTO bons (id_bon_intervention, date, versement)
            VALUES (?, NOW(), ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_intervention, $versement]);
    $id_bon = $pdo->lastInsertId();

    // 🔹 Insertion des détails du bon
    foreach ($pieces as $p) {
        $id_piece = (int)$p['id_piece'];
        $quantite = (float)$p['quantite'];
        $prix_vente = (float)$p['prix_vente'];

        $sql = "INSERT INTO bons_details (id_bon, id_piece, prix_vente, quantite)
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_bon, $id_piece, $prix_vente, $quantite]);
    }

    // 🔹 Marquer l’intervention comme convertie
    $sql = "UPDATE bons_intervention 
            SET statut = 'converti', bon_id = ? 
            WHERE id_intervention = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bon, $id_intervention]);

    $pdo->commit();

    echo "✅ Bon enregistré avec succès (ID : $id_bon)";
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "❌ Erreur lors de l’enregistrement : " . $e->getMessage();
}
