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

  // Permisos reales del rol de la sesión sobre este módulo (ver
  // lib/permisos.php / View/partials/footer.php), en vez del selector de
  // rol de mentira que se usaba antes (auxiliar/coordinador).
  var PERMISOS_VACIOS = { ver: false, consultar: false, crear: false, editar: false, inhabilitar: false, exportar: false };

  function permisos() {
    return window.SIG_PERMISOS || PERMISOS_VACIOS;
  }
  function lockedTitle(accion) {
    return "No tienes permiso para " + accion + ".";
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
      nombre: t.nombre_tanque || "",
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
      "<td>" + escapeHtml(t.nombre) + "</td>" +
      "<td>" + escapeHtml(t.tipoTanque) + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(t) + "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();
    return data.filter(function (t) {
      var texto = (t.zoocriadero + " " + t.tipoTanque + " " + t.nombre).toLowerCase();
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
    renderRegistrarBtn();
  }

  function renderRegistrarBtn() {
    var wrap = document.getElementById("registrarTanqueWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#tanqueCreateModal" id="btnAbrirRegistrarTanque">' +
        '<i class="fas fa-plus me-1"></i> Registrar Tanque</button>';
    } else {
      wrap.innerHTML =
        '<button type="button" class="btn btn-round btn-locked" disabled title="' +
        escapeHtml(lockedTitle("registrar tanques")) + '">' +
        '<i class="fas fa-lock me-1"></i> Registrar Tanque</button>';
    }
  }

  // ---------- Modal Registrar ----------
  var createModalEl = document.getElementById("tanqueCreateModal");
  var createForm = document.getElementById("tanqueCreateForm");
  var createNumeroHint = document.getElementById("tanqueCreateNumeroHint");

  // Chequeo en vivo contra los tanques ya cargados (mismo listado que
  // alimenta la tabla): si el zoocriadero elegido ya tiene un tanque con
  // ese nombre (sin importar mayúsculas/espacios), se avisa antes de
  // intentar guardar, en vez de esperar el error del servidor.
  function nombreTanqueRepetido(idZoocriadero, nombre, idExcluir) {
    var normalizado = nombre.trim().replace(/\s+/g, " ").toLowerCase();
    if (!normalizado) return false;
    return data.some(function (t) {
      return (
        t.idZoocriadero === idZoocriadero &&
        t.nombre.trim().replace(/\s+/g, " ").toLowerCase() === normalizado &&
        t.id !== idExcluir
      );
    });
  }

  function actualizarHintNumeroCreate() {
    var idZoo = Number(createForm.elements["id_zoocriadero"].value);
    var nombre = createForm.elements["nombre_tanque"].value;

    if (!idZoo) {
      createNumeroHint.textContent = "Seleccione primero el zoocriadero.";
      createNumeroHint.className = "form-text";
      return;
    }
    if (nombre.trim() && nombreTanqueRepetido(idZoo, nombre, null)) {
      createNumeroHint.textContent =
        'Ese zoocriadero ya tiene un tanque llamado "' + nombre.trim() + '". Cambia el nombre.';
      createNumeroHint.className = "form-text text-danger fw-bold";
      return;
    }
    createNumeroHint.textContent = "Nombre disponible para este zoocriadero.";
    createNumeroHint.className = "form-text text-success";
  }

  function openCreateModal() {
    createForm.reset();
    fillZoocriaderosSelect(createForm.elements["id_zoocriadero"], null);
    fillTiposTanqueSelect(createForm.elements["id_tipo_tanque"], null);
    actualizarHintNumeroCreate();
  }

  createForm.elements["id_zoocriadero"].addEventListener("change", actualizarHintNumeroCreate);
  createForm.elements["nombre_tanque"].addEventListener("input", actualizarHintNumeroCreate);

  async function handleCreateSubmit(e) {
    e.preventDefault();
    if (!permisos().crear) return;

    var payload = {
      id_zoocriadero: Number(createForm.elements["id_zoocriadero"].value),
      id_tipo_tanque: Number(createForm.elements["id_tipo_tanque"].value),
      nombre_tanque: createForm.elements["nombre_tanque"].value.trim(),
    };
    if (!payload.id_zoocriadero) { alert("Debe seleccionar un zoocriadero."); return; }
    if (!payload.nombre_tanque || payload.nombre_tanque.length < 2) {
      alert("El nombre del tanque debe tener al menos 2 caracteres.");
      return;
    }
    if (!payload.id_tipo_tanque) { alert("Debe seleccionar el tipo de tanque."); return; }
    if (nombreTanqueRepetido(payload.id_zoocriadero, payload.nombre_tanque, null)) {
      alert('Ese zoocriadero ya tiene un tanque llamado "' + payload.nombre_tanque + '". Cambia el nombre antes de guardar.');
      createForm.elements["nombre_tanque"].focus();
      return;
    }

    try {
      var res = await postJson("postCreate", payload);
      bootstrap.Modal.getOrCreateInstance(createModalEl).hide();
      await recargar();
      showMessage(res.message, "success");
    } catch (error) {
      alert(error.message);
    }
  }

  createForm.addEventListener("submit", handleCreateSubmit);

  document.getElementById("registrarTanqueWrap").addEventListener("click", function (e) {
    if (e.target.closest("#btnAbrirRegistrarTanque")) openCreateModal();
  });

  // ---------- Modal Editar ----------
  var modalEl = document.getElementById("tanqueEditModal");
  var form = document.getElementById("tanqueEditForm");

  function openEditModal(id) {
    var t = data.find(function (x) { return x.id === id; });
    if (!t) return;

    form.elements["id_tanque"].value = t.id;
    form.elements["nombre_tanque"].value = t.nombre;
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
      nombre_tanque: form.elements["nombre_tanque"].value.trim(),
    };
    if (!payload.id_zoocriadero) { alert("Debe seleccionar un zoocriadero."); return; }
    if (!payload.id_tipo_tanque) { alert("Debe seleccionar el tipo de tanque."); return; }
    if (!payload.nombre_tanque || payload.nombre_tanque.length < 2) {
      alert("El nombre del tanque debe tener al menos 2 caracteres.");
      return;
    }
    if (nombreTanqueRepetido(payload.id_zoocriadero, payload.nombre_tanque, payload.id_tanque)) {
      alert('Ese zoocriadero ya tiene un tanque llamado "' + payload.nombre_tanque + '".');
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
    if (!confirm("¿Seguro que deseas " + accion + " el tanque \"" + t.nombre + "\" de " + t.zoocriadero + "?" + advertencia)) return;

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
    if (!permisos().crear && createModalEl.classList.contains("show")) {
      bootstrap.Modal.getOrCreateInstance(createModalEl).hide();
    }
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