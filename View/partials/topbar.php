<?php
    // ============================================================
    // Partial: encabezado superior (logo + navbar + selector de rol)
    // Variables esperadas:
    //   $basePath        -> ver head.php
    //   $showRoleSwitch  -> (opcional, default true) el selector de rol
    //                       es solo una simulación de demo; se puede
    //                       ocultar en vistas que no lo necesitan.
    // ============================================================
    $basePath       = $basePath ?? '../../';
    $showRoleSwitch = $showRoleSwitch ?? true;
?>
<div class="main-header">
  <div class="main-header-logo">
    <div class="logo-header siguppys-logo-header" data-background-color="white">
      <a href="<?php echo $basePath; ?>Web/index.php" class="logo siguppys-logo">
        <span class="siguppys-pin">
          <img src="<?php echo $basePath; ?>assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
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
    <div class="container-fluid">
      <?php if($showRoleSwitch): ?>
      <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
        <li class="nav-item d-flex align-items-center">
          <div class="sig-role-switcher" title="Selector de rol para esta demostración. Cuando exista inicio de sesión, el rol vendrá de la sesión del usuario.">
            <label for="sigRoleSelect"><i class="fas fa-user-shield me-1"></i>Vista</label>
            <select id="sigRoleSelect">
              <option value="auxiliar">Auxiliar de campo</option>
              <option value="coordinador">Coordinador</option>
            </select>
          </div>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </nav>
</div>
<!-- End Header -->
