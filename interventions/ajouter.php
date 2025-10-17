<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// 🔒 Vérification de session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_vehicule = (int)$_POST['id_vehicule'];
    $date_intervention = $_POST['date_intervention'];
    $km = !empty($_POST['km']) ? (int)$_POST['km'] : null;
    $description = trim($_POST['description']);

    $pdo->beginTransaction();
    try {
        // 🔍 Récupérer le client associé au véhicule
        $stmt = $pdo->prepare("SELECT id_client FROM vehicules WHERE id = ?");
        $stmt->execute([$id_vehicule]);
        $id_client = $stmt->fetchColumn();

        if (!$id_client) {
            throw new Exception("Aucun client associé à ce véhicule !");
        }

        // ➕ Ajouter l’intervention
        $stmt = $pdo->prepare("
            INSERT INTO interventions (id_vehicule, date_intervention, km, description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_vehicule, $date_intervention, $km, $description]);
        $id_intervention = $pdo->lastInsertId();

        // ➕ Créer le bon d’intervention
        $stmt = $pdo->prepare("
            INSERT INTO bons_intervention (num_bon, id_client, date_bon)
            VALUES (?, ?, ?)
        ");
        $stmt->execute(['BON-' . $id_intervention, $id_client, date('Y-m-d')]);
        $id_bon = $pdo->lastInsertId();

        // ➕ Détails des pièces et mise à jour du stock
        if (!empty($_POST['pieces']['id'])) {
            $stmtDetail = $pdo->prepare("
                INSERT INTO bons_intervention_details (id_bon_intervention, id_piece, quantite, prix_vente)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($_POST['pieces']['id'] as $i => $id_piece) {
                $id_piece = (int)$id_piece;
                $qte = (int)$_POST['pieces']['qte'][$i];

                // 🔍 Vérifier la quantité disponible
                $p = $pdo->prepare("SELECT quantite, prix_vente_ht FROM pieces WHERE id=?");
                $p->execute([$id_piece]);
                $piece = $p->fetch(PDO::FETCH_ASSOC);

                if (!$piece) {
                    throw new Exception("Pièce ID {$id_piece} introuvable !");
                }

                if ($piece['quantite'] < $qte) {
                    throw new Exception("Stock insuffisant pour la pièce ID {$id_piece} ({$piece['quantite']} disponible, {$qte} demandé)");
                }

                // ➕ Ajouter le détail
                $stmtDetail->execute([$id_bon, $id_piece, $qte, $piece['prix_vente_ht']]);

                // 🔄 Mettre à jour le stock
                $update = $pdo->prepare("UPDATE pieces SET quantite = quantite - ? WHERE id = ?");
                $update->execute([$qte, $id_piece]);
            }
        }

        $pdo->commit();

        // ✅ Redirection
        header("Location: " . ROOT_URL . "/interventions/table.php?success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='color:red;text-align:center;margin-top:50px;'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</div>");
    }
}
?>
