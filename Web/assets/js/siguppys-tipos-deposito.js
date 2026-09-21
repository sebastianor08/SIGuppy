(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "terreno-tipo-depositos") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=TipoDeposito&controlador=TipoDeposito";

  var tbody = document.getElementById("tiposDepositoTableBody");
  var search = document.getElementById("tiposDepositoSearch");
  var filtroEstado = document.getElementById("tiposDepositoEstadoFiltro");
  var count = document.getElementById("tiposDepositoCount");
  var message = document.getElementById("tiposDepositoMessage");

  var btnCrear = document.getElementById("btnCrearTipoDeposito");
  var form = document.getElementById("tipoDepositoForm");
  var idTipo = document.getElementById("id_tipo_deposito");
  var nombre = document.getElementById("nombreTipoDeposito");
  var descripcion = document.getElementById("descripcionTipoDeposito");
  var descripcionCount = document.getElementById("tipoDepositoDescripcionCount");
  var formMessage = document.getElementById("tipoDepositoFormMessage");
  var modalLabel = document.getElementById("tipoDepositoModalLabel");
  var guardar = document.getElementById("tipoDepositoSubmitBtn");

  var formModalEl = document.getElementById("tipoDepositoModal");
  var detailModalEl = document.getElementById("tipoDepositoDetailModal");

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
      return String(x.id_tipo_deposito) === String(id);
    });
  }

  function renderAcciones(tipo) {
    var estado = Number(tipo.estado);

    return (
      '<div class="table-actions">' +
      '<button type="button" class="btn-icon" data-action="ver" data-id="' +
      tipo.id_tipo_deposito +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
      '<button type="button" class="btn-icon" data-action="editar" data-id="' +
      tipo.id_tipo_deposito +
      '" title="Editar"><i class="fas fa-pen"></i></button>' +
      '<button type="button" class="btn-icon ' +
      (estado === 1 ? "text-danger" : "text-success") +
      '" data-action="estado" data-id="' +
      tipo.id_tipo_deposito +
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

  function renderRow(tipo) {
    var estado = Number(tipo.estado);
    var estadoBadge =
      estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';

    return (
      "<tr>" +
      '<td><span class="fw-bold">' +
      escapeHtml(tipo.nombre) +
      "</span></td>" +
      "<td>" +
      escapeHtml(tipo.descripcion) +
      "</td>" +
      '<td class="text-center">' +
      estadoBadge +
      "</td>" +
      '<td class="text-center">' +
      renderAcciones(tipo) +
      "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = search.value.trim().toLowerCase();
    var estadoFiltro = filtroEstado.value;

    return data.filter(function (t) {
      var texto = ((t.nombre || "") + " " + (t.descripcion || "")).toLowerCase();

      var coincideTexto = !q || texto.indexOf(q) !== -1;
      var coincideEstado =
        estadoFiltro === "todos" ||
        (estadoFiltro === "activo" && Number(t.estado) === 1) ||
        (estadoFiltro === "inactivo" && Number(t.estado) === 0);

      return coincideTexto && coincideEstado;
    });
  }

  function render() {
    var rows = filteredData();

    if (!rows.length) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-muted py-4">No hay tipos de depósito que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }

    count.textContent = rows.length + " de " + data.length + " tipos de depósito";
  }

  function abrirDetalle(id) {
    var tipo = buscarPorId(id);

    if (!tipo) return;

    document.getElementById("tipoDepositoDetailBody").innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Nombre</dt><dd class="col-7">' + escapeHtml(tipo.nombre) + "</dd>" +
      '<dt class="col-5">Descripción</dt><dd class="col-7">' + escapeHtml(tipo.descripcion) + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' + (Number(tipo.estado) === 1 ? "Activo" : "Inhabilitado") + "</dd>" +
      "</dl>";

    bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
  }

  function abrirFormulario(tipo) {
    var editando = !!tipo;

    form.reset();
    clearFormMessage();

    idTipo.value = editando ? tipo.id_tipo_deposito : "";
    nombre.value = editando ? tipo.nombre || "" : "";
    descripcion.value = editando ? tipo.descripcion || "" : "";
    descripcionCount.textContent = descripcion.value.length;

    modalLabel.textContent = editando ? "Editar Tipo de Depósito" : "Registrar Tipo de Depósito";
    guardar.disabled = false;
    guardar.innerHTML = '<i class="fas fa-save me-1"></i>' + (editando ? "Guardar cambios" : "Guardar");

    bootstrap.Modal.getOrCreateInstance(formModalEl).show();
  }

  async function cambiarEstado(id, estado) {
    var tipo = buscarPorId(id);
    var texto = estado === 1 ? "habilitar" : "inhabilitar";
    var nombreTipo = tipo ? ' "' + tipo.nombre + '"' : "";

    if (!window.confirm("¿Desea " + texto + " el tipo de depósito" + nombreTipo + "?")) return;

    try {
      var result = await postJson("postEstado", {
        id_tipo_deposito: Number(id),
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
      var tipo = buscarPorId(id);
      if (tipo) abrirFormulario(tipo);
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

    var editando = idTipo.value !== "";

    var payload = {
      nombre: nombre.value.trim(),
      descripcion: descripcion.value.trim()
    };

    if (editando) {
      payload.id_tipo_deposito = Number(idTipo.value);
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
