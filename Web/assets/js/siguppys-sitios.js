(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "sitios") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Sitio&controlador=Sitio";

  var tbody = document.getElementById("sitiosTableBody");
  var search = document.getElementById("sitiosSearch");
  var filtroEstado = document.getElementById("sitiosEstadoFiltro");
  var count = document.getElementById("sitiosCount");
  var message = document.getElementById("sitiosMessage");

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

  function renderAcciones(sitio) {
    var estado = Number(sitio.estado);

    return (
      '<div class="table-actions">' +
      '<button type="button" class="btn-icon" data-action="ver" data-id="' +
      sitio.id_sitio +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
      '<a class="btn-icon" href="../RegistrarSitio/RegistrarSitioView.php?id_sitio=' +
      encodeURIComponent(sitio.id_sitio) +
      '" title="Editar"><i class="fas fa-pen"></i></a>' +
      '<button type="button" class="btn-icon ' +
      (estado === 1 ? "text-danger" : "text-success") +
      '" data-action="estado" data-id="' +
      sitio.id_sitio +
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

  function renderRow(sitio) {
    var estado = Number(sitio.estado);
    var estadoBadge =
      estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';

    return (
      "<tr>" +
      '<td><span class="fw-bold">' +
      escapeHtml(sitio.nombre) +
      "</span></td>" +
      "<td>" +
      escapeHtml(sitio.descripcion) +
      "</td>" +
      "<td>" +
      escapeHtml(sitio.direccion) +
      '<div class="small text-muted">' +
      escapeHtml(sitio.barrio) +
      " · " +
      escapeHtml(sitio.comuna) +
      " · " +
      escapeHtml(sitio.ciudad) +
      "</div>" +
      "</td>" +
      "<td>" +
      escapeHtml(sitio.fecha) +
      "</td>" +
      '<td class="text-center">' +
      estadoBadge +
      "</td>" +
      '<td class="text-center">' +
      renderAcciones(sitio) +
      "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = search.value.trim().toLowerCase();
    var estadoFiltro = filtroEstado.value;

    return data.filter(function (s) {
      var texto = (
        s.nombre +
        " " +
        s.descripcion +
        " " +
        s.direccion +
        " " +
        s.barrio +
        " " +
        s.comuna +
        " " +
        s.ciudad +
        " " +
        s.departamento
      ).toLowerCase();

      var coincideTexto = !q || texto.indexOf(q) !== -1;
      var coincideEstado =
        estadoFiltro === "todos" ||
        (estadoFiltro === "activo" && Number(s.estado) === 1) ||
        (estadoFiltro === "inactivo" && Number(s.estado) === 0);

      return coincideTexto && coincideEstado;
    });
  }

  function render() {
    var rows = filteredData();

    if (!rows.length) {
      tbody.innerHTML =
        '<tr><td colspan="6" class="text-center text-muted py-4">No hay sitios que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }

    count.textContent = rows.length + " de " + data.length + " sitios";
  }

  function abrirDetalle(id) {
    var sitio = data.find(function (x) {
      return String(x.id_sitio) === String(id);
    });

    if (!sitio) return;

    var body = document.getElementById("sitioDetailBody");

    body.innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Nombre</dt><dd class="col-7">' + escapeHtml(sitio.nombre) + "</dd>" +
      '<dt class="col-5">Descripción</dt><dd class="col-7">' + escapeHtml(sitio.descripcion) + "</dd>" +
      '<dt class="col-5">Dirección</dt><dd class="col-7">' + escapeHtml(sitio.direccion) + "</dd>" +
      '<dt class="col-5">Barrio</dt><dd class="col-7">' + escapeHtml(sitio.barrio) + "</dd>" +
      '<dt class="col-5">Comuna</dt><dd class="col-7">' + escapeHtml(sitio.comuna) + "</dd>" +
      '<dt class="col-5">Ciudad</dt><dd class="col-7">' + escapeHtml(sitio.ciudad) + "</dd>" +
      '<dt class="col-5">Departamento</dt><dd class="col-7">' + escapeHtml(sitio.departamento) + "</dd>" +
      '<dt class="col-5">Fecha</dt><dd class="col-7">' + escapeHtml(sitio.fecha) + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' + (Number(sitio.estado) === 1 ? "Activo" : "Inhabilitado") + "</dd>" +
      "</dl>";

    bootstrap.Modal.getOrCreateInstance(
      document.getElementById("sitioDetailModal")
    ).show();
  }

  async function cambiarEstado(id, estado) {
    var texto = estado === 1 ? "habilitar" : "inhabilitar";

    if (!window.confirm("¿Desea " + texto + " este sitio?")) return;

    try {
      var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=postEstado", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json"
        },
        body: JSON.stringify({
          id_sitio: Number(id),
          estado: Number(estado)
        })
      });

      var result = await response.json().catch(function () {
        return null;
      });

      if (!response.ok || !result || result.ok === false) {
        throw new Error((result && result.message) || "No se pudo cambiar el estado.");
      }

      showMessage(result.message, "success");
      await cargar();
    } catch (error) {
      showMessage(error.message, "danger");
    }
  }

  async function cargar() {
    tbody.innerHTML =
      '<tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr>';

    try {
      var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=lista");
      data = result.data || [];
      render();
    } catch (error) {
      tbody.innerHTML =
        '<tr><td colspan="6" class="text-center text-danger py-4">' +
        escapeHtml(error.message) +
        "</td></tr>";
    }
  }

  search.addEventListener("input", render);
  filtroEstado.addEventListener("change", render);

  tbody.addEventListener("click", function (event) {
    var button = event.target.closest("[data-action]");
    if (!button) return;

    var action = button.getAttribute("data-action");
    var id = button.getAttribute("data-id");

    if (action === "ver") {
      abrirDetalle(id);
    } else if (action === "estado") {
      cambiarEstado(id, Number(button.getAttribute("data-estado")));
    }
  });

  cargar();
})();
