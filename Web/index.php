<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Resumen · SIGuppys</title>
  <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
  <link rel="icon" href="../Web/assets/img/siguppys/favicon-32.png" type="image/png" />
  <link rel="apple-touch-icon" href="../Web/assets/img/siguppys/favicon-180.png" />

  <!-- Fonts and icons -->
  <script src="../Web/assets/js/plugin/webfont/webfont.min.js"></script>
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
        urls: ["../Web/assets/css/fonts.min.css"],
      },
      active: function () {
        sessionStorage.fonts = true;
      },
    });
  </script>

  <!-- CSS Files (mismos del template, sin modificar) -->
  <link rel="stylesheet" href="../Web/assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../Web/assets/css/plugins.min.css" />
  <link rel="stylesheet" href="../Web/assets/css/kaiadmin.min.css" />

  <!-- Estilos propios de SIGuppys: solo AGREGAN reglas encima del kaiadmin.css original -->
  <link rel="stylesheet" href="../Web/assets/css/siguppys.css" />


  <link rel="stylesheet" href="../Web/assets/css/leaflet.css" />
</head>

<body data-page="resumen">
  <?php $rutaBase = '../'; ?>
  <div class="wrapper">
    <?php include '../View/partials/sidebar.php'; ?>
    <div class="main-panel">
      <?php include '../View/partials/topbar.php'; ?>

      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Resumen</h3>
              <h6 class="op-7 mb-2">Inicio / Resumen</h6>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-primary bubble-shadow-small">
                        <i class="fas fa-warehouse"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Zoocriaderos activos</p>
                        <h4 class="card-title" id="kpiZoocriaderosActivos">…</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-info bubble-shadow-small">
                        <i class="fas fa-flask"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Seguimientos este mes</p>
                        <h4 class="card-title" id="kpiSeguimientosMes">…</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div class="icon-big text-center icon-success bubble-shadow-small">
                        <i class="fas fa-map-marker-alt"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Depósitos inspeccionados</p>
                        <h4 class="card-title" id="kpiDepositosInspeccionados">…</h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row align-items-stretch">
            <div class="col-md-7">
              <div class="card h-100">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Zoocriaderos con seguimiento reciente</h4>
                    <a href="../View/Zoocriadero/zoocriaderos.php" class="btn btn-sm btn-label-primary">Ver todos</a>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>Zoocriadero</th>
                          <th>Comuna / Barrio</th>
                          <th class="text-center">Estado</th>
                        </tr>
                      </thead>
                      <tbody id="resumenZoocriaderosBody">
                        <tr class="sig-empty-row"><td colspan="3">Cargando...</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-5">
              <div class="card h-100">
                <div class="card-header">
                  <h4 class="card-title">Mapa de zoocriaderos y depósitos</h4>
                </div>
                <div class="card-body d-flex flex-column">

                  <div id="mapaResumen" class="flex-grow-1"></div>
                  <div id="mapaFiltros" class="mt-2">
                    <span class="text-muted small">Cargando puntos del mapa…</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../Web/assets/js/plugin/leaflet/leaflet.js"></script>

  <?php
  $basePath = '../';
  $pageScripts = ['Web/assets/js/siguppys-resumen.js', 'Web/assets/js/siguppys-mapa-resumen.js'];
  include '../View/partials/footer.php';
  ?>