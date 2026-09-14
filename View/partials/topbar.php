<?php
  // =========================================================
  // Navbar superior de SIGuppys (logo del header + selector de rol)
  // =========================================================
  // Igual que sidebar.php, usa $rutaBase (declarada por la vista)
  // para las rutas de assets/links según su profundidad.
  if (!isset($rutaBase)) {
      $rutaBase = '../../';
  }
?>
<div class="main-header">
  <div class="main-header-logo">
    <div class="logo-header siguppys-logo-header" data-background-color="white">
      <a href="<?php echo $rutaBase; ?>Web/index.php" class="logo siguppys-logo">
        <span class="siguppys-pin">
          <img src="<?php echo $rutaBase; ?>assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
        </span>
        <span class="siguppys-brand">
          <strong>SIGuppys</strong>
          <small>Control Biológico contra el Dengue</small>
        </span>
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
    </div>
  </div>
  <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid"></div>
  </nav>
  <!-- End Navbar -->
</div>
