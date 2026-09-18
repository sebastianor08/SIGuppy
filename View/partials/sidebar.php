<?php

  if (!isset($rutaBase)) {
      $rutaBase = '../../';
  }

  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  // Si hay un rol real de sesión (login ya deja $_SESSION['id_rol']),
  // se respeta el permiso "Ver" de ese rol para decidir qué aparece en
  // el menú. Sin sesión (como en las pruebas directas que se han hecho
  // hasta ahora) se muestra todo, igual que el resto del sistema
  // mientras no haya un login obligatorio en cada página.
  $idRolSesionSidebar = $_SESSION['id_rol'] ?? null;
  $rolesModeloSidebar = null;
  if ($idRolSesionSidebar) {
      include_once __DIR__ . '/../../Model/Roles/RolesModel.php';
      $rolesModeloSidebar = new RolesModel();
  }

  function sigPuedeVer($nombreModulo) {
      global $idRolSesionSidebar, $rolesModeloSidebar;
      if (!$idRolSesionSidebar || !$rolesModeloSidebar) {
          return true;
      }
      return $rolesModeloSidebar->tienePermisoPorId($idRolSesionSidebar, $nombreModulo, 'Ver');
  }

  $sigMostrarReportes  = sigPuedeVer('Reportes');
  $sigMostrarZoo       = sigPuedeVer('Zoocriaderos') || sigPuedeVer('Tanque Zoocriadero') || sigPuedeVer('Acciones');
  $sigMostrarTerreno   = sigPuedeVer('Depósitos') || sigPuedeVer('Actividades') || sigPuedeVer('Tipo Depósitos');
  $sigMostrarUsuarios  = sigPuedeVer('Gestión de Usuarios') || sigPuedeVer('Roles y Permisos') || sigPuedeVer('Consultar Usuarios');
?>
<div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
  <div class="sidebar-logo">
    <div class="logo-header siguppys-logo-header">
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

        <?php if ($sigMostrarReportes): ?>
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
        <?php endif; ?>

        <?php if ($sigMostrarZoo): ?>
        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navZoocriaderos" aria-expanded="false">
            <i class="fas fa-warehouse"></i>
            <p>Zoocriaderos</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navZoocriaderos">
            <ul class="nav nav-collapse">
              <?php if (sigPuedeVer('Zoocriaderos')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/Zoocriadero/zoocriaderos.php" data-page="zoocriaderos"><span
                    class="sub-item">Zoocriaderos</span></a></li>
              <?php endif; ?>
              <?php if (sigPuedeVer('Tanque Zoocriadero')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/Tanque/tanques.php" data-page="tanques"><span
                    class="sub-item">Tanques</span></a></li>
              <?php endif; ?>
              <?php if (sigPuedeVer('Acciones')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/Acciones/acciones.php" data-page="acciones-zoocriadero"><span
                    class="sub-item">Acciones</span></a></li>
              <?php endif; ?>
            </ul>
          </div>
        </li>
        <?php endif; ?>


        <?php if ($sigMostrarTerreno): ?>
        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navTerreno" aria-expanded="false">
            <i class="fas fa-map-marker-alt"></i>
            <p>Terreno</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navTerreno">
            <ul class="nav nav-collapse">
              <?php if (sigPuedeVer('Depósitos')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/Deposito/DepositoView.php" data-page="terreno-depositos"><span class="sub-item">Depósitos</span></a></li>
              <?php endif; ?>
              <?php if (sigPuedeVer('Actividades')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/Actividad/ActividadView.php" data-page="terreno-actividades"><span class="sub-item">Actividades</span></a></li>
              <?php endif; ?>
              <?php if (sigPuedeVer('Tipo Depósitos')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/TipoDeposito/TipoDepositoView.php" data-page="terreno-tipo-depositos"><span class="sub-item">Tipo Depósitos</span></a></li>
              <?php endif; ?>
            </ul>
          </div>
        </li>
        <?php endif; ?>

        <?php if ($sigMostrarUsuarios): ?>
        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navUsuarios" aria-expanded="false">
            <i class="fas fa-users"></i>
            <p>Usuarios</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navUsuarios">
            <ul class="nav nav-collapse">
              <?php if (sigPuedeVer('Gestión de Usuarios')): ?>
              <li><a href="#" data-page="usuarios-registrar"><span class="sub-item">Gestión De Usuarios</span></a></li>
              <?php endif; ?>
              <?php if (sigPuedeVer('Roles y Permisos')): ?>
              <li><a href="<?php echo $rutaBase; ?>View/Roles/registro-roles.php" data-page="roles-registrar"><span class="sub-item">Roles y Permisos</span></a></li>
              <li><a href="<?php echo $rutaBase; ?>View/Roles/consultar-roles.php" data-page="roles-consultar"><span class="sub-item">Consultar Roles</span></a></li>
              <?php endif; ?>
            </ul>
          </div>
        </li>
        <?php endif; ?>

        <?php if (sigPuedeVer('Copia de seguridad')): ?>
        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>View/CopiaSeguridad/copia-seguridad.php" data-page="copia-seguridad">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Copia de seguridad</p>
          </a>
        </li>
        <?php endif; ?>

        <?php if (sigPuedeVer('Auditoría')): ?>
        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>View/Auditoria/AuditoriaView.php" data-page="auditoria">
            <i class="fas fa-history"></i>
            <p>Auditoría</p>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-item">
          <a href="<?php echo $rutaBase; ?>View/Configuraciones/configuraciones.php" data-page="configuraciones">
            <i class="fas fa-cogs"></i>
            <p>Configuraciones</p>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <div class="sidebar-footer">
    <a href="<?php echo $rutaBase; ?>Controller/login/logout.php" class="btn-logout">
      <i class="fas fa-sign-out-alt"></i>
      Cerrar Sesión
    </a>
  </div>
</div>
