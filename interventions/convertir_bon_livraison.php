<?php
require_once("../includes/config.php");
require_once("../includes/db.php");
require_once("../includes/header.php"); // inclut le head complet

$id_intervention = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_intervention <= 0) {
    die("<div style='color:red;padding:20px;'>❌ ID d'intervention invalide.</div>");
}

// 🔹 Récupération du bon d’intervention
$sql = "SELECT b.*, c.nom AS client_nom, c.adresse AS client_adresse, c.rc AS client_rc, c.telephone 
        FROM bons_intervention b
        JOIN clients c ON b.id_client = c.id
        WHERE b.id_intervention = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_intervention]);
$bon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bon) {
    die("<div style='color:red;padding:20px;'>❌ Aucun bon trouvé pour l'intervention ID {$id_intervention}.</div>");
}

$id_bon = $bon['id'];

// 🔹 Récupération des détails du bon
$sql_details = "SELECT d.*, p.designation, p.ref 
                FROM bons_intervention_details d
                JOIN pieces p ON d.id_piece = p.id
                WHERE d.id_bon_intervention = ?";
$stmt = $pdo->prepare($sql_details);
$stmt->execute([$id_bon]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Fonction de conversion en lettres (PHP)
function convertirNombreEnLettres($montant) {
    $montant = number_format($montant, 2, '.', '');
    list($dinars, $centimes) = explode('.', $montant);
    $dinars = (int)$dinars;
    $centimes = (int)$centimes;

    $texte = ($dinars == 0) ? "zéro dinar" : trim(convertirEnLettres($dinars)) . " dinar" . ($dinars > 1 ? "s" : "");
    if ($centimes > 0) {
        $texte .= " et " . trim(convertirEnLettres($centimes)) . " centime" . ($centimes > 1 ? "s" : "");
    }
    return ucfirst($texte);
}

function convertirEnLettres($n) {
    $unit = [
        0=>'zéro',1=>'un',2=>'deux',3=>'trois',4=>'quatre',5=>'cinq',6=>'six',
        7=>'sept',8=>'huit',9=>'neuf',10=>'dix',11=>'onze',12=>'douze',13=>'treize',
        14=>'quatorze',15=>'quinze',16=>'seize',17=>'dix-sept',18=>'dix-huit',19=>'dix-neuf'
    ];
    $diz = [
        2=>'vingt',3=>'trente',4=>'quarante',5=>'cinquante',6=>'soixante',
        7=>'soixante-dix',8=>'quatre-vingt',9=>'quatre-vingt-dix'
    ];

    if ($n < 20) return $unit[$n];
    if ($n < 100) {
        $d = intdiv($n, 10);
        $r = $n % 10;
        if ($n == 71) return "soixante et onze";
        if ($n == 80) return "quatre-vingts";
        if ($n == 81) return "quatre-vingt-un";
        return $diz[$d] . ($r == 1 && $d < 8 ? " et un" : ($r ? "-" . $unit[$r] : ""));
    }
    if ($n < 1000) {
        $c = intdiv($n, 100);
        $r = $n % 100;
        $cent = ($c == 1 ? "cent" : $unit[$c] . " cent" . ($r == 0 ? "s" : ""));
        return $cent . ($r ? " " . convertirEnLettres($r) : "");
    }
    if ($n < 1000000) {
        $m = intdiv($n, 1000);
        $r = $n % 1000;
        $mille = ($m == 1 ? "mille" : convertirEnLettres($m) . " mille");
        return $mille . ($r ? " " . convertirEnLettres($r) : "");
    }
    return (string)$n;
}
?>

<style>
@media print {
    #btnSave, #btnPrint {
        display: none !important;
    }
}
</style>

