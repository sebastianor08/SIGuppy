/* =========================================================
   SIGuppys — Módulo Zoocriaderos
   =========================================================
   Los datos YA NO están quemados en este archivo: se leen de
   PostgreSQL a través del router MVC:

     Web/ajax.php?modulo=Zoocriadero&controlador=Zoocriadero&funcion=...

   Endpoints usados:
     lista        -> GET   zoocriaderos + persona a cargo + nº de tanques
     comunas      -> GET   para el select "Comuna"
     barrios      -> GET   barrios de la comuna elegida
     tiposTanque  -> GET   para el select "Tipo de tanque"
     tanques      -> GET   tanques de un zoocriadero (modal detalle)
     postCreate   -> POST  INSERT en zoocriadero
     postUpdate   -> POST  UPDATE en zoocriadero
     postEstado   -> POST  UPDATE del campo estado (habilitar/inhabilitar)
     postTanque   -> POST  INSERT en tanque

   El diseño de la tabla, los filtros y los permisos por rol
   quedaron igual que antes.
   ========================================================= */
(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "zoocriaderos") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Zoocriadero&controlador=Zoocriadero";

  var PERMISOS = {
    auxiliar: { crear: false, editar: false, inhabilitar: false },
    coordinador: { crear: true, editar: true, inhabilitar: true },
  };

  var data = [];      // zoocriaderos traídos de la base
  var comunas = [];   // comunas de Cali
  var tiposTanque = [];
  var state = { q: "", estado: "todos" };

  // ---------- Utilidades ----------
  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function fmtFecha(iso) {
    if (!iso) return "";
    var d = new Date(iso + "T00:00:00");
    if (isNaN(d)) return iso;
    return d.toLocaleDateString("es-CO", { day: "2-digit", month: "short", year: "numeric" });
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
    var box = document.getElementById("zoocriaderosMessage");
    if (!box) return;
    box.className = "alert mb-3 alert-" + type;
    box.textContent = text;
  }
  function clearMessage() {
    var box = document.getElementById("zoocriaderosMessage");
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
  function normalizar(z) {
    return {
      id: Number(z.id_zoocriadero),
      nombre: z.nombre || "",
      direccion: z.direccion || "",
      comuna: z.comuna || "",
      barrio: z.barrio || "",
      latitud: z.latitud,
      longitud: z.longitud,
      estado: Number(z.estado),
      creado_en: z.creado_en || "",
      total_tanques: Number(z.total_tanques || 0),
    };
  }

  // ---------- Selects ----------
  function fillComunasSelect(selectEl, seleccionada) {
    selectEl.innerHTML =
      '<option value="">Seleccione la comuna</option>' +
      comunas
        .map(function (c) {
          var sel = c.nombre === seleccionada ? " selected" : "";
          return '<option value="' + c.id_comuna + '"' + sel + ">" + escapeHtml(c.nombre) + "</option>";
        })
        .join("");
  }

  // El select de barrios depende de la comuna: se pide al backend
  // solo los barrios de esa comuna.
  async function cargarBarrios(idComuna, seleccionado) {
    var barrioSelect = document.getElementById("barrioSelect");
    barrioSelect.disabled = true;

    if (!idComuna) {
      barrioSelect.innerHTML = '<option value="">Seleccione primero la comuna</option>';
      return;
    }

    barrioSelect.innerHTML = '<option value="">Cargando barrios...</option>';
    var barrios = await getJson("barrios", "&id_comuna=" + encodeURIComponent(idComuna));

    barrioSelect.innerHTML = barrios.length
      ? '<option value="">Seleccione el barrio</option>' +
        barrios
          .map(function (b) {
            var sel = b.nombre === seleccionado ? " selected" : "";
            return '<option value="' + escapeHtml(b.nombre) + '"' + sel + ">" + escapeHtml(b.nombre) + "</option>";
          })
          .join("")
      : '<option value="">Esa comuna no tiene barrios registrados</option>';
    barrioSelect.disabled = barrios.length === 0;
  }

  function fillTiposTanqueSelect(selectEl) {
    selectEl.innerHTML =
      '<option value="">Seleccione el tipo</option>' +
      tiposTanque
        .map(function (t) {
          return '<option value="' + t.id_tipo_tanque + '">' + escapeHtml(t.nombre) + "</option>";
        })
        .join("");
  }

  function fillZoocriaderosSelect(selectEl) {
    var activos = data.filter(function (z) { return z.estado === 1; });
    selectEl.innerHTML =
      '<option value="">Seleccione un zoocriadero</option>' +
      activos
        .map(function (z) {
          return '<option value="' + z.id + '">' + escapeHtml(z.nombre) + "</option>";
        })
        .join("");
  }

  // ---------- Render ----------
  function renderAcciones(z) {
    var p = permisos();
    var btns =
      '<button type="button" class="btn-icon" data-action="ver" data-id="' +
      z.id + '" title="Ver detalle"><i class="fas fa-eye"></i></button>';

    if (p.editar) {
      btns +=
        '<button type="button" class="btn-icon" data-action="editar" data-id="' +
        z.id + '" title="Editar"><i class="fas fa-pen"></i></button>';
    }
    if (p.inhabilitar) {
      if (z.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" data-action="inhabilitar" data-id="' +
          z.id + '" title="Inhabilitar"><i class="fas fa-ban"></i></button>';
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" data-action="habilitar" data-id="' +
          z.id + '" title="Habilitar"><i class="fas fa-check-circle"></i></button>';
      }
    }
    return '<div class="table-actions">' + btns + "</div>";
  }

  function renderRow(z) {
    var estadoBadge =
      z.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';
    return (
      "<tr>" +
      '<td><span class="fw-bold">' + escapeHtml(z.nombre) + "</span>" +
      '<div class="small text-muted">Registrado ' + fmtFecha(z.creado_en) + "</div></td>" +
      "<td>" + escapeHtml(z.direccion) +
      '<div class="small text-muted">' + escapeHtml(z.barrio) + " · " + escapeHtml(z.comuna) + "</div></td>" +
      '<td class="text-center">' + z.total_tanques + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(z) + "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();
    return data.filter(function (z) {
      var texto = (z.nombre + " " + z.direccion + " " + z.barrio + " " + z.comuna).toLowerCase();
      var matchesQ = !q || texto.indexOf(q) !== -1;
      var matchesEstado =
        state.estado === "todos" ||
        (state.estado === "activo" && z.estado === 1) ||
        (state.estado === "inactivo" && z.estado === 0);
      return matchesQ && matchesEstado;
    });
  }

  function render() {
    var tbody = document.getElementById("zoocriaderosTableBody");
    var rows = filteredData();
    if (!rows.length) {
      tbody.innerHTML =
        '<tr class="sig-empty-row"><td colspan="5"><i class="fas fa-folder-open mb-2 d-block" style="font-size:22px;color:#ccc;"></i>No hay zoocriaderos que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }
    renderRegistrarBtn();
    renderRegistrarTanqueBtn();
    var countEl = document.getElementById("zoocriaderosCount");
    if (countEl) countEl.textContent = rows.length + " de " + data.length + " zoocriaderos";
  }

  function renderRegistrarBtn() {
    var wrap = document.getElementById("registrarZoocriaderoWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#zoocriaderoModal" id="btnAbrirRegistrar">' +
        '<i class="fas fa-plus me-1"></i> Registrar Zoocriadero</button>';
    } else {
      wrap.innerHTML =
        '<button type="button" class="btn btn-round btn-locked" disabled title="' +
        escapeHtml(lockedTitle("registrar zoocriaderos")) + '">' +
        '<i class="fas fa-lock me-1"></i> Registrar Zoocriadero</button>';
    }
  }

  function renderRegistrarTanqueBtn() {
    var wrap = document.getElementById("registrarTanqueWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-outline-primary btn-round" data-bs-toggle="modal" data-bs-target="#tanqueModal" id="btnAbrirRegistrarTanque">' +
        '<i class="fas fa-vial me-1"></i> Registrar Tanque</button>';
    } else {
      wrap.innerHTML =
        '<button type="button" class="btn btn-round btn-locked" disabled title="' +
        escapeHtml(lockedTitle("registrar tanques")) + '">' +
        '<i class="fas fa-lock me-1"></i> Registrar Tanque</button>';
    }
  }

  // ---------- Modal Registrar / Editar ----------
  var modalEl = document.getElementById("zoocriaderoModal");
  var form = document.getElementById("zoocriaderoForm");

  function openCreateModal() {
    form.reset();
    form.elements["id"].value = "";
    document.getElementById("zoocriaderoModalLabel").textContent = "Registrar Zoocriadero";
    document.getElementById("zoocriaderoSubmitBtn").textContent = "Guardar Registro";
    fillComunasSelect(document.getElementById("comunaSelect"), null);
    cargarBarrios(null, null);
  }

  function openEditModal(id) {
    var z = data.find(function (x) { return x.id === id; });
    if (!z) return;
    document.getElementById("zoocriaderoModalLabel").textContent = "Editar Zoocriadero";
    document.getElementById("zoocriaderoSubmitBtn").textContent = "Guardar Cambios";
    form.elements["id"].value = z.id;
    form.elements["nombre"].value = z.nombre;
    form.elements["direccion"].value = z.direccion;
    form.elements["latitud"].value = z.latitud || "";
    form.elements["longitud"].value = z.longitud || "";

    var comunaSelect = document.getElementById("comunaSelect");
    fillComunasSelect(comunaSelect, z.comuna);
    cargarBarrios(comunaSelect.value, z.barrio);

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }

  async function openDetailModal(id) {
    var z = data.find(function (x) { return x.id === id; });
    if (!z) return;

    var body = document.getElementById("zoocriaderoDetailBody");
    body.innerHTML = '<p class="text-muted mb-0">Cargando tanques...</p>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById("zoocriaderoDetailModal")).show();

    var tanquesZoo = [];
    try {
      tanquesZoo = await getJson("tanques", "&id_zoocriadero=" + encodeURIComponent(z.id));
    } catch (e) {
      tanquesZoo = [];
    }

    body.innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Nombre</dt><dd class="col-7">' + escapeHtml(z.nombre) + "</dd>" +
      '<dt class="col-5">Dirección</dt><dd class="col-7">' + escapeHtml(z.direccion) + "</dd>" +
      '<dt class="col-5">Comuna</dt><dd class="col-7">' + escapeHtml(z.comuna) + "</dd>" +
      '<dt class="col-5">Barrio</dt><dd class="col-7">' + escapeHtml(z.barrio) + "</dd>" +
      '<dt class="col-5">Tanques activos</dt><dd class="col-7">' + z.total_tanques + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' +
      (z.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>') +
      "</dd>" +
      '<dt class="col-5">Registrado</dt><dd class="col-7">' + fmtFecha(z.creado_en) + "</dd>" +
      "</dl>" +
      '<hr class="my-3" />' +
      '<h6 class="fw-bold mb-2">Tanques registrados</h6>' +
      (tanquesZoo.length
        ? '<ul class="list-group list-group-flush">' +
          tanquesZoo
            .map(function (t) {
              return (
                '<li class="list-group-item px-0 d-flex justify-content-between align-items-center">' +
                '<span><span class="fw-bold">Tanque ' + escapeHtml(t.numero_tanque) + "</span>" +
                '<div class="small text-muted">' + escapeHtml(t.tipo_tanque) + "</div></span>" +
                (Number(t.estado) === 1
                  ? '<span class="badge-estado activo">Activo</span>'
                  : '<span class="badge-estado inactivo">Inhabilitado</span>') +
                "</li>"
              );
            })
            .join("") +
          "</ul>"
        : '<p class="small text-muted mb-0">Este zoocriadero todavía no tiene tanques registrados.</p>');
  }

  // ---------- Modal Registrar Tanque ----------
  var tanqueModalEl = document.getElementById("tanqueModal");
  var tanqueForm = document.getElementById("tanqueForm");

  function openCreateTanqueModal() {
    tanqueForm.reset();
    fillZoocriaderosSelect(tanqueForm.elements["id_zoocriadero"]);
    fillTiposTanqueSelect(tanqueForm.elements["id_tipo_tanque"]);
  }

  async function handleTanqueSubmit(e) {
    e.preventDefault();
    if (!permisos().crear) return;

    var payload = {
      id_zoocriadero: Number(tanqueForm.elements["id_zoocriadero"].value),
      id_tipo_tanque: Number(tanqueForm.elements["id_tipo_tanque"].value),
      numero_tanque: Number(tanqueForm.elements["numero_tanque"].value),
    };
    // Validación en el navegador (el servidor la vuelve a hacer)
    if (!payload.id_zoocriadero) { alert("Debe seleccionar un zoocriadero."); return; }
    if (!payload.numero_tanque || payload.numero_tanque < 1) {
      alert("El número de tanque debe ser un entero mayor que cero.");
      return;
    }
    if (!payload.id_tipo_tanque) { alert("Debe seleccionar el tipo de tanque."); return; }

    try {
      var res = await postJson("postTanque", payload); // INSERT en tanque
      bootstrap.Modal.getOrCreateInstance(tanqueModalEl).hide();
      await recargar();
      showMessage(res.message, "success");
    } catch (error) {
      alert(error.message);
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    if (!permisos().crear) return;

    var id = form.elements["id"].value;
    var payload = {
      nombre: form.elements["nombre"].value.trim(),
      direccion: form.elements["direccion"].value.trim(),
      comuna: document.getElementById("comunaSelect").selectedOptions[0]
        ? document.getElementById("comunaSelect").selectedOptions[0].textContent.trim()
        : "",
      barrio: document.getElementById("barrioSelect").value,
      latitud: form.elements["latitud"].value,
      longitud: form.elements["longitud"].value,
    };
    // Un campo escrito solo con espacios queda vacío tras el .trim() de arriba
    if (payload.nombre.length < 3) {
      alert("El nombre es obligatorio y debe tener al menos 3 caracteres (no solo espacios).");
      form.elements["nombre"].focus();
      return;
    }
    if (payload.direccion.length < 5) {
      alert("La dirección es obligatoria y debe tener al menos 5 caracteres (no solo espacios).");
      form.elements["direccion"].focus();
      return;
    }

    try {
      var res;
      if (id) {
        payload.id_zoocriadero = Number(id);
        res = await postJson("postUpdate", payload); // UPDATE
      } else {
        res = await postJson("postCreate", payload); // INSERT
      }
      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      await recargar();
      showMessage(res.message, "success");
    } catch (error) {
      alert(error.message);
    }
  }

  async function toggleEstado(id, nuevoEstado) {
    if (!permisos().inhabilitar) return;
    var z = data.find(function (x) { return x.id === id; });
    if (!z) return;

    var accion = nuevoEstado === 1 ? "habilitar" : "inhabilitar";
    if (!confirm("¿Seguro que deseas " + accion + ' "' + z.nombre + '"?')) return;

    try {
      var res = await postJson("postEstado", { id_zoocriadero: id, estado: nuevoEstado });
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
  document.getElementById("zoocriaderosTableBody").addEventListener("click", function (e) {
    var btn = e.target.closest("[data-action]");
    if (!btn) return;
    var id = Number(btn.getAttribute("data-id"));
    var action = btn.getAttribute("data-action");
    if (action === "ver") openDetailModal(id);
    if (action === "editar" && permisos().editar) openEditModal(id);
    if (action === "inhabilitar") toggleEstado(id, 0);
    if (action === "habilitar") toggleEstado(id, 1);
  });

  document.getElementById("registrarZoocriaderoWrap").addEventListener("click", function (e) {
    if (e.target.closest("#btnAbrirRegistrar")) openCreateModal();
  });

  document.getElementById("registrarTanqueWrap").addEventListener("click", function (e) {
    if (e.target.closest("#btnAbrirRegistrarTanque")) openCreateTanqueModal();
  });

  document.getElementById("comunaSelect").addEventListener("change", function () {
    cargarBarrios(this.value, null).catch(function (error) {
      showMessage(error.message, "danger");
    });
  });

  form.addEventListener("submit", handleSubmit);
  tanqueForm.addEventListener("submit", handleTanqueSubmit);

  document.getElementById("zoocriaderosSearch").addEventListener("input", function () {
    state.q = this.value;
    render();
  });
  document.getElementById("zoocriaderosEstadoFiltro").addEventListener("change", function () {
    state.estado = this.value;
    render();
  });

  document.addEventListener("siguppys:role-changed", function () {
    if (!permisos().crear && modalEl.classList.contains("show")) {
      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    }
    if (!permisos().crear && tanqueModalEl.classList.contains("show")) {
      bootstrap.Modal.getOrCreateInstance(tanqueModalEl).hide();
    }
    render();
  });

  // ---------- Arranque ----------
  (async function init() {
    document.getElementById("zoocriaderosTableBody").innerHTML =
      '<tr class="sig-empty-row"><td colspan="5">Cargando zoocriaderos...</td></tr>';
    try {
      var resultados = await Promise.all([
        getJson("lista"),
        getJson("comunas"),
        getJson("tiposTanque"),
      ]);
      data = resultados[0].map(normalizar);
      comunas = resultados[1];
      tiposTanque = resultados[2];
      clearMessage();
      render();
    } catch (error) {
      document.getElementById("zoocriaderosTableBody").innerHTML =
        '<tr class="sig-empty-row"><td colspan="5">No se pudieron cargar los datos.</td></tr>';
      showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
    }
  })();
})();
