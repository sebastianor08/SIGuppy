<?php

  if (!isset($rutaBase)) {
      $rutaBase = '../../';
  }
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  $sigNombreUsuarioTopbar = $_SESSION['usuario'] ?? 'SIGuppys';
?>
<div class="main-header">
  <div class="main-header-logo">
    <div class="logo-header siguppys-logo-header" data-background-color="white">
      <a href="<?php echo $rutaBase; ?>Web/index.php" class="logo siguppys-logo">
        <span class="siguppys-pin">
          <img src="<?php echo $rutaBase; ?>Web/assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
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
    <div class="container-fluid justify-content-end">
      <div class="dropdown">
        <a class="btn dropdown-toggle d-flex align-items-center gap-2" href="#" role="button"
           data-bs-toggle="dropdown" aria-expanded="false">
          <span class="avatar avatar-sm">
            <i class="fas fa-user-circle" style="font-size: 28px;"></i>
          </span>
          <span class="d-none d-md-inline"><?php echo htmlspecialchars($sigNombreUsuarioTopbar, ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="<?php echo $rutaBase; ?>View/Perfil/perfil.php" data-page="perfil">
              <i class="fas fa-user-circle me-2"></i>Mi Perfil
            </a>
          </li>
          <li><hr class="dropdown-divider" /></li>
          <li>
            <a class="dropdown-item text-danger" href="<?php echo $rutaBase; ?>Controller/login/logout.php">
              <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
</div>