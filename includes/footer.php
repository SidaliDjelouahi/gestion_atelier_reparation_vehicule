
    </div> <!-- .row -->
</div> <!-- .container-fluid -->

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Enregistrement du service worker -->
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?php echo ROOT_URL; ?>/sw.js')
      .then(reg => console.log('Service Worker enregistré :', reg.scope))
      .catch(err => console.warn('Erreur enregistrement SW :', err));
  });
}
</script>

</body>
</html>
