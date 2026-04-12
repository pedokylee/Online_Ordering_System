</div><!-- /content-area -->

<footer class="site-footer">
  <span>FeastFlow &mdash; Food Online Ordering System</span>
  <span>
    <a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php">Dashboard</a>
    &nbsp;&middot;&nbsp;
    <a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/orders.php">Orders</a>
    &nbsp;&middot;&nbsp;
    <a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/products.php">Menu</a>
  </span>
</footer>

</div><!-- /main-content -->
</div><!-- /layout-wrapper -->

<script>
(function() {
  var toggle  = document.getElementById('menuToggle');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  if (!toggle) return;

  function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', function() {
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
  });
  overlay.addEventListener('click', closeSidebar);

  // Close on Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
  });
})();
</script>
</body>
</html>