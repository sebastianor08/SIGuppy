<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Resumen · SIGuppys</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link rel="icon" href="../assets/img/siguppys/favicon-32.png" type="image/png" />
    <link rel="apple-touch-icon" href="../assets/img/siguppys/favicon-180.png" />

    <!-- Fonts and icons -->
    <script src="../assets/js/plugin/webfont/webfont.min.js"></script>
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
          urls: ["../assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files (mismos del template, sin modificar) -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/plugins.min.css" />
    <link rel="stylesheet" href="../assets/css/kaiadmin.min.css" />

    <!-- Estilos propios de SIGuppys: solo AGREGAN reglas encima del kaiadmin.css original -->
    <link rel="stylesheet" href="../assets/css/siguppys.css" />
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
              <div class="col-sm-6 col-md-3">
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
                          <h4 class="card-title">4</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
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
                          <h4 class="card-title">27</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
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
                          <h4 class="card-title">138</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-icon">
                        <div class="icon-big text-center icon-danger bubble-shadow-small">
                          <i class="fas fa-triangle-exclamation"></i>
                        </div>
                      </div>
                      <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                          <p class="card-category">Alertas pendientes</p>
                          <h4 class="card-title">3</h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-7">
                <div class="card">
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
                        <tbody>
                          <tr>
                            <td>Zoocriadero Central</td>
                            <td>Comuna 10 · Guabal</td>
                            <td class="text-center"><span class="badge-estado activo">Activo</span></td>
                          </tr>
                          <tr>
                            <td>Zoocriadero Norte</td>
                            <td>Comuna 2 · Granada</td>
                            <td class="text-center"><span class="badge-estado activo">Activo</span></td>
                          </tr>
                          <tr>
                            <td>Zoocriadero Aguablanca</td>
                            <td>Comuna 15 · Mojica</td>
                            <td class="text-center"><span class="badge-estado activo">Activo</span></td>
                          </tr>
                          <tr>
                            <td>Zoocriadero Ladera</td>
                            <td>Comuna 18 · Meléndez</td>
                            <td class="text-center"><span class="badge-estado inactivo">Inhabilitado</span></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-5">
                <div class="card">
                  <div class="card-body">
                    <!-- Espacio reservado: aquí irá contenido nuevo (ya no son los accesos rápidos) -->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php
    $pageScripts = [];
    include '../View/partials/footer.php';
?>
  </body>
</html>
