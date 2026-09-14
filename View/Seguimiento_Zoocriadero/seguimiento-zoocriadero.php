<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Registrar Seguimiento · SIGuppys</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
  <link rel="icon" href="../../assets/img/siguppys/favicon-32.png" type="image/png" />
  <link rel="apple-touch-icon" href="../../assets/img/siguppys/favicon-180.png" />
  <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
  <script>
    WebFont.load({
      google: { families: ["Public Sans:300,400,500,600,700"] },
      custom: { families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"], urls: ["../../assets/css/fonts.min.css"] },
      active: function () { sessionStorage.fonts = true; }
    });
  </script>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
  <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />
  <link rel="stylesheet" href="../../assets/css/siguppys.css" />
</head>
<body data-page="seguimiento-zoocriadero">
<div class="wrapper">
  <div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
    <div class="sidebar-logo">
      <div class="logo-header siguppys-logo-header">
        <a href="../../Web/index.php" class="logo siguppys-logo">
          <span class="siguppys-pin"><img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys" /></span>
          <span class="siguppys-brand"><strong>SIGuppys</strong><small>Control Biológico contra el Dengue</small></span>
        </a>
        <div class="nav-toggle">
          <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
          <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
        </div>
      </div>
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
      <div class="sidebar-content">
        <ul class="nav nav-secondary">
          <li class="nav-section"><span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span><h4 class="text-section">Menú</h4></li>
          <li class="nav-item"><a href="../../Web/index.php" data-page="resumen"><i class="fas fa-home"></i><p>Resumen</p></a></li>
          <li class="nav-item"><a href="../../View/Zoocriadero/zoocriaderos.php" data-page="zoocriaderos"><i class="fas fa-warehouse"></i><p>Zoocriaderos</p></a></li>
          <li class="nav-item submenu">
            <a data-bs-toggle="collapse" href="#navReportes" aria-expanded="false"><i class="fas fa-chart-bar"></i><p>Reportes</p><span class="caret"></span></a>
            <div class="collapse" id="navReportes"><ul class="nav nav-collapse">
              <li><a href="#" data-page="rep-actividades-zoo"><span class="sub-item">Seguimiento de Actividades en los Zoocriaderos</span></a></li>
              <li><a href="#" data-page="rep-peces-tanque"><span class="sub-item">Peces nacidos o muertos por tanque</span></a></li>
              <li><a href="#" data-page="rep-tanques-zoo"><span class="sub-item">Tanques por Zoocriadero</span></a></li>
              <li><a href="#" data-page="rep-terreno-tipo"><span class="sub-item">Actividades de Terreno por Tipo</span></a></li>
              <li><a href="#" data-page="rep-terreno-auxiliar"><span class="sub-item">Actividades De Terreno Por Auxiliar Responsable</span></a></li>
              <li><a href="#" data-page="rep-sitios-deposito"><span class="sub-item">Gráfico de Sitios por Tipo de Depósito</span></a></li>
            </ul></div>
          </li>
          <li class="nav-item active"><a href="../../View/Seguimiento_Zoocriadero/seguimiento-zoocriadero.php" data-page="seguimiento-zoocriadero"><i class="fas fa-clipboard-check"></i><p>Registrar seguimiento</p></a></li>
          <li class="nav-item submenu">
            <a data-bs-toggle="collapse" href="#navTerreno" aria-expanded="false"><i class="fas fa-map-marker-alt"></i><p>Terreno</p><span class="caret"></span></a>
            <div class="collapse" id="navTerreno"><ul class="nav nav-collapse">
              <li><a href="#" data-page="terreno-depositos"><span class="sub-item">Depósitos</span></a></li>
              <li><a href="#" data-page="terreno-actividades"><span class="sub-item">Actividades</span></a></li>
              <li><a href="#" data-page="terreno-tipo-depositos"><span class="sub-item">Tipo Depósitos</span></a></li>
            </ul></div>
          </li>
          <li class="nav-item submenu">
            <a data-bs-toggle="collapse" href="#navUsuarios" aria-expanded="false"><i class="fas fa-users"></i><p>Usuarios</p><span class="caret"></span></a>
            <div class="collapse" id="navUsuarios"><ul class="nav nav-collapse">
              <li><a href="#" data-page="usuarios-registrar"><span class="sub-item">Registrar Usuario</span></a></li>
              <li><a href="#" data-page="usuarios-consultar"><span class="sub-item">Consultar Usuarios</span></a></li>
              <li><a href="../../View/Roles/registro-roles.php" data-page="roles-registrar"><span class="sub-item">Roles y Permisos</span></a></li>
              <li><a href="../../View/Roles/consultar-roles.php" data-page="roles-consultar"><span class="sub-item">Consultar Roles</span></a></li>
            </ul></div>
          </li>
          <li class="nav-item"><a href="#" data-page="copia-seguridad"><i class="fas fa-cloud-upload-alt"></i><p>Copia de seguridad</p></a></li>
          <li class="nav-item"><a href="../../View/Configuraciones/configuraciones.php" data-page="configuraciones"><i class="fas fa-cogs"></i><p>Configuraciones</p></a></li>
        </ul>
      </div>
      <div class="sidebar-footer"><a href="#" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></div>
    </div>
  </div>

  <div class="main-panel">
    <div class="main-header">
      <div class="main-header-logo"><div class="logo-header siguppys-logo-header" data-background-color="white"><a href="../../Web/index.php" class="logo siguppys-logo"><span class="siguppys-pin"><img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys" /></span><span class="siguppys-brand"><strong>SIGuppys</strong><small>Control Biológico contra el Dengue</small></span></a><div class="nav-toggle"><button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button><button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button></div></div></div>
      <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
        <div class="container-fluid"><ul class="navbar-nav topbar-nav ms-md-auto align-items-center"><li class="nav-item d-flex align-items-center"><div class="sig-role-switcher"><label for="sigRoleSelect"><i class="fas fa-user-shield me-1"></i>Vista</label><select id="sigRoleSelect"><option value="auxiliar">Auxiliar de campo</option><option value="coordinador">Coordinador</option></select></div></li></ul></div>
      </nav>
    </div>

    <div class="container"><div class="page-inner">
      <div class="d-flex align-items-center flex-column flex-md-row pt-2 pb-4">
        <div><h3 class="fw-bold mb-2" id="seguimientoTitulo">Registrar Seguimiento de Zoocriadero</h3><h6 class="op-7 mb-0">Registre la actividad realizada sobre un tanque existente.</h6></div>
      </div>

      <div class="card sig-followup-card"><div class="card-body">
        <form id="seguimientoZoocriaderoForm" novalidate>
          <input type="hidden" id="id_seguimiento" name="id_seguimiento" value="" />
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="id_zoocriadero">Zoocriadero</label>
              <select class="form-select" id="id_zoocriadero" name="id_zoocriadero" required><option value="">Seleccione un zoocriadero</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="id_tanque">Tanque</label>
              <select class="form-select" id="id_tanque" name="id_tanque" disabled required><option value="">Seleccione primero un zoocriadero</option></select>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="direccion">Dirección</label>
              <input type="text" class="form-control" id="direccion" readonly placeholder="Se cargará desde el zoocriadero" />
              <div class="form-text">La dirección pertenece al zoocriadero y no se puede modificar en este registro.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="fecha">Fecha</label>
              <input type="date" class="form-control" id="fecha" name="fecha" required />
            </div>

            <div class="col-md-3">
              <label class="form-label" for="ph">pH <span class="text-muted small">(opcional)</span></label>
              <input type="number" min="0" max="14" step="0.01" class="form-control" id="ph" name="ph" placeholder="7.20" />
            </div>
            <div class="col-md-3">
              <label class="form-label" for="temperatura">Temperatura °C <span class="text-muted small">(opcional)</span></label>
              <input type="number" step="0.01" class="form-control" id="temperatura" name="temperatura" placeholder="26.50" />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_sembrados">Peces sembrados</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_sembrados" name="numero_sembrados" required />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_nacidos">Peces nacidos</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_nacidos" name="numero_nacidos" required />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_muertos">Peces muertos</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_muertos" name="numero_muertos" required />
            </div>
            <div class="col-md-4">
              <label class="form-label" for="id_actividad">Acción</label>
              <select class="form-select" id="id_actividad" name="id_actividad" required><option value="">Seleccione la acción</option></select>
            </div>

            <div class="col-12">
              <label class="form-label" for="observaciones">Observaciones</label>
              <textarea class="form-control" id="observaciones" name="observaciones" rows="4" maxlength="300" placeholder="Escribe aquí las observaciones..."></textarea>
              <div class="form-text text-end"><span id="observacionesCount">0</span>/300</div>
            </div>
          </div>

          <div id="seguimientoMessage" class="alert d-none mt-4 mb-0" role="alert"></div>
          <div class="d-flex justify-content-end mt-4 gap-2">
            <button type="button" id="btnCancelarEdicion" class="btn btn-label-secondary d-none">Cancelar edición</button>
            <a href="../../View/Zoocriadero/zoocriaderos.php" class="btn btn-label-secondary">Volver</a>
            <button type="submit" class="btn btn-primary" id="btnGuardarSeguimiento"><i class="fas fa-save me-1"></i>Guardar</button>
          </div>
        </form>
      </div></div>

      <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0">Seguimientos registrados</h4>
          <button type="button" class="btn btn-sm btn-label-primary" id="btnRecargarHistorial">
            <i class="fas fa-sync-alt me-1"></i>Actualizar
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead class="table-light">
                <tr>
                  <th>Fecha</th>
                  <th>Zoocriadero</th>
                  <th class="text-center">Tanque</th>
                  <th>Acción</th>
                  <th class="text-center">Sembrados</th>
                  <th class="text-center">Nacidos</th>
                  <th class="text-center">Muertos</th>
                  <th class="text-center">pH</th>
                  <th class="text-center">T °C</th>
                  <th class="text-center">Editar</th>
                </tr>
              </thead>
              <tbody id="historialBody">
                <tr><td colspan="10" class="text-center text-muted py-4">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div></div>
  </div>
</div>

<script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="../../assets/js/kaiadmin.min.js"></script>
<script src="../../assets/js/siguppys-nav.js"></script>
<script src="../../assets/js/siguppys-seguimiento-zoocriadero.js"></script>
</body>
</html>
