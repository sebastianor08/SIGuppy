

(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "copia-seguridad") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=CopiaSeguridad&controlador=CopiaSeguridad";

  // ---------- Utilidades ----------
  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function fmtFechaHora(valor) {
    if (!valor) return "—";
    var d = new Date(valor.replace(" ", "T"));
    if (isNaN(d)) return valor;
    return d.toLocaleString("es-CO", {
      year: "numeric", month: "2-digit", day: "2-digit",
      hour: "2-digit", minute: "2-digit",
    });
  }

  function showMessage(text, type) {
    var box = document.getElementById("copiaSeguridadMessage");
    if (!box) return;
    box.className = "alert mb-3 alert-" + type;
    box.textContent = text;
  }
  function clearMessage() {
    var box = document.getElementById("copiaSeguridadMessage");
    if (!box) return;
    box.className = "alert d-none mb-3";
    box.textContent = "";
  }

  // ---------- Llamadas al backend ----------
  async function getJson(funcion, extra) {
    var url = AJAX_URL + "?" + MODULO + "&funcion=" + funcion + (extra || "");
    var response = await fetch(url, { headers: { Accept: "application/json" } });
    var result = await response.json().catch(function () { return null; });
    if (!response.ok || !result || result.ok === false) {
      throw new Error((result && result.message) || "No fue posible consultar la información.");
    }
    return result.data;
  }

  // ---------- Tarjetas de estado ----------
  async function cargarEstado() {
    var pill = document.getElementById("bdEstadoPill");
    var texto = document.getElementById("bdEstadoTexto");
    try {
      var data = await getJson("estado");
      document.getElementById("statUltimoBackup").textContent = data.ultimo_backup ? fmtFechaHora(data.ultimo_backup) : "Sin registros aún";
      document.getElementById("statTamanoBD").textContent = data.tamano_bd || "—";
      document.getElementById("statRespaldos").textContent = data.cantidad_respaldos + (data.cantidad_respaldos === 1 ? " respaldo" : " respaldos");

      pill.className = "badge-estado " + (data.db_online ? "activo" : "inactivo");
      texto.textContent = "Base de Datos: " + (data.db_online ? "En línea / Saludable" : "Sin conexión");
    } catch (error) {
      pill.className = "badge-estado inactivo";
      texto.textContent = "Base de Datos: sin conexión";
      showMessage(error.message, "danger");
    }
  }

  // ---------- Historial de auditoría ----------
  function badgeTipo(tipo) {
    var mapa = {
      descarga: '<span class="badge badge-primary">Descarga</span>',
      restauracion: '<span class="badge badge-warning">Restauración</span>',
      automatica: '<span class="badge badge-info">Automática</span>',
    };
    return mapa[tipo] || escapeHtml(tipo);
  }

  function badgeEstado(estado) {
    return estado === "exito"
      ? '<span class="badge-estado activo">Éxito</span>'
      : '<span class="badge-estado inactivo">Error</span>';
  }

  function renderHistorialRow(fila) {
    var descargaBtn = fila.nombre_archivo
      ? '<button type="button" class="btn-icon" data-archivo="' + encodeURIComponent(fila.nombre_archivo) +
        '" title="Descargar este respaldo"><i class="fas fa-download"></i></button>'
      : "";

    var detalleBtn = (fila.estado === "error" && fila.detalle)
      ? '<button type="button" class="btn-icon text-danger" data-detalle="' + encodeURIComponent(fila.detalle) +
        '" title="Ver detalle del error"><i class="fas fa-circle-info"></i></button>'
      : "";

    return (
      "<tr>" +
      "<td>" + fmtFechaHora(fila.fecha_hora) + "</td>" +
      "<td>" + badgeTipo(fila.tipo_operacion) + "</td>" +
      "<td>" + (fila.nombre_archivo ? escapeHtml(fila.nombre_archivo) : "—") + "</td>" +
      "<td>" + escapeHtml(fila.usuario_nombre) + "</td>" +
      '<td class="text-center">' + badgeEstado(fila.estado) + "</td>" +
      '<td class="text-center"><div class="table-actions">' + descargaBtn + detalleBtn + "</div></td>" +
      "</tr>"
    );
  }

  async function cargarHistorial() {
    var tbody = document.getElementById("historialTableBody");
    try {
      var filas = await getJson("historial");
      tbody.innerHTML = filas.length
        ? filas.map(renderHistorialRow).join("")
        : '<tr class="sig-empty-row"><td colspan="6">Todavía no hay operaciones registradas.</td></tr>';
    } catch (error) {
      tbody.innerHTML = '<tr class="sig-empty-row"><td colspan="6">No se pudo cargar el historial.</td></tr>';
      showMessage(error.message, "danger");
    }
  }

  document.getElementById("historialTableBody").addEventListener("click", function (e) {
    var descargaBtn = e.target.closest("[data-archivo]");
    if (descargaBtn) {
      var nombre = decodeURIComponent(descargaBtn.getAttribute("data-archivo"));
      window.open(AJAX_URL + "?" + MODULO + "&funcion=descargarArchivo&archivo=" + encodeURIComponent(nombre), "_blank");
      return;
    }
    var detalleBtn = e.target.closest("[data-detalle]");
    if (detalleBtn) {
      alert(decodeURIComponent(detalleBtn.getAttribute("data-detalle")));
    }
  });

  // ---------- Descargar Copia (genera un .sql nuevo) ----------
  var btnDescargar = document.getElementById("btnDescargarCopia");

  btnDescargar.addEventListener("click", async function () {
    btnDescargar.disabled = true;
    var textoOriginal = btnDescargar.innerHTML;
    btnDescargar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando copia...';
    clearMessage();

    try {
      var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=descargar");
      if (!response.ok) {
        var textoError = await response.text();
        throw new Error(textoError || "No se pudo generar la copia de seguridad.");
      }
      var blob = await response.blob();
      var disposicion = response.headers.get("Content-Disposition") || "";
      var match = disposicion.match(/filename="?([^"]+)"?/);
      var nombreArchivo = match ? match[1] : "backup.sql";

      var urlBlob = URL.createObjectURL(blob);
      var a = document.createElement("a");
      a.href = urlBlob;
      a.download = nombreArchivo;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(urlBlob);

      showMessage("Copia de seguridad generada y descargada: " + nombreArchivo, "success");
      await Promise.all([cargarEstado(), cargarHistorial()]);
    } catch (error) {
      showMessage(error.message, "danger");
    } finally {
      btnDescargar.disabled = false;
      btnDescargar.innerHTML = textoOriginal;
    }
  });

  // ---------- Restaurar Base de Datos ----------
  var restaurarModalEl = document.getElementById("restaurarModal");
  var restaurarForm = document.getElementById("restaurarForm");
  var restaurarSelect = document.getElementById("restaurarSelectExistente");
  var restaurarArchivoInput = document.getElementById("restaurarArchivoInput");
  var restaurarSubmitBtn = document.getElementById("restaurarSubmitBtn");

  document.getElementById("btnAbrirRestaurar").addEventListener("click", async function () {
    restaurarForm.reset();
    restaurarSelect.innerHTML = '<option value="">Cargando respaldos...</option>';
    try {
      var archivos = await getJson("archivos");
      restaurarSelect.innerHTML =
        '<option value="">Seleccione un respaldo...</option>' +
        archivos
          .map(function (a) {
            return '<option value="' + escapeHtml(a.nombre) + '">' + escapeHtml(a.nombre) + " (" + fmtFechaHora(a.fecha) + ")</option>";
          })
          .join("");
    } catch (error) {
      restaurarSelect.innerHTML = '<option value="">No se pudieron cargar los respaldos existentes</option>';
    }
    bootstrap.Modal.getOrCreateInstance(restaurarModalEl).show();
  });

  // Elegir uno de los dos métodos deshabilita el otro, para no enviar ambos a la vez.
  restaurarSelect.addEventListener("change", function () {
    restaurarArchivoInput.disabled = this.value !== "";
  });
  restaurarArchivoInput.addEventListener("change", function () {
    restaurarSelect.disabled = this.files.length > 0;
  });

  restaurarForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    var archivoElegido = restaurarSelect.value;
    var archivoSubido = restaurarArchivoInput.files[0];

    if (!archivoElegido && !archivoSubido) {
      alert("Elige un respaldo existente o sube un archivo .sql.");
      return;
    }
    if (!confirm("Esto reemplazará los datos actuales de BD_Dengue_SIGuppy. ¿Deseas continuar?")) {
      return;
    }

    var formData = new FormData();
    if (archivoSubido) {
      formData.append("archivo", archivoSubido);
    } else {
      formData.append("archivo_existente", archivoElegido);
    }

    restaurarSubmitBtn.disabled = true;
    restaurarSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Restaurando...';

    try {
      var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=restaurar", {
        method: "POST",
        body: formData,
      });
      var result = await response.json().catch(function () { return null; });
      if (!response.ok || !result || result.ok === false) {
        throw new Error((result && result.message) || "No fue posible restaurar la base de datos.");
      }

      bootstrap.Modal.getOrCreateInstance(restaurarModalEl).hide();
      showMessage(result.message, "success");
      await Promise.all([cargarEstado(), cargarHistorial()]);
      restaurarArchivoInput.disabled = false;
      restaurarSelect.disabled = false;
    } catch (error) {
      alert(error.message);
      await cargarHistorial(); // el intento fallido también queda auditado
    } finally {
      restaurarSubmitBtn.disabled = false;
      restaurarSubmitBtn.innerHTML = "Restaurar";
    }
  });

  // ---------- Arranque ----------
  clearMessage();
  cargarEstado();
  cargarHistorial();
})();
