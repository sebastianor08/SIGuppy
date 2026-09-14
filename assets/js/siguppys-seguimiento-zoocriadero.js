(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "seguimiento-zoocriadero") return;

  // Router MVC: Web/ajax.php?modulo=...&controlador=...&funcion=...
  // Ruta relativa desde View/Seguimiento_Zoocriadero/seguimiento-zoocriadero.php
  // (única página que carga este archivo) hasta Web/ajax.php en la raíz del proyecto.
  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=SeguimientoZoocriadero&controlador=SeguimientoZoocriadero";
  var form = document.getElementById("seguimientoZoocriaderoForm");
  var zooSelect = document.getElementById("id_zoocriadero");
  var tanqueSelect = document.getElementById("id_tanque");
  var direccionInput = document.getElementById("direccion");
  var accionSelect = document.getElementById("id_actividad");
  var fechaInput = document.getElementById("fecha");
  var obsInput = document.getElementById("observaciones");
  var obsCount = document.getElementById("observacionesCount");
  var message = document.getElementById("seguimientoMessage");
  var saveButton = document.getElementById("btnGuardarSeguimiento");
  var zoocriaderos = [];

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (c) {
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

  async function loadZoocriaderos() {
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=zoocriaderos");
    zoocriaderos = result.data || [];
    zooSelect.innerHTML = '<option value="">Seleccione un zoocriadero</option>' +
      zoocriaderos.map(function (z) {
        return '<option value="' + z.id_zoocriadero + '">' + escapeHtml(z.nombre) + '</option>';
      }).join("");
  }

  async function loadAcciones() {
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=acciones");
    accionSelect.innerHTML = '<option value="">Seleccione la acción</option>' +
      (result.data || []).map(function (a) {
        return '<option value="' + a.id_actividad + '">' + escapeHtml(a.nombre) + '</option>';
      }).join("");
  }

  async function loadTanques(idZoocriadero) {
    tanqueSelect.disabled = true;
    tanqueSelect.innerHTML = '<option value="">Cargando tanques...</option>';
    if (!idZoocriadero) {
      tanqueSelect.innerHTML = '<option value="">Seleccione primero un zoocriadero</option>';
      return;
    }
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=tanques&id_zoocriadero=" + encodeURIComponent(idZoocriadero));
    var tanques = result.data || [];
    tanqueSelect.innerHTML = tanques.length
      ? '<option value="">Seleccione un tanque</option>' + tanques.map(function (t) {
          return '<option value="' + t.id_tanque + '">Tanque ' + escapeHtml(t.numero_tanque) + ' - ' + escapeHtml(t.tipo_tanque) + '</option>';
        }).join("")
      : '<option value="">No hay tanques activos para este zoocriadero</option>';
    tanqueSelect.disabled = tanques.length === 0;
  }

  zooSelect.addEventListener("change", async function () {
    clearMessage();
    var selected = zoocriaderos.find(function (z) { return String(z.id_zoocriadero) === String(zooSelect.value); });
    direccionInput.value = selected ? selected.direccion : "";
    try {
      await loadTanques(zooSelect.value);
    } catch (error) {
      tanqueSelect.innerHTML = '<option value="">No fue posible cargar los tanques</option>';
      tanqueSelect.disabled = true;
      showMessage(error.message, "danger");
    }
  });

  obsInput.addEventListener("input", function () {
    obsCount.textContent = this.value.length;
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    clearMessage();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    saveButton.disabled = true;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    var payload = {
      id_zoocriadero: Number(zooSelect.value),
      id_tanque: Number(tanqueSelect.value),
      fecha: fechaInput.value,
      numero_nacidos: Number(document.getElementById("numero_nacidos").value),
      numero_muertos: Number(document.getElementById("numero_muertos").value),
      observaciones: obsInput.value.trim(),
      id_actividad: Number(accionSelect.value)
    };

    try {
      var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=postCreate", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(payload)
      });
      var result = await response.json().catch(function () { return null; });
      if (!response.ok || !result || result.ok === false) {
        throw new Error((result && result.message) || "No fue posible guardar el seguimiento.");
      }

      showMessage(result.message, "success");
      form.reset();
      direccionInput.value = "";
      obsCount.textContent = "0";
      tanqueSelect.disabled = true;
      tanqueSelect.innerHTML = '<option value="">Seleccione primero un zoocriadero</option>';
      fechaInput.value = new Date().toISOString().slice(0, 10);
      zooSelect.focus();
    } catch (error) {
      showMessage(error.message, "danger");
    } finally {
      saveButton.disabled = false;
      saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
    }
  });

  fechaInput.value = new Date().toISOString().slice(0, 10);

  Promise.all([loadZoocriaderos(), loadAcciones()]).catch(function (error) {
    showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
  });
})();