<div class="container py-4">
    <div class="text-center border-bottom border-2 border-dark mb-3">
        <h1 class="fw-bold">GENYTECH</h1>
        <h4>Atelier de réparation & maintenance</h4>
        <p class="mb-1">Adresse : Blida - Algérie</p>
        <p>Tél : +213 555 55 55 55</p>
        <hr>
        <h4>Bon d’intervention N° <?= htmlspecialchars($bon['num_bon']) ?></h4>
        <p>Date : <?= htmlspecialchars($bon['date_bon']) ?></p>
    </div>

    <h4>Client : <?= htmlspecialchars($bon['client_nom']) ?></h4>
    <p>
        Adresse : <?= htmlspecialchars($bon['client_adresse'] ?? 'N/A') ?><br>
        RC : <?= htmlspecialchars($bon['client_rc'] ?? 'N/A') ?><br>
        Téléphone : <?= htmlspecialchars($bon['telephone'] ?? 'N/A') ?>
    </p>

    <?php if (empty($details)): ?>
        <div class="alert alert-warning text-center">⚠️ Aucun détail trouvé.</div>
    <?php else: ?>
    <form id="formBon">
        <table class="table table-bordered text-center align-middle" id="tablePieces">
            <thead class="table-light">
                <tr>
                    <th>Réf</th>
                    <th>Désignation</th>
                    <th>Quantité</th>
                    <th>Prix unitaire HT (DA)</th>
                    <th>Total HT (DA)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_ht = 0;
                foreach ($details as $d): 
                    $sous_total = $d['quantite'] * $d['prix_vente'];
                    $total_ht += $sous_total;
                ?>
                <tr data-id-piece="<?= $d['id_piece'] ?>">
                    <td><?= htmlspecialchars($d['ref']) ?></td>
                    <td><?= htmlspecialchars($d['designation']) ?></td>
                    <td><?= $d['quantite'] ?></td>
                    <td><input type="number" step="0.01" name="prix_vente[]" class="form-control text-center prix" value="<?= $d['prix_vente'] ?>"></td>
                    <td class="sous-total"><?= number_format($sous_total, 2, ',', ' ') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-end">
            <p><strong>Total HT :</strong> <span id="totalHT"><?= number_format($total_ht, 2, ',', ' ') ?></span> DA</p>
            <p><strong>TVA (19%) :</strong> <span id="tva"><?= number_format($total_ht * 0.19, 2, ',', ' ') ?></span> DA</p>
            <p><strong>Total TTC :</strong> <span id="totalTTC"><?= number_format($total_ht * 1.19, 2, ',', ' ') ?></span> DA</p>

            <label><strong>Versement (DA):</strong></label>
            <input type="number" id="versement" name="versement" class="form-control d-inline-block text-center" style="width:200px;"
                value="<?= number_format($total_ht * 1.19, 2, '.', '') ?>">
        </div>

        <p class="mt-3"><strong>Montant en lettres :</strong> 
            <span id="montantLettres"><?= convertirNombreEnLettres(round($total_ht * 1.19)) ?></span>
        </p>

        <div class="text-end mt-4">
            <p>Signature client : __________________________</p>
        </div>

        <div class="text-end mt-3">
            <button type="button" class="btn btn-primary" id="btnSave">💾 Enregistrer comme bon de livraison</button>
            <button type="button" class="btn btn-secondary" id="btnPrint">🖨️ Imprimer</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function convertirNombreEnLettres(n) {
    const unite = ["zéro","un","deux","trois","quatre","cinq","six","sept","huit","neuf",
                   "dix","onze","douze","treize","quatorze","quinze","seize",
                   "dix-sept","dix-huit","dix-neuf"];
    const dizaine = ["", "", "vingt","trente","quarante","cinquante","soixante","soixante-dix","quatre-vingt","quatre-vingt-dix"];
    if (n < 20) return unite[n];
    if (n < 100) {
        let d = Math.floor(n / 10);
        let r = n % 10;
        let txt = dizaine[d];
        if (r === 1 && d < 8) txt += " et un";
        else if (r > 0) txt += "-" + unite[r];
        return txt;
    }
    if (n < 1000) {
        let c = Math.floor(n / 100);
        let r = n % 100;
        let txt = (c > 1 ? unite[c] + " cent" : "cent");
        if (r > 0) txt += " " + convertirNombreEnLettres(r);
        return txt;
    }
    if (n < 1000000) {
        let m = Math.floor(n / 1000);
        let r = n % 1000;
        let txt = (m > 1 ? convertirNombreEnLettres(m) + " mille" : "mille");
        if (r > 0) txt += " " + convertirNombreEnLettres(r);
        return txt;
    }
    return n.toString();
}

function recalculerTotaux() {
    let totalHT = 0;
    $("#tablePieces tbody tr").each(function() {
        let quantite = parseFloat($(this).find("td:nth-child(3)").text());
        let prix = parseFloat($(this).find(".prix").val());
        let sousTotal = quantite * prix;
        $(this).find(".sous-total").text(sousTotal.toFixed(2));
        totalHT += sousTotal;
    });
    let tva = totalHT * 0.19;
    let ttc = totalHT + tva;
    $("#totalHT").text(totalHT.toFixed(2));
    $("#tva").text(tva.toFixed(2));
    $("#totalTTC").text(ttc.toFixed(2));
    $("#versement").val(ttc.toFixed(2));
    $("#montantLettres").text(convertirNombreEnLettres(Math.round(ttc)) + " dinars");
}

$(".prix").on("input", recalculerTotaux);
$("#btnPrint").click(() => window.print());

$("#btnSave").on("click", function(e) {
    e.preventDefault();

    let donnees = {
        id_intervention: <?= $id_intervention ?>,
        versement: $("#versement").val(),
        pieces: []
    };

    $("#tablePieces tbody tr").each(function() {
        donnees.pieces.push({
            id_piece: $(this).data("id-piece"),
            quantite: parseFloat($(this).find("td:nth-child(3)").text()),
            prix_vente: parseFloat($(this).find(".prix").val())
        });
    });

    $.ajax({
        url: "<?= ROOT_URL ?>/interventions/enregistrer_bon.php",
        type: "POST",
        data: { data: JSON.stringify(donnees) },
        success: function(rep) {
            alert(rep);
            window.location.href = "<?= ROOT_URL ?>/bons_livraison/liste.php";
        },
        error: function(xhr, status, error) {
            alert("❌ Erreur AJAX : " + error + "\n" + xhr.responseText);
        }
    });
});
</script>

</body>
</html>
