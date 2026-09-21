(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "terreno-actividades") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Actividad&controlador=Actividad";

  var tbody = document.getElementById("actividadesTableBody");
  var search = document.getElementById("actividadesSearch");
  var filtroEstado = document.getElementById("actividadesEstadoFiltro");
  var count = document.getElementById("actividadesCount");
  var message = document.getElementById("actividadesMessage");

  var btnCrear = document.getElementById("btnCrearActividad");
  var form = document.getElementById("actividadForm");
  var idActividad = document.getElementById("id_actividad");
  var nombre = document.getElementById("nombreActividad");
  var descripcion = document.getElementById("descripcionActividad");
  var descripcionCount = document.getElementById("actividadDescripcionCount");
  var formMessage = document.getElementById("actividadFormMessage");
  var modalLabel = document.getElementById("actividadModalLabel");
  var guardar = document.getElementById("actividadSubmitBtn");

  var formModalEl = document.getElementById("actividadModal");
  var detailModalEl = document.getElementById("actividadDetailModal");

  var data = [];

  function escapeHtml(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (c) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
      }[c];
    });
  }

  function showMessage(text, type) {
    message.className = "alert mb-3 alert-" + type;
    message.textContent = text;
  }

  function showFormMessage(text, type) {
    formMessage.className = "alert mb-0 alert-" + type;
    formMessage.textContent = text;
  }

  function clearFormMessage() {
    formMessage.className = "alert d-none mb-0";
    formMessage.textContent = "";
  }

  async function getJson(url) {
    var response = await fetch(url, {
      headers: { Accept: "application/json" }
    });

    var result = await response.json().catch(function () {
      return null;
    });

    if (!response.ok || !result || result.ok === false) {
      throw new Error((result && result.message) || "No fue posible consultar la información.");
    }

    return result;
  }

  async function postJson(funcion, payload) {
    var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=" + funcion, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json"
      },
      body: JSON.stringify(payload)
    });

    var result = await response.json().catch(function () {
      return null;
    });

    if (!response.ok || !result || result.ok === false) {
      throw new Error((result && result.message) || "No fue posible guardar la información.");
    }

    return result;
  }

  function buscarPorId(id) {
    return data.find(function (x) {
      return String(x.id_actividad) === String(id);
    });
  }

  function renderAcciones(actividad) {
    var estado = Number(actividad.estado);

    return (
      '<div class="table-actions">' +
      '<button type="button" class="btn-icon" data-action="ver" data-id="' +
      actividad.id_actividad +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
      '<button type="button" class="btn-icon" data-action="editar" data-id="' +
      actividad.id_actividad +
      '" title="Editar"><i class="fas fa-pen"></i></button>' +
      '<button type="button" class="btn-icon ' +
      (estado === 1 ? "text-danger" : "text-success") +
      '" data-action="estado" data-id="' +
      actividad.id_actividad +
      '" data-estado="' +
      (estado === 1 ? 0 : 1) +
      '" title="' +
      (estado === 1 ? "Inhabilitar" : "Habilitar") +
      '">' +
      '<i class="fas ' +
      (estado === 1 ? "fa-ban" : "fa-check-circle") +
      '"></i></button>' +
      "</div>"
    );
  }

  function renderRow(actividad) {
    var estado = Number(actividad.estado);
    var estadoBadge =
      estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';

    return (
      "<tr>" +
      '<td><span class="fw-bold">' +
      escapeHtml(actividad.nombre) +
      "</span></td>" +
      "<td>" +
      escapeHtml(actividad.descripcion) +
      "</td>" +
      '<td class="text-center">' +
      estadoBadge +
      "</td>" +
      '<td class="text-center">' +
      renderAcciones(actividad) +
      "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = search.value.trim().toLowerCase();
    var estadoFiltro = filtroEstado.value;

    return data.filter(function (a) {
      var texto = ((a.nombre || "") + " " + (a.descripcion || "")).toLowerCase();

      var coincideTexto = !q || texto.indexOf(q) !== -1;
      var coincideEstado =
        estadoFiltro === "todos" ||
        (estadoFiltro === "activo" && Number(a.estado) === 1) ||
        (estadoFiltro === "inactivo" && Number(a.estado) === 0);

      return coincideTexto && coincideEstado;
    });
  }

  function render() {
    var rows = filteredData();

    if (!rows.length) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-muted py-4">No hay actividades que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }

    count.textContent = rows.length + " de " + data.length + " actividades";
  }

  function abrirDetalle(id) {
    var actividad = buscarPorId(id);

    if (!actividad) return;

    document.getElementById("actividadDetailBody").innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Nombre</dt><dd class="col-7">' + escapeHtml(actividad.nombre) + "</dd>" +
      '<dt class="col-5">Descripción</dt><dd class="col-7">' + escapeHtml(actividad.descripcion) + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' + (Number(actividad.estado) === 1 ? "Activo" : "Inhabilitado") + "</dd>" +
      "</dl>";

    bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
  }

  function abrirFormulario(actividad) {
    var editando = !!actividad;

    form.reset();
    clearFormMessage();

    idActividad.value = editando ? actividad.id_actividad : "";
    nombre.value = editando ? actividad.nombre || "" : "";
    descripcion.value = editando ? actividad.descripcion || "" : "";
    descripcionCount.textContent = descripcion.value.length;

    modalLabel.textContent = editando ? "Editar Actividad" : "Registrar Actividad";
    guardar.disabled = false;
    guardar.innerHTML = '<i class="fas fa-save me-1"></i>' + (editando ? "Guardar cambios" : "Guardar");

    bootstrap.Modal.getOrCreateInstance(formModalEl).show();
  }

  async function cambiarEstado(id, estado) {
    var actividad = buscarPorId(id);
    var texto = estado === 1 ? "habilitar" : "inhabilitar";
    var etiqueta = actividad ? ' "' + actividad.nombre + '"' : "";

    if (!window.confirm("¿Desea " + texto + " la actividad" + etiqueta + "?")) return;

    try {
      var result = await postJson("postEstado", {
        id_actividad: Number(id),
        estado: Number(estado)
      });

      showMessage(result.message, "success");
      await cargar();
    } catch (error) {
      showMessage(error.message, "danger");
    }
  }

  async function cargar() {
    tbody.innerHTML =
      '<tr><td colspan="4" class="text-center text-muted py-4">Cargando...</td></tr>';

    try {
      var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=lista");
      data = result.data || [];
      render();
    } catch (error) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-danger py-4">' +
        escapeHtml(error.message) +
        "</td></tr>";
    }
  }

  search.addEventListener("input", render);
  filtroEstado.addEventListener("change", render);

  descripcion.addEventListener("input", function () {
    descripcionCount.textContent = this.value.length;
  });

  btnCrear.addEventListener("click", function () {
    abrirFormulario(null);
  });

  tbody.addEventListener("click", function (event) {
    var button = event.target.closest("[data-action]");
    if (!button) return;

    var action = button.getAttribute("data-action");
    var id = button.getAttribute("data-id");

    if (action === "ver") {
      abrirDetalle(id);
    } else if (action === "editar") {
      var actividad = buscarPorId(id);
      if (actividad) abrirFormulario(actividad);
    } else if (action === "estado") {
      cambiarEstado(id, Number(button.getAttribute("data-estado")));
    }
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    clearFormMessage();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var editando = idActividad.value !== "";

    var payload = {
      nombre: nombre.value.trim(),
      descripcion: descripcion.value.trim()
    };

    if (editando) {
      payload.id_actividad = Number(idActividad.value);
    }

    guardar.disabled = true;
    guardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    try {
      var result = await postJson(editando ? "postUpdate" : "postCreate", payload);

      bootstrap.Modal.getOrCreateInstance(formModalEl).hide();
      showMessage(result.message, "success");
      await cargar();
    } catch (error) {
      showFormMessage(error.message, "danger");
      guardar.disabled = false;
      guardar.innerHTML =
        '<i class="fas fa-save me-1"></i>' + (editando ? "Guardar cambios" : "Guardar");
    }
  });

  cargar();
})();
