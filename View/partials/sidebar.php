<?php
    // Módulo y controlador actuales en minúsculas para comparaciones exactas
    $moduloActual = strtolower($_GET['modulo'] ?? '');
    $controladorActual = strtolower($_GET['controlador'] ?? '');

    function activoSi($condicion){
        echo $condicion ? 'active' : '';
    }
    $modulosTerreno = ['deposito', 'actividad', 'tipodeposito', 'terreno'];

    // Verifica si la URL actual pertenece a cualquiera de las subopciones de Terreno
    $esTerreno = in_array($moduloActual, $modulosTerreno) || in_array($controladorActual, $modulosTerreno);

    $modulosReporte = ['reporte', 'actividadeszoocriaderos', 'pesesnacidosmuertos', 'tanqueszoocriadero', 'actividadesterrenotipo',
    'actividadesterrenoauxiliar', 'graficositiotipodeposito'];

    $esReporte = in_array($moduloActual, $modulosReporte) || in_array($controladorActual, $modulosReporte);
?>
<div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
  <div class="sidebar-logo">
    <div class="logo-header siguppys-logo-header">
      <a href="index.php" class="logo siguppys-logo">
        <img src="assets/img/logo_sistema_SIGuppy.svg" alt="SIGuppys" />
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

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-section">
          <span class="sidebar-mini-icon">
            <i class="fa fa-ellipsis-h"></i>
          </span>
          <h4 class="text-section">Menú</h4>
        </li>

        <li class="nav-item <?php activoSi($moduloActual === '' || $moduloActual === 'resumen'); ?>">
          <a href="index.php">
            <i class="fas fa-home"></i>
            <p>Resumen</p>
          </a>
        </li>

        <li class="nav-item submenu <?php activoSi($esReporte); ?>">
          <a
            data-bs-toggle="collapse"
            href="#navReporte"
            aria-expanded="<?php echo $esReporte ? 'true' : 'false'; ?>"
          >
            <i class="fas fa-chart-bar"></i>
            <p>Reportes</p>
            <span class="caret"></span>
          </a>
          <div class="collapse <?php echo $esReporte ? 'show' : ''; ?>" id="navReporte">
            <ul class="nav nav-collapse">
              <li class="<?php activoSi($controladorActual === 'deposito'); ?>">
                <a href="<?php echo getUrl('Deposito', 'Deposito', 'list'); ?>">
                  <span class="sub-item">Seguimiento de Actividades en los Zoocriaderos</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'actividad'); ?>">
                <a href="<?php echo getUrl('Actividad', 'Actividad', 'list'); ?>">
                  <span class="sub-item">Peces nacidos o muertos por tanque</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'tipodeposito'); ?>">
                <a href="<?php echo getUrl('TipoDeposito', 'TipoDeposito', 'list'); ?>">
                  <span class="sub-item">Tanques por Zoocriadero</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'tipodeposito'); ?>">
                <a href="<?php echo getUrl('TipoDeposito', 'TipoDeposito', 'list'); ?>">
                  <span class="sub-item">Actividades de Terreno por Tipo</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'tipodeposito'); ?>">
                <a href="<?php echo getUrl('TipoDeposito', 'TipoDeposito', 'list'); ?>">
                  <span class="sub-item">Actividades De Terreno Por Auxiliar Responsable</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'tipodeposito'); ?>">
                <a href="<?php echo getUrl('TipoDeposito', 'TipoDeposito', 'list'); ?>">
                  <span class="sub-item">Gráfico de Sitios por Tipo de Depósito</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item <?php activoSi($moduloActual === 'zoocriadero'); ?>">
          <a href="<?php echo getUrl('Zoocriadero', 'Zoocriadero', 'list'); ?>">
            <i class="fas fa-warehouse"></i>
            <p>Zoocriaderos</p>
          </a>
        </li>

        <li class="nav-item submenu <?php activoSi($esTerreno); ?>">
          <a
            data-bs-toggle="collapse"
            href="#navTerreno"
            aria-expanded="<?php echo $esTerreno ? 'true' : 'false'; ?>"
          >
            <i class="fas fa-map-marker-alt"></i>
            <p>Terreno</p>
            <span class="caret"></span>
          </a>
          <div class="collapse <?php echo $esTerreno ? 'show' : ''; ?>" id="navTerreno">
            <ul class="nav nav-collapse">
              <li class="<?php activoSi($controladorActual === 'deposito'); ?>">
                <a href="<?php echo getUrl('Deposito', 'Deposito', 'list'); ?>">
                  <span class="sub-item">Depósitos</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'actividad'); ?>">
                <a href="<?php echo getUrl('Actividad', 'Actividad', 'list'); ?>">
                  <span class="sub-item">Actividades</span>
                </a>
              </li>
              <li class="<?php activoSi($controladorActual === 'tipodeposito'); ?>">
                <a href="<?php echo getUrl('TipoDeposito', 'TipoDeposito', 'list'); ?>">
                  <span class="sub-item">Tipo Depósitos</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item <?php activoSi($moduloActual === 'usuario'); ?>">
          <a href="<?php echo getUrl('Usuario', 'Usuario', 'list'); ?>">
            <i class="fas fa-users"></i>
            <p>Usuarios</p>
          </a>
        </li>

        <li class="nav-item <?php activoSi($moduloActual === 'copiadeseguridad'); ?>">
          <a href="<?php echo getUrl('CopiaDeSeguridad', 'CopiaDeSeguridad', 'list'); ?>">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Copia de seguridad</p>
          </a>
        </li>

        <li class="nav-item <?php activoSi($moduloActual === 'configuracion'); ?>">
          <a href="<?php echo getUrl('Configuracion', 'Configuracion', 'list'); ?>">
            <i class="fas fa-cogs"></i>
            <p>Configuraciones</p>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-footer">
      <a href="<?php echo getUrl('acceso', 'acceso', 'logout'); ?>" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i>
        Cerrar Sesión
      </a>
    </div>
  </div>
</div>