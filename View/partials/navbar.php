<!-- Sidebar -->
<div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
  <div class="sidebar-logo">
    <div class="logo-header siguppys-logo-header">
      <a href="Index.php" class="logo siguppys-logo">
        <span class="siguppys-pin">
          <img src="../Web/assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
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
          <a href="Index.php">
            <i class="fas fa-home"></i>
            <p>Principal</p>
          </a>
        </li>

        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navCiudades" aria-expanded="false">
            <i class="fas fa-city"></i>
            <p>Ciudades</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navCiudades">
            <ul class="nav nav-collapse">
              <li>
                <a href="<?php echo getUrl("Ciudades", "Ciudades", "getCreate") ?>">
                  <span class="sub-item">Registrar</span>
                </a>
              </li>
              <li>
                <a href="<?php echo getUrl("Ciudades", "Ciudades", "list") ?>">
                  <span class="sub-item">Consultar</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item submenu">
          <a data-bs-toggle="collapse" href="#navDepartamentos" aria-expanded="false">
            <i class="fas fa-map-marker-alt"></i>
            <p>Departamentos</p>
            <span class="caret"></span>
          </a>
          <div class="collapse" id="navDepartamentos">
            <ul class="nav nav-collapse">
              <li>
                <a href="<?php echo getUrl("Departamentos", "Departamentos", "getCreate") ?>">
                  <span class="sub-item">Registrar</span>
                </a>
              </li>
              <li>
                <a href="<?php echo getUrl("Departamentos", "Departamentos", "list") ?>">
                  <span class="sub-item">Consultar</span>
                </a>
              </li>
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
              <li>
                <a href="<?php echo getUrl("Usuarios", "Usuarios", "getCreate") ?>">
                  <span class="sub-item">Registrar</span>
                </a>
              </li>
              <li>
                <a href="<?php echo getUrl("Usuarios", "Usuarios", "list") ?>">
                  <span class="sub-item">Consultar</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- Estos dos módulos aún no tienen Controller/Model propios,
             se dejan como referencia visual igual que en las vistas de
             demostración (index.php, zoocriaderos.php) -->
        <li class="nav-item">
          <a href="#">
            <i class="fas fa-warehouse"></i>
            <p>Zoocriaderos</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="#">
            <i class="fas fa-cogs"></i>
            <p>Configuraciones</p>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-footer">
      <a href="<?php echo getUrl("Acceso", "Acceso", "logout") ?>" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i>
        Cerrar Sesión
      </a>
    </div>
  </div>
</div>
<!-- End Sidebar -->
