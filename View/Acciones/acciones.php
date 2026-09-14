<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Acciones · SIGuppys</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link rel="icon" href="../../assets/img/siguppys/favicon-32.png" type="image/png" />
    <link rel="apple-touch-icon" href="../../assets/img/siguppys/favicon-180.png" />

    <!-- Fonts and icons -->
    <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["../../assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css" />

   
    <link rel="stylesheet" href="../../assets/css/siguppys.css" />
  </head>
  <body data-page="acciones-zoocriadero">
    <div class="wrapper">
      <!-- Sidebar -->
      <div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">
        <div class="sidebar-logo">
          <!-- Logo Header -->
          <div class="logo-header siguppys-logo-header">
            <a href="../../Web/index.php" class="logo siguppys-logo">
              <span class="siguppys-pin">
                <img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
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
                <a href="../../Web/index.php" data-page="resumen">
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
                    <li>
                      <a href="#" data-page="rep-actividades-zoo">
                        <span class="sub-item">Seguimiento de Actividades en los Zoocriaderos</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="rep-peces-tanque">
                        <span class="sub-item">Peces nacidos o muertos por tanque</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="rep-tanques-zoo">
                        <span class="sub-item">Tanques por Zoocriadero</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="rep-terreno-tipo">
                        <span class="sub-item">Actividades de Terreno por Tipo</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="rep-terreno-auxiliar">
                        <span class="sub-item">Actividades De Terreno Por Auxiliar Responsable</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="rep-sitios-deposito">
                        <span class="sub-item">Gráfico de Sitios por Tipo de Depósito</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <li class="nav-item submenu">
                <a data-bs-toggle="collapse" href="#navZoocriaderos" aria-expanded="false">
                  <i class="fas fa-warehouse"></i>
                  <p>Zoocriaderos</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="navZoocriaderos">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="../../View/Zoocriadero/zoocriaderos.php" data-page="zoocriaderos">
                        <span class="sub-item">Zoocriaderos</span>
                      </a>
                    </li>
                    <li>
                      <a href="../../View/Zoocriadero/acciones.php" data-page="acciones-zoocriadero">
                        <span class="sub-item">Acciones</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <li class="nav-item submenu">
                <a data-bs-toggle="collapse" href="#navTerreno" aria-expanded="false">
                  <i class="fas fa-map-marker-alt"></i>
                  <p>Terreno</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="navTerreno">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="#" data-page="terreno-depositos">
                        <span class="sub-item">Depósitos</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="terreno-actividades">
                        <span class="sub-item">Actividades</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="terreno-tipo-depositos">
                        <span class="sub-item">Tipo Depósitos</span>
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
                      <a href="#" data-page="usuarios-registrar">
                        <span class="sub-item">Registrar Usuario</span>
                      </a>
                    </li>
                    <li>
                      <a href="#" data-page="usuarios-consultar">
                        <span class="sub-item">Consultar Usuarios</span>
                      </a>
                    </li>
                    <li>
                      <a href="../../View/Roles/registro-roles.php" data-page="roles-registrar">
                        <span class="sub-item">Roles y Permisos</span>
                      </a>
                    </li>
                    <li>
                      <a href="../../View/Roles/consultar-roles.php" data-page="roles-consultar">
                        <span class="sub-item">Consultar Roles</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a href="#" data-page="copia-seguridad">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Copia de seguridad</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="../../View/Configuraciones/configuraciones.php" data-page="configuraciones">
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
      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <div class="logo-header siguppys-logo-header" data-background-color="white">
              <a href="../../Web/index.php" class="logo siguppys-logo">
                <span class="siguppys-pin">
                  <img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys" />
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
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Zoocriaderos</h3>
                <h6 class="op-7 mb-2">Gestiona la información de los zoocriaderos, tipos de tanque y acciones disponibles.</h6>
              </div>
            </div>

            
            <div class="card">
              <div class="card-body">
                <h5 class="fw-bold mb-3">Gestión de Acciones</h5>
                <form id="accionForm">
                  <input type="hidden" name="id_actividad" />
                  <div class="mb-3">
                    <label class="form-label">Nombre de la Acción*</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Limpieza de tanque" required />
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción de la acción..."></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                      <option value="1">Activo</option>
                      <option value="0">Inhabilitado</option>
                    </select>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="accionSubmitBtn">
                      <i class="fas fa-plus me-1"></i> <span id="accionSubmitLabel">Nueva Acción</span>
                    </button>
                    <button type="button" class="btn btn-success" id="accionGuardarBtn">
                      <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-label-secondary" id="accionLimpiarBtn">
                      <i class="fas fa-sync-alt me-1"></i> Limpiar
                    </button>
                  </div>
                </form>
              </div>
            </div>

            
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-2">
              <div>
                <h5 class="fw-bold mb-0">Acciones Registradas</h5>
                <h6 class="op-7 mb-2">Listado de acciones disponibles en el sistema.</h6>
              </div>
              <div class="ms-md-auto py-2 py-md-0" id="crearAccionWrap">
                
              </div>
            </div>
            <div class="card">
              <div class="card-body">
                <div id="accionesMessage" class="alert d-none mb-3" role="alert"></div>

                <div class="table-responsive">
                  <table class="table align-items-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-center">Estado</th>
                        <th>Fecha de Creación</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="accionesTableBody"></tbody>
                  </table>
                </div>
              </div>
            </div>

           
            <div class="modal fade" id="accionDetailModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Detalle de la acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>
                  <div class="modal-body" id="accionDetailBody"></div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    
    <script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="../../assets/js/kaiadmin.min.js"></script>

    
    <script src="../../assets/js/siguppys-nav.js"></script>
    <script src="../../assets/js/siguppys-acciones-zoocriadero.js"></script>
  </body>
</html>