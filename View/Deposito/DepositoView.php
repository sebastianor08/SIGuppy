<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <title>Depósitos · SIGuppys</title>

    <link rel="icon" href="../../assets/img/siguppys/favicon-32.png" type="image/png">
    <link rel="apple-touch-icon" href="../../assets/img/siguppys/favicon-180.png">

    <script src="../../assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons"
          ],
          urls: ["../../assets/css/fonts.min.css"]
        },
        active: function () {
          sessionStorage.fonts = true;
        }
      });
    </script>

    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/plugins.min.css">
    <link rel="stylesheet" href="../../assets/css/kaiadmin.min.css">
    <link rel="stylesheet" href="../../assets/css/siguppys.css">

    <style>
      .depositos-card {
        border-radius: 12px;
      }

      .depositos-table th {
        white-space: nowrap;
      }

      .depositos-table td {
        vertical-align: middle;
      }

      .deposito-descripcion {
        max-width: 300px;
      }

      .deposito-descripcion .descripcion {
        color: #8a8d93;
        font-size: 12px;
      }

      .deposito-direccion {
        min-width: 180px;
      }

      #depositosTableBody .sig-empty-row td {
        padding: 45px 20px;
      }

      .btn-crear-deposito {
        min-width: 125px;
      }
    </style>
</head>

<body data-page="terreno-depositos">

<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar sidebar-style-2 siguppys-sidebar" data-background-color="white">

        <div class="sidebar-logo">
            <div class="logo-header siguppys-logo-header">
                <a href="../../Web/index.php" class="logo siguppys-logo">
                    <span class="siguppys-pin">
                        <img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys">
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

                    <li class="nav-item">
                        <a href="../../View/Zoocriadero/zoocriaderos.php" data-page="zoocriaderos">
                            <i class="fas fa-warehouse"></i>
                            <p>Zoocriaderos</p>
                        </a>
                    </li>

                    <!-- Terreno -->
                    <li class="nav-item submenu">
                        <a data-bs-toggle="collapse"
                            href="#navTerreno"
                            aria-expanded="true">

                            <i class="fas fa-map-marker-alt"></i>
                            <p>Terreno</p>
                            <span class="caret"></span>
                        </a>

                        <div class="collapse show" id="navTerreno">
                            <ul class="nav nav-collapse">

                                <li>
                                    <a href="../../View/Deposito/DepositoView.php"
                                        data-page="terreno-depositos">
                                        <span class="sub-item">Depósitos</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="../../View/Actividad/ActividadView.php"
                                        data-page="terreno-actividades">
                                        <span class="sub-item">Actividades</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="../../View/TipoDeposito/TipoDepositoView.php"
                                        data-page="terreno-tipo-depositos">
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
                        <a href="../../View/Configuraciones/configuraciones.php"
                            data-page="configuraciones">
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

        <!-- Header -->
        <div class="main-header">

            <div class="main-header-logo">
                <div class="logo-header siguppys-logo-header"
                        data-background-color="white">

                    <a href="../../Web/index.php" class="logo siguppys-logo">
                        <span class="siguppys-pin">
                            <img src="../../assets/img/siguppys/logo-pin.png" alt="SIGuppys">
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

                            <div class="sig-role-switcher">
                                <label for="sigRoleSelect">
                                    <i class="fas fa-user-shield me-1"></i>Vista
                                </label>

                                <select id="sigRoleSelect">
                                    <option value="auxiliar">Auxiliar de campo</option>
                                    <option value="coordinador">Coordinador</option>
                                </select>
                            </div>

                        </li>
                    </ul>

                </div>
            </nav>

        </div>
        <!-- End Header -->


        <div class="container">
            <div class="page-inner">

                <!-- Título -->
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">

                    <div>
                        <h3 class="fw-bold mb-3">
                            Trabajo de terreno - Depósitos
                        </h3>

                        <h6 class="op-7 mb-2">
                            Trabajo de terreno / Depósitos
                        </h6>
                    </div>

                </div>


                <!-- Mensaje -->
                <div id="depositosMessage"
                        class="alert d-none mb-3"
                        role="alert"></div>


                <!-- Tabla -->
                <div class="card card-round depositos-card">

                    <div class="card-body">

                        <div class="sig-table-toolbar">

                            <div class="sig-search">
                                <i class="fas fa-search"></i>

                                <input
                                    type="text"
                                    id="depositosSearch"
                                    class="form-control"
                                    placeholder="Buscar por tipo, descripción o dirección..."
                                >
                            </div>

                            <div class="d-flex align-items-center gap-2">

                                <select
                                    id="depositosEstadoFiltro"
                                    class="form-select form-select-sm"
                                    style="width:auto;"
                                >
                                    <option value="todos">Todos los estados</option>
                                    <option value="activo">Activos</option>
                                    <option value="inactivo">Inactivos</option>
                                </select>

                                <span
                                    class="small text-muted"
                                    id="depositosCount"
                                ></span>

                            </div>

                        </div>


                        <div class="table-responsive">

                            <table class="table align-items-center mb-0 depositos-table">

                                <thead class="table-light">

                                    <tr>
                                        <th>Tipo depósito</th>
                                        <th>Descripción</th>
                                        <th>Dirección</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>

                                </thead>

                                <tbody id="depositosTableBody">
                                    <tr class="sig-empty-row">
                                        <td colspan="5">
                                            Cargando depósitos...
                                        </td>
                                    </tr>
                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>


                <!-- Botón -->
                <div class="d-flex justify-content-end mt-4 only-coordinador">

                    <button
                        type="button"
                        id="btnCrearDeposito"
                        class="btn btn-primary btn-round btn-crear-deposito"
                        data-bs-toggle="modal"
                        data-bs-target="#depositoModal"
                    >
                        <i class="fas fa-plus me-1"></i>
                        Crear Depósito
                    </button>

                </div>

            </div>
        </div>

    </div>
</div>


<!-- =========================================================
    MODAL CREAR / EDITAR DEPÓSITO
========================================================= -->

<div class="modal fade" id="depositoModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <form id="depositoForm">

                <input type="hidden" name="id_sitio">

                <div class="modal-header">

                    <h5 class="modal-title" id="depositoModalLabel">
                        Crear Depósito
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Tipo de depósito
                        </label>

                        <select
                            name="id_tipo_deposito"
                            id="idTipoDeposito"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Cargando tipos...
                            </option>
                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Dirección
                        </label>

                        <select
                            name="id_direccion"
                            id="idDireccion"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Cargando direcciones...
                            </option>
                        </select>

                        <div class="form-text">
                            La dirección debe existir previamente en la tabla
                            <strong>direccion</strong>.
                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-label-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="depositoSubmitBtn"
                    >
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- JS -->
<script src="../../assets/js/core/jquery-3.7.1.min.js"></script>
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="../../assets/js/kaiadmin.min.js"></script>

<script src="../../assets/js/siguppys-nav.js"></script>
<script src="../../assets/js/siguppys-depositos.js"></script>

</body>
</html>
