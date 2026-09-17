/* =========================================================
   SIGuppys — Seguimiento de Zoocriadero
   =========================================================
   Todo sale de PostgreSQL a través del router MVC:
     Web/ajax.php?modulo=SeguimientoZoocriadero&controlador=SeguimientoZoocriadero&funcion=...

     zoocriaderos -> GET  llena el select "Zoocriadero"
     tanques      -> GET  llena el select "Tanque" del zoocriadero elegido
     acciones     -> GET  llena el select "Acción" (tabla actividad)
     historial    -> GET  tabla de seguimientos registrados
     postCreate   -> POST INSERT en seguimiento_zoocriadero
     postUpdate   -> POST UPDATE del seguimiento que se está editando
   ========================================================= */
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
  var nacidosInput = document.getElementById("numero_nacidos");
  var muertosInput = document.getElementById("numero_muertos");
  var obsInput = document.getElementById("observaciones");
  var obsCount = document.getElementById("observacionesCount");
  var message = document.getElementById("seguimientoMessage");
  var saveButton = document.getElementById("btnGuardarSeguimiento");
  var tituloEl = document.getElementById("seguimientoTitulo");
  var btnCancelarEdicion = document.getElementById("btnCancelarEdicion");
  var historialBody = document.getElementById("historialBody");

  var zoocriaderos = [];
  var historial = [];

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

  // ---------- Carga de catálogos ----------
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

  // ---------- Historial ----------
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
          '<button type="button" class="btn-icon" data-editar="' + s.id_seguimiento +
          '" title="Editar"><i class="fas fa-pen"></i></button>' +
          "</td>" +
          "</tr>"
        );
      })
      .join("");
  }

  // ---------- Modo edición ----------
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
    nacidosInput.value = s.numero_nacidos;
    muertosInput.value = s.numero_muertos;
    obsInput.value = s.observaciones || "";
    obsCount.textContent = obsInput.value.length;
    accionSelect.value = s.id_actividad || "";

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
    fechaInput.value = new Date().toISOString().slice(0, 10);
    tituloEl.textContent = "Registrar Seguimiento de Zoocriadero";
    saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
    btnCancelarEdicion.classList.add("d-none");
  }

  // ---------- Eventos ----------
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
    var btn = e.target.closest("[data-editar]");
    if (btn) cargarEnFormulario(btn.getAttribute("data-editar"));
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
      numero_nacidos: Number(nacidosInput.value),
      numero_muertos: Number(muertosInput.value),
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

  // ---------- Arranque ----------
  fechaInput.value = new Date().toISOString().slice(0, 10);

  Promise.all([loadZoocriaderos(), loadAcciones(), loadHistorial()]).catch(function (error) {
    showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
  });
})();
