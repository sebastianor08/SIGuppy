<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Registrar Seguimiento · SIGuppys</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
  <link rel="icon" href="../../Web/assets/img/siguppys/favicon-32.png" type="image/png" />
  <link rel="apple-touch-icon" href="../../Web/assets/img/siguppys/favicon-180.png" />
  <script src="../../Web/assets/js/plugin/webfont/webfont.min.js"></script>
  <script>
    WebFont.load({
      google: { families: ["Public Sans:300,400,500,600,700"] },
      custom: { families: ["Font Awesome 5 Solid","Font Awesome 5 Regular","Font Awesome 5 Brands","simple-line-icons"], urls: ["../../Web/assets/css/fonts.min.css"] },
      active: function () { sessionStorage.fonts = true; }
    });
  </script>
  <link rel="stylesheet" href="../../Web/assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../../Web/assets/css/plugins.min.css" />
  <link rel="stylesheet" href="../../Web/assets/css/kaiadmin.min.css" />
  <link rel="stylesheet" href="../../Web/assets/css/siguppys.css" />
</head>
<body data-page="seguimiento-zoocriadero">
<div class="wrapper">
      <?php $rutaBase = '../../'; ?>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-panel">
    <?php include '../partials/topbar.php'; ?>


    <div class="container"><div class="page-inner">
      <div class="d-flex align-items-center flex-column flex-md-row pt-2 pb-4">
        <div>
          <h3 class="fw-bold mb-2" id="seguimientoTitulo">Registrar Seguimiento de Zoocriadero</h3>
          <h6 class="op-7 mb-0">Registre la actividad realizada sobre un tanque existente.</h6></div>
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
              <label class="form-label" for="ph">pH <span class="text-muted small"></span></label>
              <input type="number" min="0" max="14" step="0.01" class="form-control" id="ph" name="ph" placeholder="7.20" />
            </div>
            <div class="col-md-3">
              <label class="form-label" for="temperatura">Temperatura °C <span class="text-muted small"></span></label>
              <input type="number" step="0.01" class="form-control" id="temperatura" name="temperatura" placeholder="26.50" />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_sembrados">Peces sembrados</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_sembrados" name="numero_sembrados" required />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_nacidos_hembra">Hembras nacidas</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_nacidos_hembra" name="numero_nacidos_hembra" required />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_nacidos_macho">Machos nacidos</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_nacidos_macho" name="numero_nacidos_macho" required />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_muertos_hembra">Hembras muertas</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_muertos_hembra" name="numero_muertos_hembra" required />
            </div>
            <div class="col-md-2">
              <label class="form-label" for="numero_muertos_macho">Machos muertos</label>
              <input type="number" min="0" step="1" value="0" class="form-control" id="numero_muertos_macho" name="numero_muertos_macho" required />
            </div>
            <div class="col-md-2">
              <span class="form-label d-block">Total nacidos / muertos</span>
              <span class="form-control-plaintext fw-bold" id="totalNacidosMuertos">0 / 0</span>
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
            <a href="../Zoocriadero/zoocriaderos.php" class="btn btn-label-secondary">Volver</a>
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
                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="historialBody">
                    <tr><td colspan="10" class="text-center text-muted py-4">Cargando...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="modal fade" id="seguimientoDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detalle del seguimiento</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="seguimientoDetailBody"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
        </div></div>
      </div>
    </div>

<?php
    $pageScripts = ['Web/assets/js/siguppys-seguimiento-zoocriadero.js'];
    include '../partials/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // La fecha del seguimiento debe ser siempre la de hoy (el backend
  // también lo valida): se fija el valor y se bloquea min/max.
  const inputFecha = document.getElementById('fecha');
  if (inputFecha) {
    const hoy = new Date();
    const year = hoy.getFullYear();
    const month = String(hoy.getMonth() + 1).padStart(2, '0');
    const day = String(hoy.getDate()).padStart(2, '0');
    const fechaActual = `${year}-${month}-${day}`;

    inputFecha.value = fechaActual;
    inputFecha.min = fechaActual;
    inputFecha.max = fechaActual;
  }

  // 1. Apuntar exactamente al select por su ID en tu HTML
  const selectAccion = document.getElementById('id_actividad');
  const inputPh = document.getElementById('ph');
  const inputTemp = document.getElementById('temperatura');
  const labelPh = document.querySelector('label[for="ph"]');
  const labelTemp = document.querySelector('label[for="temperatura"]');

  // Acciones que exigen medir pH
  const accionesPH = [
    'Aplicar tratamiento',
    'Cambiar agua',
    'Cosechar peces',
    'Equilibrar pH',
    'Limpiar filtro',
    'Limpiar tanque',
    'Retirar peces muertos'
  ];

  // Acciones que exigen medir Temperatura
  const accionesTemperatura = [
    'Cambiar agua',
    'Medir temperatura'
  ];

  function gestionarReglasNegocio() {
    if (!selectAccion) return;

    // Obtener el texto visible de la opción seleccionada
    const accionSeleccionada = selectAccion.options[selectAccion.selectedIndex]?.text.trim();

    // 1. Evaluar campo pH
    const requierePh = accionesPH.includes(accionSeleccionada);
    aplicarEstadoCampo(inputPh, labelPh, requierePh);

    // 2. Evaluar campo Temperatura
    const requiereTemp = accionesTemperatura.includes(accionSeleccionada);
    aplicarEstadoCampo(inputTemp, labelTemp, requiereTemp);
  }

  function aplicarEstadoCampo(input, label, esObligatorio) {
    if (!input) return;

    if (esObligatorio) {
      input.disabled = false;
      input.required = true;
      actualizarAsterisco(label, true);
    } else {
      input.value = '';        // Limpia cualquier valor previo
      input.disabled = true;   // Bloquea el campo si la acción no lo requiere
      input.required = false;
      actualizarAsterisco(label, false);
    }
  }

  function actualizarAsterisco(label, mostrar) {
    if (!label) return;
    let span = label.querySelector('.asterisco-req');

    if (mostrar) {
      if (!span) {
        span = document.createElement('span');
        span.className = 'asterisco-req text-danger ms-1';
        span.textContent = '*';
        label.appendChild(span);
      }
    } else {
      if (span) span.remove();
    }
  }

  // Escuchar evento de cambio en la lista de acciones
  if (selectAccion) {
    selectAccion.addEventListener('change', gestionarReglasNegocio);
  }

  // Ejecutar al cargar la página por si hay alguna opción seleccionada
  gestionarReglasNegocio();
});
</script>
</body>
</html>