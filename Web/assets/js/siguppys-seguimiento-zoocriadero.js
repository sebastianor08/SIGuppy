(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "seguimiento-zoocriadero") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=SeguimientoZoocriadero&controlador=SeguimientoZoocriadero";

  var form = document.getElementById("seguimientoZoocriaderoForm");
  var idSeguimientoInput = document.getElementById("id_seguimiento");
  var zooSelect = document.getElementById("id_zoocriadero");
  var tanqueSelect = document.getElementById("id_tanque");
  var direccionInput = document.getElementById("direccion");
  var accionSelect = document.getElementById("id_actividad");
  var fechaInput = document.getElementById("fecha");
  var phInput = document.getElementById("ph");
  var temperaturaInput = document.getElementById("temperatura");
  var sembradosInput = document.getElementById("numero_sembrados");
  var nacidosHembraInput = document.getElementById("numero_nacidos_hembra");
  var nacidosMachoInput = document.getElementById("numero_nacidos_macho");
  var muertosHembraInput = document.getElementById("numero_muertos_hembra");
  var muertosMachoInput = document.getElementById("numero_muertos_macho");
  var totalNacidosMuertosEl = document.getElementById("totalNacidosMuertos");

  var labelPh = document.querySelector('label[for="ph"]');
  var labelTemperatura = document.querySelector('label[for="temperatura"]');
  var labelSembrados = document.querySelector('label[for="numero_sembrados"]');
  var labelNacidosHembra = document.querySelector('label[for="numero_nacidos_hembra"]');
  var labelNacidosMacho = document.querySelector('label[for="numero_nacidos_macho"]');
  var labelMuertosHembra = document.querySelector('label[for="numero_muertos_hembra"]');
  var labelMuertosMacho = document.querySelector('label[for="numero_muertos_macho"]');

  function actualizarTotales() {
    var totalNacidos = (Number(nacidosHembraInput.value) || 0) + (Number(nacidosMachoInput.value) || 0);
    var totalMuertos = (Number(muertosHembraInput.value) || 0) + (Number(muertosMachoInput.value) || 0);
    totalNacidosMuertosEl.textContent = totalNacidos + " / " + totalMuertos;
  }
  [nacidosHembraInput, nacidosMachoInput, muertosHembraInput, muertosMachoInput].forEach(function (input) {
    input.addEventListener("input", actualizarTotales);
  });

  var obsInput = document.getElementById("observaciones");
  var obsCount = document.getElementById("observacionesCount");
  var message = document.getElementById("seguimientoMessage");
  var saveButton = document.getElementById("btnGuardarSeguimiento");
  var tituloEl = document.getElementById("seguimientoTitulo");
  var btnCancelarEdicion = document.getElementById("btnCancelarEdicion");
  var historialBody = document.getElementById("historialBody");

  var zoocriaderos = [];
  var historial = [];

  function fijarFechaDeHoy() {
    var hoy = new Date();
    var year = hoy.getFullYear();
    var month = String(hoy.getMonth() + 1).padStart(2, "0");
    var day = String(hoy.getDate()).padStart(2, "0");
    var fechaActual = year + "-" + month + "-" + day;

    fechaInput.value = fechaActual;
    fechaInput.min = fechaActual;
    fechaInput.max = fechaActual;
  }
  fijarFechaDeHoy();

  function escapeHtml(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function showMessage(text, type) {
    message.className = "alert mt-4 mb-0 alert-" + type;
    message.textContent = text;
  }

  function clearMessage() {
    message.className = "alert d-none mt-4 mb-0";
    message.textContent = "";
  }

  async function getJson(url) {
    var response = await fetch(url, { headers: { Accept: "application/json" } });
    var data = await response.json().catch(function () { return null; });
    if (!response.ok || !data || data.ok === false) {
      throw new Error((data && data.message) || "No fue posible consultar la información.");
    }
    return data;
  }

  var accionesPH = [
    "Aplicar tratamiento",
    "Cambiar agua",
    "Cosechar peces",
    "Equilibrar pH",
    "Limpiar filtro",
    "Limpiar tanque",
    "Retirar peces muertos",
  ];

  // Acciones que exigen medir Temperatura
  var accionesTemperatura = ["Cambiar agua", "Medir temperatura"];

  // Acciones que exigen registrar peces sembrados
  var accionesSembrados = ["Sembrar alevinos"];

  var accionesConteo = ["Contar peces", "Clasificar por tallas"];

  function actualizarAsterisco(label, mostrar) {
    if (!label) return;
    var span = label.querySelector(".asterisco-req");

    if (mostrar) {
      if (!span) {
        span = document.createElement("span");
        span.className = "asterisco-req text-danger ms-1";
        span.textContent = "*";
        label.appendChild(span);
      }
    } else if (span) {
      span.remove();
    }
  }

  function aplicarEstadoCampo(input, label, esObligatorio, valorInactivo) {
    if (!input) return;
    if (valorInactivo === undefined) valorInactivo = "";

    if (esObligatorio) {
      input.disabled = false;
      input.required = true;
      actualizarAsterisco(label, true);
    } else {
      input.value = valorInactivo; // Limpia (u opcionalmente resetea) el valor previo
      input.disabled = true; // Bloquea el campo si la acción no lo requiere
      input.required = false;
      actualizarAsterisco(label, false);
    }
  }


  function marcarObligatorio(input, label, esObligatorio) {
    if (!input) return;
    input.required = esObligatorio;
    actualizarAsterisco(label, esObligatorio);
  }

  function gestionarReglasNegocio() {
    if (!accionSelect) return;

    // Obtener el texto visible de la opción seleccionada
    var opcion = accionSelect.options[accionSelect.selectedIndex];
    var accionSeleccionada = opcion ? opcion.text.trim() : "";

    // Evaluar campo pH
    aplicarEstadoCampo(phInput, labelPh, accionesPH.includes(accionSeleccionada));

    // Evaluar campo Temperatura
    aplicarEstadoCampo(temperaturaInput, labelTemperatura, accionesTemperatura.includes(accionSeleccionada));

    // Evaluar campo Peces sembrados: solo tiene sentido si la acción es "Sembrar alevinos"
    aplicarEstadoCampo(sembradosInput, labelSembrados, accionesSembrados.includes(accionSeleccionada), "0");

    var requiereConteo = accionesConteo.includes(accionSeleccionada);
    marcarObligatorio(nacidosHembraInput, labelNacidosHembra, requiereConteo);
    marcarObligatorio(nacidosMachoInput, labelNacidosMacho, requiereConteo);
    marcarObligatorio(muertosHembraInput, labelMuertosHembra, requiereConteo);
    marcarObligatorio(muertosMachoInput, labelMuertosMacho, requiereConteo);

    actualizarTotales();
  }

  if (accionSelect) {
    accionSelect.addEventListener("change", gestionarReglasNegocio);
  }

  async function loadZoocriaderos() {
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=zoocriaderos");
    zoocriaderos = result.data || [];
    zooSelect.innerHTML =
      '<option value="">Seleccione un zoocriadero</option>' +
      zoocriaderos
        .map(function (z) {
          return '<option value="' + z.id_zoocriadero + '">' + escapeHtml(z.nombre) + "</option>";
        })
        .join("");
  }

  async function loadAcciones() {
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=acciones");
    accionSelect.innerHTML =
      '<option value="">Seleccione la acción</option>' +
      (result.data || [])
        .map(function (a) {
          return '<option value="' + a.id_actividad + '">' + escapeHtml(a.nombre) + "</option>";
        })
        .join("");
    gestionarReglasNegocio();
  }

  async function loadTanques(idZoocriadero, seleccionado) {
    tanqueSelect.disabled = true;
    if (!idZoocriadero) {
      tanqueSelect.innerHTML = '<option value="">Seleccione primero un zoocriadero</option>';
      return;
    }
    tanqueSelect.innerHTML = '<option value="">Cargando tanques...</option>';

    var result = await getJson(
      AJAX_URL + "?" + MODULO + "&funcion=tanques&id_zoocriadero=" + encodeURIComponent(idZoocriadero)
    );
    var tanques = result.data || [];

    tanqueSelect.innerHTML = tanques.length
      ? '<option value="">Seleccione un tanque</option>' +
      tanques
        .map(function (t) {
          var sel = String(t.id_tanque) === String(seleccionado) ? " selected" : "";
          return (
            '<option value="' + t.id_tanque + '"' + sel + ">Tanque " +
            escapeHtml(t.numero_tanque) + " - " + escapeHtml(t.tipo_tanque) + "</option>"
          );
        })
        .join("")
      : '<option value="">No hay tanques activos para este zoocriadero</option>';
    tanqueSelect.disabled = tanques.length === 0;
  }

  async function loadHistorial() {
    historialBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Cargando...</td></tr>';
    try {
      var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=historial");
      historial = result.data || [];
    } catch (error) {
      historialBody.innerHTML =
        '<tr><td colspan="10" class="text-center text-danger py-4">' + escapeHtml(error.message) + "</td></tr>";
      return;
    }

    if (!historial.length) {
      historialBody.innerHTML =
        '<tr><td colspan="10" class="text-center text-muted py-4">Todavía no hay seguimientos registrados.</td></tr>';
      return;
    }

    historialBody.innerHTML = historial
      .map(function (s) {
        return (
          "<tr>" +
          "<td>" + escapeHtml(s.fecha) + "</td>" +
          "<td>" + escapeHtml(s.zoocriadero) + "</td>" +
          '<td class="text-center">' + escapeHtml(s.numero_tanque) + "</td>" +
          "<td>" + escapeHtml(s.actividad || "—") + "</td>" +
          '<td class="text-center">' + escapeHtml(s.numero_sembrados) + "</td>" +
          '<td class="text-center">' + escapeHtml(s.numero_nacidos) + "</td>" +
          '<td class="text-center">' + escapeHtml(s.numero_muertos) + "</td>" +
          '<td class="text-center">' + escapeHtml(s.ph || "—") + "</td>" +
          '<td class="text-center">' + escapeHtml(s.temperatura || "—") + "</td>" +
          '<td class="text-center">' +
          '<div class="table-actions">' +
          '<button type="button" class="btn-icon" data-ver="' + s.id_seguimiento +
          '" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
          '<button type="button" class="btn-icon" data-editar="' + s.id_seguimiento +
          '" title="Editar"><i class="fas fa-pen"></i></button>' +
          "</div>" +
          "</td>" +
          "</tr>"
        );
      })
      .join("");
  }

  function abrirDetalle(idSeguimiento) {
    var s = historial.find(function (x) { return String(x.id_seguimiento) === String(idSeguimiento); });
    if (!s) return;

    var zoo = zoocriaderos.find(function (z) { return String(z.id_zoocriadero) === String(s.id_zoocriadero); });
    var totalNacidos = (Number(s.numero_nacidos_hembra) || 0) + (Number(s.numero_nacidos_macho) || 0);
    var totalMuertos = (Number(s.numero_muertos_hembra) || 0) + (Number(s.numero_muertos_macho) || 0);

    var body = document.getElementById("seguimientoDetailBody");
    body.innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Fecha</dt><dd class="col-7">' + escapeHtml(s.fecha) + "</dd>" +
      '<dt class="col-5">Zoocriadero</dt><dd class="col-7">' + escapeHtml(s.zoocriadero) + "</dd>" +
      '<dt class="col-5">Dirección</dt><dd class="col-7">' + escapeHtml(zoo ? zoo.direccion : "—") + "</dd>" +
      '<dt class="col-5">Tanque</dt><dd class="col-7">' + escapeHtml(s.numero_tanque) + "</dd>" +
      '<dt class="col-5">Acción</dt><dd class="col-7">' + escapeHtml(s.actividad || "—") + "</dd>" +
      '<dt class="col-5">pH</dt><dd class="col-7">' + escapeHtml(s.ph || "—") + "</dd>" +
      '<dt class="col-5">Temperatura</dt><dd class="col-7">' +
      (s.temperatura ? escapeHtml(s.temperatura) + " °C" : "—") + "</dd>" +
      '<dt class="col-5">Peces sembrados</dt><dd class="col-7">' + escapeHtml(s.numero_sembrados) + "</dd>" +
      '<dt class="col-5">Nacidos (hembras / machos)</dt><dd class="col-7">' +
      escapeHtml(s.numero_nacidos_hembra) + " / " + escapeHtml(s.numero_nacidos_macho) +
      " <span class=\"text-muted\">(total " + totalNacidos + ")</span></dd>" +
      '<dt class="col-5">Muertos (hembras / machos)</dt><dd class="col-7">' +
      escapeHtml(s.numero_muertos_hembra) + " / " + escapeHtml(s.numero_muertos_macho) +
      " <span class=\"text-muted\">(total " + totalMuertos + ")</span></dd>" +
      "</dl>" +
      '<hr class="my-3" />' +
      '<h6 class="fw-bold mb-2">Observaciones</h6>' +
      '<p class="mb-0" style="white-space: pre-wrap;">' +
      (s.observaciones ? escapeHtml(s.observaciones) : '<span class="text-muted">Sin observaciones registradas.</span>') +
      "</p>";

    bootstrap.Modal.getOrCreateInstance(document.getElementById("seguimientoDetailModal")).show();
  }

  async function cargarEnFormulario(idSeguimiento) {
    var s = historial.find(function (x) { return String(x.id_seguimiento) === String(idSeguimiento); });
    if (!s) return;

    clearMessage();
    idSeguimientoInput.value = s.id_seguimiento;
    zooSelect.value = s.id_zoocriadero;

    var zoo = zoocriaderos.find(function (z) { return String(z.id_zoocriadero) === String(s.id_zoocriadero); });
    direccionInput.value = zoo ? zoo.direccion : "";

    await loadTanques(s.id_zoocriadero, s.id_tanque);

    fechaInput.value = s.fecha;
    phInput.value = s.ph || "";
    temperaturaInput.value = s.temperatura || "";
    sembradosInput.value = s.numero_sembrados;
    nacidosHembraInput.value = s.numero_nacidos_hembra;
    nacidosMachoInput.value = s.numero_nacidos_macho;
    muertosHembraInput.value = s.numero_muertos_hembra;
    muertosMachoInput.value = s.numero_muertos_macho;
    actualizarTotales();
    obsInput.value = s.observaciones || "";
    obsCount.textContent = obsInput.value.length;
    accionSelect.value = s.id_actividad || "";

    gestionarReglasNegocio();

    tituloEl.textContent = "Editar Seguimiento de Zoocriadero";
    saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar cambios';
    btnCancelarEdicion.classList.remove("d-none");
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function salirDeEdicion() {
    form.reset();
    idSeguimientoInput.value = "";
    direccionInput.value = "";
    obsCount.textContent = "0";
    tanqueSelect.disabled = true;
    tanqueSelect.innerHTML = '<option value="">Seleccione primero un zoocriadero</option>';
    fijarFechaDeHoy();
    tituloEl.textContent = "Registrar Seguimiento de Zoocriadero";
    saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
    btnCancelarEdicion.classList.add("d-none");

    gestionarReglasNegocio();
  }

  // ================= Eventos =================
  zooSelect.addEventListener("change", async function () {
    clearMessage();
    var selected = zoocriaderos.find(function (z) {
      return String(z.id_zoocriadero) === String(zooSelect.value);
    });
    direccionInput.value = selected ? selected.direccion : "";
    try {
      await loadTanques(zooSelect.value, null);
    } catch (error) {
      tanqueSelect.innerHTML = '<option value="">No fue posible cargar los tanques</option>';
      tanqueSelect.disabled = true;
      showMessage(error.message, "danger");
    }
  });

  obsInput.addEventListener("input", function () {
    obsCount.textContent = this.value.length;
  });

  historialBody.addEventListener("click", function (e) {
    var btnVer = e.target.closest("[data-ver]");
    if (btnVer) { abrirDetalle(btnVer.getAttribute("data-ver")); return; }

    var btnEditar = e.target.closest("[data-editar]");
    if (btnEditar) cargarEnFormulario(btnEditar.getAttribute("data-editar"));
  });

  btnCancelarEdicion.addEventListener("click", salirDeEdicion);

  document.getElementById("btnRecargarHistorial").addEventListener("click", loadHistorial);

  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    clearMessage();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    if (!zooSelect.value) { showMessage("Debe seleccionar un zoocriadero.", "danger"); return; }
    if (!tanqueSelect.value) { showMessage("Debe seleccionar un tanque.", "danger"); return; }
    if (!accionSelect.value) { showMessage("Debe seleccionar una acción.", "danger"); return; }

    var editando = idSeguimientoInput.value !== "";
    saveButton.disabled = true;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    var payload = {
      id_zoocriadero: Number(zooSelect.value),
      id_tanque: Number(tanqueSelect.value),
      fecha: fechaInput.value,
      ph: phInput.value,
      temperatura: temperaturaInput.value,
      numero_sembrados: Number(sembradosInput.value),
      numero_nacidos_hembra: Number(nacidosHembraInput.value),
      numero_nacidos_macho: Number(nacidosMachoInput.value),
      numero_muertos_hembra: Number(muertosHembraInput.value),
      numero_muertos_macho: Number(muertosMachoInput.value),
      observaciones: obsInput.value.trim(),
      id_actividad: Number(accionSelect.value),
    };
    if (editando) payload.id_seguimiento = Number(idSeguimientoInput.value);

    var funcion = editando ? "postUpdate" : "postCreate";

    try {
      var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=" + funcion, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(payload),
      });
      var result = await response.json().catch(function () { return null; });
      if (!response.ok || !result || result.ok === false) {
        throw new Error((result && result.message) || "No fue posible guardar el seguimiento.");
      }

      salirDeEdicion();
      showMessage(result.message, "success");
      await loadHistorial();
      zooSelect.focus();
    } catch (error) {
      showMessage(error.message, "danger");
    } finally {
      saveButton.disabled = false;
      if (idSeguimientoInput.value === "") {
        saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
      } else {
        saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar cambios';
      }
    }
  });
  gestionarReglasNegocio();

  Promise.all([loadZoocriaderos(), loadAcciones(), loadHistorial()]).catch(function (error) {
    showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
  });
})();