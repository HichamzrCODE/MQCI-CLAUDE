<?php include '../views/layout.php'; ?>
<?php require_once __DIR__ . '/../../includes/permissions.php'; ?>
<style>
.card.compact-card {
    padding: 0.3rem 0.4rem;
    font-size: 0.92rem;
    min-height: 72px;
    border-radius: 0.6rem;
    border: 1px solid #e4e4e4;
    margin-bottom: 0.6rem;
}
.card.compact-card .card-title {
    font-size: 1.01rem;
    margin-bottom: 0.4rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
}
.card.compact-card .btn {
    font-size: 0.83rem;
    padding: 0.07rem 0.48rem;
    margin-bottom: 0.07rem;
    border-radius: 0.35rem;
}
.card.compact-card .btn + .btn {margin-left: 0.08rem;}
@media (max-width: 767.98px) {
    .card.compact-card { font-size:0.96rem; min-height: 56px;}
    .card.compact-card .card-title { max-width: 90px; }
    .card.compact-card .btn { font-size:0.95rem; padding:0.07rem 0.39rem;}
}
#releves-list .col-md-3 { flex: 0 0 22%; max-width: 22%; }
@media (max-width: 991.98px) {
    #releves-list .col-md-3 { flex: 0 0 33%; max-width: 33%; }
}
@media (max-width: 767.98px) {
    #releves-list .col-md-3 { flex: 0 0 47%; max-width: 47%; }
}
@media (max-width: 575.98px) {
    #releves-list .col-md-3 { flex: 0 0 100%; max-width: 100%; }
}
</style>
<div class="container">
  <h1 style="margin-top:25px; font-size:1.21rem;">Liste des relevés clients</h1>
  <div class="row mb-2">
    <div class="col-sm-6 col-md-4">
      <input type="text" id="client-search" class="form-control form-control-sm" placeholder="Rechercher un client...">
    </div>
  </div>
  <?php if(hasPermission('releve', 'create')): ?>
    <a href="index.php?action=releve/create" class="btn btn-success mb-3" style="font-size: 0.9rem;">Créer un relevé</a>
  <?php endif; ?>
  <div class="row" id="releves-list">
  <?php foreach($releves as $releve): ?>
    <div class="col-md-3 col-sm-6 col-12 mb-2 releve-card">
      <div class="card compact-card shadow-sm">
        <div class="card-body p-2">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0" title="<?=htmlspecialchars($releve['nom'])?>"><?=htmlspecialchars($releve['nom'])?></h5>
            <button class="btn btn-link p-0" title="Voir rapide"
              onclick="voirRelevePopup(<?= $releve['id'] ?>)">
              <span class="fa fa-eye"></span>
            </button>
          </div>
          <div class="mt-2">
            <?php if(hasPermission('releve', 'view')): ?>
              <a href="index.php?action=releve/show&id=<?= $releve['id'] ?>" class="btn btn-primary btn-sm">Voir</a>
            <?php endif; ?>
            <?php if(hasPermission('releve', 'edit')): ?>
              <a href="index.php?action=releve/edit&id=<?=$releve['id']?>" class="btn btn-warning btn-sm">Modifier</a>
            <?php endif; ?>
            <?php if(hasPermission('releve', 'delete')): ?>
              <a href="index.php?action=releve/delete&id=<?=$releve['id']?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Supprimer ?')">X</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>

<!-- Modal pour afficher le relevé -->
<div class="modal fade" id="releveModal" tabindex="-1" role="dialog" aria-labelledby="releveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        <h5 class="modal-title" id="releveModalLabel">Relevé client</h5>
        <div class="ms-auto d-flex align-items-center">
          <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="printReleve()" title="Imprimer ce relevé">Imprimer</button>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
      <div class="modal-body" id="releveModalBody">
        <!-- Le relevé sera chargé ici -->
      </div>
    </div>
  </div>
</div>

<script>
// Fonction pour charger le relevé en AJAX dans la popup
function voirRelevePopup(releve_id) {
  document.getElementById('releveModalBody').innerHTML = "<div class='text-center my-4'><span>Chargement...</span></div>";
  $('#releveModal').modal('show');
  fetch('index.php?action=releve/ajax&id=' + releve_id)
    .then(response => response.json())
    .then(data => {
      if(data.status === "ok") {
        document.getElementById('releveModalBody').innerHTML = data.html;
      } else {
        document.getElementById('releveModalBody').innerHTML = data.html;
      }
    })
    .catch(() => {
      document.getElementById('releveModalBody').innerHTML = "<div class='alert alert-danger'>Erreur lors du chargement.</div>";
    });
}
function printReleve() {
  var printContents = document.getElementById('releveModalBody').innerHTML;
  var printWindow = window.open('', '', 'height=700,width=900');
  let bootstrapCss = '';
  if (document.querySelector('link[href*="bootstrap"]')) {
    bootstrapCss = '<link rel="stylesheet" href="' + document.querySelector('link[href*="bootstrap"]').href + '">';
  }
  printWindow.document.write('<html><head><title>Impression relevé</title>' + bootstrapCss + '</head><body>');
  printWindow.document.write(printContents);
  printWindow.document.write('</body></html>');
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}

// Barre de recherche client
document.getElementById('client-search').addEventListener('keyup', function() {
  let search = this.value.toLowerCase();
  document.querySelectorAll('#releves-list .releve-card').forEach(function(card) {
    let name = card.querySelector('.card-title').textContent.toLowerCase();
    card.style.display = name.indexOf(search) !== -1 ? '' : 'none';
  });
});
</script>