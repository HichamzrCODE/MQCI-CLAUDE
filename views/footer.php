<?php
// Sécurité : si $action n'est pas défini dans ce scope
$action = $action ?? ($_GET['action'] ?? '');
$prefix = explode('/', (string)$action, 2)[0];
?>

    </main>
</div>

<!-- ============== FOOTER (fin et discret) ============== -->
<footer class="app-footer">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
 
    </div>
  </div>
</footer>

<!-- ============== SCRIPTS (FIN DE PAGE) ============== -->
<!-- IMPORTANT: ordre obligatoire (Bootstrap 4) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Script global -->
<script src="/MAQCI/public/js/script.js"></script>

<!-- ✅ Scripts uniquement sur devis/* -->
<?php if ($prefix === 'devis'): ?>
  <script src="/MAQCI/public/js/devis-lignes.js"></script>
  <script src="/MAQCI/public/js/devis-validation.js"></script>
  <script src="/MAQCI/public/js/articles-modal.js"></script>
  <script src="/MAQCI/public/js/unsaved-warning.js"></script>
<?php endif; ?>

<script>
$(function () {
  var btn = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');

  function closeSidebar(){
    if (sidebar) sidebar.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
  }

  function toggleSidebar(){
    if (!sidebar) return;
    sidebar.classList.toggle('show');
    if (overlay) overlay.classList.toggle('show');
  }

  if (btn && sidebar) {
    btn.addEventListener('click', function () {
      toggleSidebar();
    });
  }

  if (overlay) {
    overlay.addEventListener('click', function () {
      closeSidebar();
    });
  }

  // Tooltips Bootstrap 4 (global)
  $('[data-toggle="tooltip"]').tooltip();

  // Fermer la sidebar au clic sur un lien (mobile)
  document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.innerWidth <= 992) closeSidebar();
    });
  });
});
</script>

</body>
</html>