(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "acciones-zoocriadero") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Acciones&controlador=Acciones";

  var PERMISOS = {
    auxiliar: { crear: false, editar: false, inhabilitar: false },
    coordinador: { crear: true, editar: true, inhabilitar: true },
  };

  var data = []; 

  
  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function role() {
    return (window.SIGuppys && window.SIGuppys.getRole()) || "auxiliar";
  }
  function permisos() {
    return PERMISOS[role()];
  }
  function roleLabel() {
    var roles = window.SIGuppys && window.SIGuppys.ROLES;
    return (roles && roles[role()] && roles[role()].label) || role();
  }
  function lockedTitle(accion) {
    return "Tu rol (" + roleLabel() + ") no tiene permiso para " + accion + ".";
  }

  function showMessage(text, type) {
    var box = document.getElementById("accionesMessage");
    if (!box) return;
    box.className = "alert mb-3 alert-" + type;
    box.textContent = text;
  }
  function clearMessage() {
    var box = document.getElementById("accionesMessage");
    if (!box) return;
    box.className = "alert d-none mb-3";
    box.textContent = "";
  }


  async function getJson(funcion, extra) {
    var url = AJAX_URL + "?" + MODULO + "&funcion=" + funcion + (extra || "");
    var response = await fetch(url, { headers: { Accept: "application/json" } });
    var result = await response.json().catch(function () { return null; });
    if (!response.ok || !result || result.ok === false) {
      throw new Error((result && result.message) || "No fue posible consultar la información.");
    }
    return result.data || [];
  }

  async function postJson(funcion, payload) {
    var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=" + funcion, {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify(payload),
    });
    var result = await response.json().catch(function () { return null; });
    if (!response.ok || !result || result.ok === false) {
      throw new Error((result && result.message) || "No fue posible guardar.");
    }
    return result;
  }


  function normalizar(a) {
    return {
      id: Number(a.id_actividad),
      nombre: a.nombre || "",
      descripcion: a.descripcion || "",
      estado: Number(a.estado),
    };
  }

  
  function renderCrearBtn() {
    // El módulo ya no permite crear acciones nuevas desde la interfaz;
    // solo editar las existentes. Se deja este contenedor vacío.
    var wrap = document.getElementById("crearAccionWrap");
    if (wrap) wrap.innerHTML = "";
  }

 
  function renderAcciones(a) {
    var p = permisos();
    var btns =
      '<button type="button" class="btn-icon" data-action="editar" data-id="' + a.id + '" title="Editar">' +
      '<i class="fas fa-pen"></i></button>' +
      '<button type="button" class="btn-icon" data-action="ver" data-id="' + a.id +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>';

    if (p.inhabilitar) {
      if (a.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" data-action="inhabilitar" data-id="' +
          a.id + '" title="Inhabilitar"><i class="fas fa-ban"></i></button>';
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" data-action="habilitar" data-id="' +
          a.id + '" title="Habilitar"><i class="fas fa-check-circle"></i></button>';
      }
    }
    return '<div class="table-actions">' + btns + "</div>";
  }

  function renderRow(a) {
    var estadoBadge =
      a.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';
    return (
      "<tr>" +
      "<td>" + a.id + "</td>" +
      '<td><span class="fw-bold">' + escapeHtml(a.nombre) + "</span></td>" +
      "<td>" + escapeHtml(a.descripcion) + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(a) + "</td>" +
      "</tr>"
    );
  }

  function render() {
    var body = document.getElementById("accionesTableBody");
    if (!data.length) {
      body.innerHTML = '<tr class="sig-empty-row"><td colspan="5">No hay acciones registradas todavía.</td></tr>';
      return;
    }
    body.innerHTML = data.map(renderRow).join("");
    renderCrearBtn();
  }


  var form = document.getElementById("accionForm");
  var guardarBtn = document.getElementById("accionGuardarBtn");
  var limpiarBtn = document.getElementById("accionLimpiarBtn");

  function resetForm() {
    form.reset();
    form.elements["id_actividad"].value = "";
    form.elements["estado"].value = "1";
    guardarBtn.classList.add("d-none");
  }

  function fillFormFor(id) {
    var a = data.find(function (x) { return x.id === id; });
    if (!a) return;
    form.elements["id_actividad"].value = a.id;
    form.elements["nombre"].value = a.nombre;
    form.elements["descripcion"].value = a.descripcion;
    form.elements["estado"].value = String(a.estado);
    guardarBtn.classList.remove("d-none");
    form.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  function leerPayload() {
    return {
      nombre: form.elements["nombre"].value.trim(),
      descripcion: form.elements["descripcion"].value.trim(),
      estado: Number(form.elements["estado"].value),
    };
  }

  async function handleGuardar() {
    var payload = leerPayload();
    if (!payload.nombre) {
      showMessage("El nombre de la acción es obligatorio.", "danger");
      return;
    }

    var id = form.elements["id_actividad"].value;

    try {
      var res;
      if (id) {
        payload.id_actividad = Number(id);
        res = await postJson("postUpdate", payload); // UPDATE
      } else {
        res = await postJson("postCreate", payload); // INSERT
      }
      resetForm();
      await recargar();
      showMessage(res.message, "success");
    } catch (error) {
      showMessage(error.message, "danger");
    }
  }

  async function openDetailModal(id) {
    var a = data.find(function (x) { return x.id === id; });
    if (!a) return;

    var body = document.getElementById("accionDetailBody");
    body.innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Nombre</dt><dd class="col-7">' + escapeHtml(a.nombre) + "</dd>" +
      '<dt class="col-5">Descripción</dt><dd class="col-7">' + (escapeHtml(a.descripcion) || '<span class="text-muted">Sin descripción</span>') + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' +
      (a.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>') +
      "</dd>" +
      "</dl>";
    bootstrap.Modal.getOrCreateInstance(document.getElementById("accionDetailModal")).show();
  }

  async function toggleEstado(id, nuevoEstado) {
    if (!permisos().inhabilitar) return;
    var a = data.find(function (x) { return x.id === id; });
    if (!a) return;

    var accion = nuevoEstado === 1 ? "habilitar" : "inhabilitar";
    if (!confirm("¿Seguro que deseas " + accion + ' "' + a.nombre + '"?')) return;

    try {
      var res = await postJson("postEstado", { id_actividad: id, estado: nuevoEstado });
      await recargar();
      showMessage(res.message, "success");
    } catch (error) {
      showMessage(error.message, "danger");
    }
  }

  async function recargar() {
    var lista = await getJson("lista");
    data = lista.map(normalizar);
    render();
  }

 
  document.getElementById("accionesTableBody").addEventListener("click", function (e) {
    var btn = e.target.closest("[data-action]");
    if (!btn || btn.disabled) return;
    var id = Number(btn.getAttribute("data-id"));
    var action = btn.getAttribute("data-action");
    if (action === "ver") openDetailModal(id);
    if (action === "editar") fillFormFor(id);
    if (action === "inhabilitar") toggleEstado(id, 0);
    if (action === "habilitar") toggleEstado(id, 1);
  });

  document.getElementById("crearAccionWrap").addEventListener("click", function (e) {
    if (e.target.closest("#btnAbrirCrear")) {
      resetForm();
      form.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });

  form.addEventListener("submit", function (e) { e.preventDefault(); handleGuardar(); });
  guardarBtn.addEventListener("click", handleGuardar);
  limpiarBtn.addEventListener("click", resetForm);

  document.addEventListener("siguppys:role-changed", function () {
    render();
  });

 
  (async function init() {
    document.getElementById("accionesTableBody").innerHTML =
      '<tr class="sig-empty-row"><td colspan="5">Cargando acciones...</td></tr>';
    // El botón "Guardar Cambios" siempre queda habilitado: este módulo
    // ya no depende del rol para poder editar.
    try {
      await recargar();
      clearMessage();
    } catch (error) {
      document.getElementById("accionesTableBody").innerHTML =
        '<tr class="sig-empty-row"><td colspan="5">No se pudieron cargar los datos.</td></tr>';
      showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
    }
  })();
})();