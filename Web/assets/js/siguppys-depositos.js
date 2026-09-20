(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "terreno-depositos") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Deposito&controlador=Deposito";

  var tbody = document.getElementById("depositosTableBody");
  var search = document.getElementById("depositosSearch");
  var filtroEstado = document.getElementById("depositosEstadoFiltro");
  var count = document.getElementById("depositosCount");
  var message = document.getElementById("depositosMessage");

  var btnCrear = document.getElementById("btnCrearDeposito");
  var form = document.getElementById("depositoForm");
  var idDeposito = document.getElementById("id_deposito");
  var tipoSelect = document.getElementById("id_tipo_deposito");
  var sitioSelect = document.getElementById("id_sitio");
  var descripcion = document.getElementById("depositoDescripcion");
  var descripcionCount = document.getElementById("depositoDescripcionCount");
  var formMessage = document.getElementById("depositoFormMessage");
  var modalLabel = document.getElementById("depositoModalLabel");
  var guardar = document.getElementById("depositoSubmitBtn");

  var formModalEl = document.getElementById("depositoModal");
  var detailModalEl = document.getElementById("depositoDetailModal");

  var data = [];
  var tipos = [];
  var sitios = [];

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
      return String(x.id_deposito) === String(id);
    });
  }

  function ubicacion(deposito) {
    return [deposito.direccion, deposito.barrio, deposito.ciudad]
      .filter(function (parte) {
        return parte;
      })
      .join(" · ");
  }

  function renderAcciones(deposito) {
    var estado = Number(deposito.estado);

    return (
      '<div class="table-actions">' +
      '<button type="button" class="btn-icon" data-action="ver" data-id="' +
      deposito.id_deposito +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
      '<button type="button" class="btn-icon" data-action="editar" data-id="' +
      deposito.id_deposito +
      '" title="Editar"><i class="fas fa-pen"></i></button>' +
      '<button type="button" class="btn-icon ' +
      (estado === 1 ? "text-danger" : "text-success") +
      '" data-action="estado" data-id="' +
      deposito.id_deposito +
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

  function renderRow(deposito) {
    var estado = Number(deposito.estado);
    var estadoBadge =
      estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';

    return (
      "<tr>" +
      '<td><span class="fw-bold">' +
      escapeHtml(deposito.tipo_deposito) +
      "</span></td>" +
      "<td>" +
      escapeHtml(deposito.descripcion) +
      "</td>" +
      "<td>" +
      escapeHtml(deposito.sitio) +
      '<div class="small text-muted">' +
      escapeHtml(ubicacion(deposito)) +
      "</div>" +
      "</td>" +
      '<td class="text-center">' +
      estadoBadge +
      "</td>" +
      '<td class="text-center">' +
      renderAcciones(deposito) +
      "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = search.value.trim().toLowerCase();
    var estadoFiltro = filtroEstado.value;

    return data.filter(function (d) {
      var texto = [
        d.tipo_deposito,
        d.descripcion,
        d.sitio,
        d.direccion,
        d.barrio,
        d.comuna,
        d.ciudad
      ]
        .join(" ")
        .toLowerCase();

      var coincideTexto = !q || texto.indexOf(q) !== -1;
      var coincideEstado =
        estadoFiltro === "todos" ||
        (estadoFiltro === "activo" && Number(d.estado) === 1) ||
        (estadoFiltro === "inactivo" && Number(d.estado) === 0);

      return coincideTexto && coincideEstado;
    });
  }

  function render() {
    var rows = filteredData();

    if (!rows.length) {
      tbody.innerHTML =
        '<tr><td colspan="5" class="text-center text-muted py-4">No hay depósitos que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }

    count.textContent = rows.length + " de " + data.length + " depósitos";
  }

  function abrirDetalle(id) {
    var d = buscarPorId(id);

    if (!d) return;

    document.getElementById("depositoDetailBody").innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Tipo de depósito</dt><dd class="col-7">' + escapeHtml(d.tipo_deposito) + "</dd>" +
      '<dt class="col-5">Descripción</dt><dd class="col-7">' + escapeHtml(d.descripcion) + "</dd>" +
      '<dt class="col-5">Sitio</dt><dd class="col-7">' + escapeHtml(d.sitio) + "</dd>" +
      '<dt class="col-5">Dirección</dt><dd class="col-7">' + escapeHtml(d.direccion) + "</dd>" +
      '<dt class="col-5">Barrio</dt><dd class="col-7">' + escapeHtml(d.barrio) + "</dd>" +
      '<dt class="col-5">Comuna</dt><dd class="col-7">' + escapeHtml(d.comuna) + "</dd>" +
      '<dt class="col-5">Ciudad</dt><dd class="col-7">' + escapeHtml(d.ciudad) + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' + (Number(d.estado) === 1 ? "Activo" : "Inhabilitado") + "</dd>" +
      "</dl>";

    bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
  }

  function fillSelect(select, opciones, placeholder, seleccionado) {
    select.innerHTML =
      '<option value="">' +
      escapeHtml(placeholder) +
      "</option>" +
      opciones
        .map(function (o) {
          return (
            '<option value="' + escapeHtml(o.value) + '">' + escapeHtml(o.text) + "</option>"
          );
        })
        .join("");

    select.value = seleccionado ? String(seleccionado) : "";
  }

  // Solo se ofrecen los habilitados, más el que ya tiene el depósito que se
  // está editando (aunque después se haya inhabilitado) para no perderlo.
  function llenarTipos(seleccionado) {
    var opciones = tipos
      .filter(function (t) {
        return Number(t.estado) === 1 || String(t.id_tipo_deposito) === String(seleccionado);
      })
      .map(function (t) {
        return {
          value: t.id_tipo_deposito,
          text: t.nombre + (Number(t.estado) === 1 ? "" : " (inhabilitado)")
        };
      });

    fillSelect(
      tipoSelect,
      opciones,
      opciones.length ? "Seleccione el tipo de depósito" : "No hay tipos de depósito habilitados",
      seleccionado
    );
  }

  function llenarSitios(seleccionado) {
    var opciones = sitios
      .filter(function (s) {
        return Number(s.estado) === 1 || String(s.id_sitio) === String(seleccionado);
      })
      .map(function (s) {
        return {
          value: s.id_sitio,
          text:
            s.nombre +
            " — " +
            ubicacion(s) +
            (Number(s.estado) === 1 ? "" : " (inhabilitado)")
        };
      });

    fillSelect(
      sitioSelect,
      opciones,
      opciones.length ? "Seleccione el sitio" : "No hay sitios habilitados",
      seleccionado
    );
  }

  async function cargarCatalogos() {
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=catalogos");

    tipos = result.data.tipos || [];
    sitios = result.data.sitios || [];
  }

  async function abrirFormulario(deposito) {
    var editando = !!deposito;

    try {
      await cargarCatalogos();
    } catch (error) {
      showMessage(error.message, "danger");
      return;
    }

    form.reset();
    clearFormMessage();

    idDeposito.value = editando ? deposito.id_deposito : "";
    llenarTipos(editando ? deposito.id_tipo_deposito : "");
    llenarSitios(editando ? deposito.id_sitio : "");
    descripcion.value = editando ? deposito.descripcion || "" : "";
    descripcionCount.textContent = descripcion.value.length;

    modalLabel.textContent = editando ? "Editar Depósito" : "Registrar Depósito";
    guardar.disabled = false;
    guardar.innerHTML = '<i class="fas fa-save me-1"></i>' + (editando ? "Guardar cambios" : "Guardar");

    bootstrap.Modal.getOrCreateInstance(formModalEl).show();
  }

  async function cambiarEstado(id, estado) {
    var deposito = buscarPorId(id);
    var texto = estado === 1 ? "habilitar" : "inhabilitar";
    var etiqueta = deposito ? ' "' + deposito.tipo_deposito + '" (' + deposito.sitio + ")" : "";

    if (!window.confirm("¿Desea " + texto + " el depósito" + etiqueta + "?")) return;

    try {
      var result = await postJson("postEstado", {
        id_deposito: Number(id),
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
      '<tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr>';

    try {
      var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=lista");
      data = result.data || [];
      render();
    } catch (error) {
      tbody.innerHTML =
        '<tr><td colspan="5" class="text-center text-danger py-4">' +
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
      var deposito = buscarPorId(id);
      if (deposito) abrirFormulario(deposito);
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

    var editando = idDeposito.value !== "";

    var payload = {
      id_tipo_deposito: Number(tipoSelect.value),
      id_sitio: Number(sitioSelect.value),
      descripcion: descripcion.value.trim()
    };

    if (editando) {
      payload.id_deposito = Number(idDeposito.value);
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
