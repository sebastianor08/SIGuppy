<?php
// =========================================================
// Sidebar de SIGuppys (menú lateral + logo)
// =========================================================
// Cada vista declara $rutaBase ANTES de este include, según su
// profundidad respecto a la raíz del proyecto (SIGuppy/):
//   Web/index.php                -> $rutaBase = '../';
//   View/<Modulo>/archivo.php    -> $rutaBase = '../../';
//
// El link activo (y el submenú que corresponde abrir) NO se marca
// aquí a mano: lo hace highlightActiveNav() en siguppys-nav.js
// leyendo el data-page del <body> de cada vista. Así no hay que
// tocar este archivo cuando cambia cuál página está activa.
if (!isset($rutaBase)) {
  $rutaBase = '../../';
}
?>
<!-- Sidebar -->
<div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header siguppys-logo-header">
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
    <!-- End Logo Header -->
  </div>

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-section">
          <span class="sidebar-mini-icon">
            <i class="fa fa-ellipsis-h"></i>
          </span>
          <h4 class="text-section">Menú</h4>
        </li>

        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>Web/index.php" data-page="resumen">
            <i class="fas fa-home"></i>
            <p>Resumen</p>
          </a>
        </li>

        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navReportes" aria-expanded="false">
            <i class="fas fa-chart-bar"></i>
            <p>Reportes</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navReportes">
            <ul class="nav nav-collapse">
              <li><a href="<?php echo $rutaBase; ?>View/Reportes/SeguimientoDeActividadesView.php" data-page="rep-actividades-zoo"><span class="sub-item">Seguimiento de Actividades en los Zoocriaderos</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Reportes/PesesNacidos-MuertosPorTanqueView.php" data-page="rep-peces-tanque"><span class="sub-item">Peces nacidos o muertos por tanque</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Reportes/TanquesPorZoocriaderoView.php" data-page="rep-tanques-zoo"><span class="sub-item">Tanques por Zoocriadero</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Reportes/ActividadesDeTerrenoPorTipoView.php" data-page="rep-terreno-tipo"><span class="sub-item">Actividades de Terreno por Tipo</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Reportes/ActividadesPorAuxiliarView.php" data-page="rep-terreno-auxiliar"><span class="sub-item">Actividades De Terreno Por Auxiliar Responsable</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Reportes/GráficoDeSitiosPorDepósitoView.php" data-page="rep-sitios-deposito"><span class="sub-item">Gráfico de Sitios por Tipo de Depósito</span></a></li>
            </ul>
          </div>
        </li>

        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>View/Zoocriadero/zoocriaderos.php" data-page="zoocriaderos">
            <i class="fas fa-warehouse"></i>
            <p>Zoocriaderos</p>
          </a>
        </li>

        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navTerreno" aria-expanded="false">
            <i class="fas fa-map-marker-alt"></i>
            <p>Terreno</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navTerreno">
            <ul class="nav nav-collapse">
              <li><a href="#" data-page="terreno-depositos"><span class="sub-item">Depósitos</span></a></li>
              <li><a href="#" data-page="terreno-actividades"><span class="sub-item">Actividades</span></a></li>
              <li><a href="#" data-page="terreno-tipo-depositos"><span class="sub-item">Tipo Depósitos</span></a></li>
            </ul>
          </div>
        </li>

        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navUsuarios" aria-expanded="false">
            <i class="fas fa-users"></i>
            <p>Usuarios</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navUsuarios">
            <ul class="nav nav-collapse">
              <li><a href="#" data-page="usuarios-registrar"><span class="sub-item">Registrar Usuario</span></a></li>
              <li><a href="#" data-page="usuarios-consultar"><span class="sub-item">Consultar Usuarios</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Roles/registro-roles.php" data-page="roles-registrar"><span class="sub-item">Roles y Permisos</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Roles/consultar-roles.php" data-page="roles-consultar"><span class="sub-item">Consultar Roles</span></a></li>
            </ul>
          </div>
        </li>

        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>View/CopiaSeguridad/copia-seguridad.php" data-page="copia-seguridad">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Copia de seguridad</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>View/Configuraciones/configuraciones.php" data-page="configuraciones">
            <i class="fas fa-cogs"></i>
            <p>Configuraciones</p>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-footer">
      <a href="#" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i>
        Cerrar Sesión
      </a>
    </div>
  </div>
</div>
<!-- End Sidebar -->