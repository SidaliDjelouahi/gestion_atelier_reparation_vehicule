<?php
session_start();
require_once("../includes/config.php");
require_once("../includes/db.php");

// ✅ Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: " . ROOT_URL . "/default.php");
    exit;
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

<style>
body { overflow-x: hidden; }
.table td, .table th { vertical-align: middle; }

@media (max-width: 768px) {
    .desktop-table { display: none; }
    .mobile-table { display: table; width: 100%; border-collapse: collapse; }
    .mobile-table th, .mobile-table td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }
    .mobile-table th {
        background: #f8f9fa;
        color: #0d6efd;
        font-weight: bold;
    }
    .mobile-table td:first-child {
        text-align: left;
        font-weight: 500;
    }
    .mobile-table .btn { width: 100%; padding: 5px; }
}

@media (min-width: 769px) {
    .mobile-table { display: none; }
}

.piece-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.piece-item input[type='number'] {
    width: 80px;
}
</style>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h5 class="fw-bold text-primary mb-2"><i class="bi bi-tools"></i> Interventions</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
            <i class="bi bi-plus-circle"></i> Ajouter
        </button>
    </div>

    <!-- 🔍 Recherche AJAX -->
    <div class="input-group mb-3">
        <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
        <input type="text" id="search" class="form-control" placeholder="Rechercher une intervention..." autocomplete="off">
    </div>

    <!-- 🔁 Contenu chargé dynamiquement -->
    <div id="resultats"></div>
</div>

<!-- 🆕 MODAL AJOUT COMPLET -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= ROOT_URL ?>/interventions/ajouter.php" method="POST" id="formIntervention">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nouvelle intervention</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- 🚗 Recherche véhicule AJAX -->
          <div class="mb-3 position-relative">
              <label class="form-label">Véhicule *</label>
              <input type="text" id="searchVehicule" class="form-control" placeholder="Marque, modèle ou matricule..." autocomplete="off" required>
              <input type="hidden" name="id_vehicule" id="idVehicule">
              <div id="listeVehicules" class="list-group position-absolute w-100" style="z-index: 1000; max-height:200px; overflow-y:auto; display:none;"></div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Date d’intervention *</label>
              <input type="datetime-local" name="date_intervention" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Kilométrage</label>
              <input type="number" name="km" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>

          <hr>

          <!-- 🔧 Moteur de recherche de pièces -->
          <h6 class="fw-bold text-secondary"><i class="bi bi-gear"></i> Pièces utilisées</h6>

          <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchPiece" class="form-control" placeholder="Rechercher une pièce..." autocomplete="off">
          </div>

          <div id="listePieces" class="border rounded p-2 bg-light" style="max-height:200px; overflow-y:auto; display:none;"></div>

          <!-- 🧩 Zone d’ajout dynamique -->
          <div id="piecesSelectionnees" class="mt-3"></div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// 🔎 Recherche AJAX pour interventions
function chargerResultats(query = '') {
    fetch('<?= ROOT_URL ?>/interventions/search_ajax.php?search=' + encodeURIComponent(query))
        .then(res => res.text())
        .then(html => document.getElementById('resultats').innerHTML = html)
        .catch(err => console.error('Erreur AJAX:', err));
}
chargerResultats();

let timer;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(timer);
    timer = setTimeout(() => chargerResultats(this.value), 300);
});

// 🧩 Recherche de pièces
const searchPiece = document.getElementById('searchPiece');
const listePieces = document.getElementById('listePieces');
const zonePieces = document.getElementById('piecesSelectionnees');

searchPiece.addEventListener('input', () => {
    const query = searchPiece.value.trim();
    if (query.length < 2) {
        listePieces.style.display = 'none';
        return;
    }
    fetch('<?= ROOT_URL ?>/interventions/search_piece_ajax.php?search=' + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            listePieces.innerHTML = '';
            if (data.length === 0) {
                listePieces.innerHTML = '<small class="text-muted px-2">Aucune pièce trouvée.</small>';
            } else {
                data.forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'p-2 border-bottom piece-result';
                    item.style.cursor = 'pointer';
                    item.innerHTML = `<strong>${p.ref}</strong> - ${p.designation} <span class="text-muted">(${p.prix_vente_ht} DA)</span>`;
                    item.onclick = () => ajouterPiece(p);
                    listePieces.appendChild(item);
                });
            }
            listePieces.style.display = 'block';
        })
        .catch(err => console.error('Erreur:', err));
});

// 🧱 Liste des pièces ajoutées
let piecesAjoutees = [];

function ajouterPiece(piece) {
    if (piecesAjoutees.find(p => p.id === piece.id)) return; // éviter doublons
    piecesAjoutees.push(piece);

    const div = document.createElement('div');
    div.className = 'piece-item border p-2 rounded bg-white';
    div.dataset.id = piece.id;
    div.innerHTML = `
        <input type="hidden" name="pieces[id][]" value="${piece.id}">
        <span class="flex-grow-1">${piece.ref} - ${piece.designation}</span>
        <input type="number" name="pieces[qte][]" value="1" min="1" class="form-control form-control-sm">
        <button type="button" class="btn btn-outline-danger btn-sm"><i class="bi bi-dash-circle"></i></button>
    `;
    div.querySelector('button').onclick = () => {
        zonePieces.removeChild(div);
        piecesAjoutees = piecesAjoutees.filter(p => p.id !== piece.id);
    };
    zonePieces.appendChild(div);
    listePieces.style.display = 'none';
    searchPiece.value = '';
}

// 🔎 Recherche AJAX véhicules
const searchVehicule = document.getElementById('searchVehicule');
const listeVehicules = document.getElementById('listeVehicules');
const idVehicule = document.getElementById('idVehicule');

let timerVehicule;
searchVehicule.addEventListener('input', () => {
    const query = searchVehicule.value.trim();
    if (query.length < 2) {
        listeVehicules.style.display = 'none';
        idVehicule.value = '';
        return;
    }

    clearTimeout(timerVehicule);
    timerVehicule = setTimeout(() => {
        fetch('<?= ROOT_URL ?>/interventions/search_vehicule_ajax.php?search=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                listeVehicules.innerHTML = '';
                if (data.length === 0) {
                    listeVehicules.innerHTML = '<small class="text-muted px-2">Aucun véhicule trouvé.</small>';
                } else {
                    data.forEach(v => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${v.marque} ${v.modele} (${v.matricule})`;
                        item.dataset.id = v.id;
                        item.onclick = () => {
                            searchVehicule.value = item.textContent;
                            idVehicule.value = v.id;
                            listeVehicules.style.display = 'none';
                        };
                        listeVehicules.appendChild(item);
                    });
                }
                listeVehicules.style.display = 'block';
            })
            .catch(err => console.error('Erreur AJAX véhicules:', err));
    }, 300);
});

</script>

<?php include("../includes/footer.php"); ?>
