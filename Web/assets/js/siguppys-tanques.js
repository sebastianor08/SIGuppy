/* =========================================================
   SIGuppys — Módulo Tanques
   =========================================================
   Listado de TODOS los tanques (de todos los zoocriaderos),
   con edición y habilitar/inhabilitar. La creación de tanques
   se sigue haciendo desde el módulo Zoocriadero.

   Web/ajax.php?modulo=Tanque&controlador=Tanque&funcion=...

   Endpoints usados:
     lista        -> GET   tanques + zoocriadero + tipo ya resueltos
     zoocriaderos -> GET   para el select "Zoocriadero" del modal
     tiposTanque  -> GET   para el select "Tipo de tanque" del modal
     postUpdate   -> POST  UPDATE en tanque
     postEstado   -> POST  UPDATE del campo estado (habilitar/inhabilitar)

   Un tanque inhabilitado deja de aparecer como opción al
   registrar un seguimiento de zoocriadero (el backend también
   lo bloquea aunque alguien intente forzarlo).
   ========================================================= */

(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "tanques") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Tanque&controlador=Tanque";

  var data = [];         // tanques traídos de la base
  var zoocriaderos = [];
  var tiposTanque = [];
  var state = { q: "", estado: "todos" };

  // ---------- Utilidades ----------
  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function permisos() {
    // Igual que en Zoocriaderos: por ahora todos los roles pueden
    // editar/inhabilitar (sin filtrar por rol todavía).
    return { editar: true, inhabilitar: true };
  }

  function showMessage(text, type) {
    var box = document.getElementById("tanquesMessage");
    if (!box) return;
    box.className = "alert mb-3 alert-" + type;
    box.textContent = text;
  }
  function clearMessage() {
    var box = document.getElementById("tanquesMessage");
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

  // PostgreSQL devuelve todo como texto: aquí se normaliza a números
  function normalizar(t) {
    return {
      id: Number(t.id_tanque),
      numero: Number(t.numero_tanque),
      estado: Number(t.estado),
      idZoocriadero: Number(t.id_zoocriadero),
      zoocriadero: t.zoocriadero || "",
      idTipoTanque: Number(t.id_tipo_tanque),
      tipoTanque: t.tipo_tanque || "",
    };
  }

  // ---------- Selects del modal ----------
  function fillZoocriaderosSelect(selectEl, seleccionado) {
    selectEl.innerHTML =
      '<option value="">Seleccione un zoocriadero</option>' +
      zoocriaderos
        .map(function (z) {
          var sel = Number(z.id_zoocriadero) === Number(seleccionado) ? " selected" : "";
          return '<option value="' + z.id_zoocriadero + '"' + sel + ">" + escapeHtml(z.nombre) + "</option>";
        })
        .join("");
  }

  function fillTiposTanqueSelect(selectEl, seleccionado) {
    selectEl.innerHTML =
      '<option value="">Seleccione el tipo</option>' +
      tiposTanque
        .map(function (t) {
          var sel = Number(t.id_tipo_tanque) === Number(seleccionado) ? " selected" : "";
          return '<option value="' + t.id_tipo_tanque + '"' + sel + ">" + escapeHtml(t.nombre) + "</option>";
        })
        .join("");
  }

  // ---------- Render ----------
  function renderAcciones(t) {
    var p = permisos();
    var btns = "";
    if (p.editar) {
      btns +=
        '<button type="button" class="btn-icon" data-action="editar" data-id="' +
        t.id + '" title="Editar"><i class="fas fa-pen"></i></button>';
    }
    if (p.inhabilitar) {
      if (t.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" data-action="inhabilitar" data-id="' +
          t.id + '" title="Inhabilitar"><i class="fas fa-ban"></i></button>';
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" data-action="habilitar" data-id="' +
          t.id + '" title="Habilitar"><i class="fas fa-check-circle"></i></button>';
      }
    }
    return '<div class="table-actions">' + btns + "</div>";
  }

  function renderRow(t) {
    var estadoBadge =
      t.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';
    return (
      "<tr>" +
      "<td>" + escapeHtml(t.zoocriadero) + "</td>" +
      "<td>Tanque " + t.numero + "</td>" +
      "<td>" + escapeHtml(t.tipoTanque) + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(t) + "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();
    return data.filter(function (t) {
      var texto = (t.zoocriadero + " " + t.tipoTanque + " " + t.numero).toLowerCase();
      var matchesQ = !q || texto.indexOf(q) !== -1;
      var matchesEstado =
        state.estado === "todos" ||
        (state.estado === "activo" && t.estado === 1) ||
        (state.estado === "inactivo" && t.estado === 0);
      return matchesQ && matchesEstado;
    });
  }

  function render() {
    var tbody = document.getElementById("tanquesTableBody");
    var rows = filteredData();
    if (!rows.length) {
      tbody.innerHTML =
        '<tr class="sig-empty-row"><td colspan="5"><i class="fas fa-folder-open mb-2 d-block" style="font-size:22px;color:#ccc;"></i>No hay tanques que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }
    var countEl = document.getElementById("tanquesCount");
    if (countEl) countEl.textContent = rows.length + " de " + data.length + " tanques";
  }

  // ---------- Modal Editar ----------
  var modalEl = document.getElementById("tanqueEditModal");
  var form = document.getElementById("tanqueEditForm");

  function openEditModal(id) {
    var t = data.find(function (x) { return x.id === id; });
    if (!t) return;

    form.elements["id_tanque"].value = t.id;
    form.elements["numero_tanque"].value = t.numero;
    fillZoocriaderosSelect(form.elements["id_zoocriadero"], t.idZoocriadero);
    fillTiposTanqueSelect(form.elements["id_tipo_tanque"], t.idTipoTanque);

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }

  async function handleSubmit(e) {
    e.preventDefault();
    if (!permisos().editar) return;

    var payload = {
      id_tanque: Number(form.elements["id_tanque"].value),
      id_zoocriadero: Number(form.elements["id_zoocriadero"].value),
      id_tipo_tanque: Number(form.elements["id_tipo_tanque"].value),
      numero_tanque: Number(form.elements["numero_tanque"].value),
    };
    if (!payload.id_zoocriadero) { alert("Debe seleccionar un zoocriadero."); return; }
    if (!payload.id_tipo_tanque) { alert("Debe seleccionar el tipo de tanque."); return; }
    if (!payload.numero_tanque || payload.numero_tanque < 1) {
      alert("El número de tanque debe ser un entero mayor que cero.");
      return;
    }

    try {
      var res = await postJson("postUpdate", payload);
      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      await recargar();
      showMessage(res.message, "success");
    } catch (error) {
      alert(error.message);
    }
  }

  async function toggleEstado(id, nuevoEstado) {
    if (!permisos().inhabilitar) return;
    var t = data.find(function (x) { return x.id === id; });
    if (!t) return;

    var accion = nuevoEstado === 1 ? "habilitar" : "inhabilitar";
    var advertencia =
      nuevoEstado === 0
        ? " Mientras esté inhabilitado no se podrán registrar seguimientos para este tanque."
        : "";
    if (!confirm("¿Seguro que deseas " + accion + " el Tanque " + t.numero + " de " + t.zoocriadero + "?" + advertencia)) return;

    try {
      var res = await postJson("postEstado", { id_tanque: id, estado: nuevoEstado });
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

  // ---------- Eventos ----------
  document.getElementById("tanquesTableBody").addEventListener("click", function (e) {
    var btn = e.target.closest("[data-action]");
    if (!btn) return;
    var id = Number(btn.getAttribute("data-id"));
    var action = btn.getAttribute("data-action");
    if (action === "editar" && permisos().editar) openEditModal(id);
    if (action === "inhabilitar") toggleEstado(id, 0);
    if (action === "habilitar") toggleEstado(id, 1);
  });

  form.addEventListener("submit", handleSubmit);

  document.getElementById("tanquesSearch").addEventListener("input", function () {
    state.q = this.value;
    render();
  });
  document.getElementById("tanquesEstadoFiltro").addEventListener("change", function () {
    state.estado = this.value;
    render();
  });

  document.addEventListener("siguppys:role-changed", function () {
    render();
  });

  // ---------- Arranque ----------
  (async function init() {
    document.getElementById("tanquesTableBody").innerHTML =
      '<tr class="sig-empty-row"><td colspan="5">Cargando tanques...</td></tr>';
    try {
      var resultados = await Promise.all([
        getJson("lista"),
        getJson("zoocriaderos"),
        getJson("tiposTanque"),
      ]);
      data = resultados[0].map(normalizar);
      zoocriaderos = resultados[1];
      tiposTanque = resultados[2];
      clearMessage();
      render();
    } catch (error) {
      document.getElementById("tanquesTableBody").innerHTML =
        '<tr class="sig-empty-row"><td colspan="5">No se pudieron cargar los datos.</td></tr>';
      showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
    }
  })();
})();
