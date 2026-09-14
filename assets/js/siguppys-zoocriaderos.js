/* =========================================================
   SIGuppys — Módulo Zoocriaderos
   =========================================================
   Estructura de datos alineada a las tablas `zoocriadero` y
   `usuario` de dengueprueba.sql:
     zoocriadero(id_zoocriadero, nombre, direccion, comuna, barrio,
                 id_persona_cargo -> usuario, estado, creado_en)

   El tanque se maneja aquí de forma simplificada (nombre +
   descripción, asociado a un zoocriadero) para que el registro
   quede bien acoplado con el seguimiento. Nota: la tabla `tanque`
   real de dengueprueba.sql usa numero_tanque + id_tipo_tanque; si
   se conecta este módulo a ese esquema hay que decidir si se migra
   la tabla a nombre/descripcion o se mapea nombre -> numero_tanque.

   Como todavía no hay API conectada, ambas listas viven en
   localStorage para poder demostrar registrar / editar /
   inhabilitar. El día que exista backend, reemplazar las
   funciones marcadas "// TODO API" por llamadas fetch() reales;
   el resto (render, filtros, permisos) puede quedar igual.

   Permisos por rol (según rol_permiso / accion_permiso):
     - coordinador -> puede VER, REGISTRAR/EDITAR zoocriaderos y
                      tanques, e INHABILITAR/HABILITAR
     - auxiliar    -> solo puede VER
   ========================================================= */
(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "zoocriaderos") return;

  var STORAGE_KEY = "siguppys_zoocriaderos_demo";
  var TANQUES_STORAGE_KEY = "siguppys_tanques_demo";

  var PERMISOS = {
    auxiliar: { crear: false, editar: false, inhabilitar: false },
    coordinador: { crear: true, editar: true, inhabilitar: true },
  };

  var USUARIOS = [
    "Luisa Fernanda Ríos",
    "Carlos Andrés Mosquera",
    "Diana Marcela Ortiz",
    "Jhon Édison Valencia",
    "María José Perlaza",
  ];

  var SEED = [
    {
      id: 1,
      nombre: "Zoocriadero Central",
      direccion: "Calle 13 # 24-05",
      comuna: "Comuna 10",
      barrio: "Guabal",
      persona_cargo: "Luisa Fernanda Ríos",
      estado: 1,
      creado_en: "2026-02-11",
    },
    {
      id: 2,
      nombre: "Zoocriadero Norte",
      direccion: "Cra 8 # 45-12",
      comuna: "Comuna 2",
      barrio: "Granada",
      persona_cargo: "Carlos Andrés Mosquera",
      estado: 1,
      creado_en: "2026-03-02",
    },
    {
      id: 3,
      nombre: "Zoocriadero Oriente",
      direccion: "Calle 70 # 28D-19",
      comuna: "Comuna 13",
      barrio: "El Retiro",
      persona_cargo: "Diana Marcela Ortiz",
      estado: 1,
      creado_en: "2026-04-18",
    },
    {
      id: 4,
      nombre: "Zoocriadero Ladera",
      direccion: "Cra 26 # 9-40",
      comuna: "Comuna 18",
      barrio: "Meléndez",
      persona_cargo: "Jhon Édison Valencia",
      estado: 0,
      creado_en: "2025-11-05",
    },
    {
      id: 5,
      nombre: "Zoocriadero Aguablanca",
      direccion: "Cra 31 # 22-71",
      comuna: "Comuna 15",
      barrio: "Mojica",
      persona_cargo: "María José Perlaza",
      estado: 1,
      creado_en: "2026-01-27",
    },
  ];

  function loadData() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (raw) return JSON.parse(raw);
    } catch (e) {}
    return SEED.slice();
  }

  function saveData(data) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch (e) {}
  }

  var data = loadData(); // TODO API: reemplazar por GET /api/zoocriaderos

  // ---- Tanques: nombre + descripción, asociados a un zoocriadero ----
  var TANQUES_SEED = [
    { id: 1, id_zoocriadero: 1, nombre: "Tanque 1", descripcion: "" },
    { id: 2, id_zoocriadero: 1, nombre: "Tanque 2", descripcion: "" },
    { id: 3, id_zoocriadero: 1, nombre: "Tanque 3", descripcion: "" },
    { id: 4, id_zoocriadero: 1, nombre: "Tanque 4", descripcion: "" },
    { id: 5, id_zoocriadero: 2, nombre: "Tanque 1", descripcion: "" },
    { id: 6, id_zoocriadero: 2, nombre: "Tanque 2", descripcion: "" },
    { id: 7, id_zoocriadero: 2, nombre: "Tanque 3", descripcion: "" },
    { id: 8, id_zoocriadero: 3, nombre: "Tanque 1", descripcion: "" },
    { id: 9, id_zoocriadero: 3, nombre: "Tanque 2", descripcion: "" },
    { id: 10, id_zoocriadero: 4, nombre: "Tanque 1", descripcion: "" },
    { id: 11, id_zoocriadero: 4, nombre: "Tanque 2", descripcion: "" },
    { id: 12, id_zoocriadero: 4, nombre: "Tanque 3", descripcion: "" },
    { id: 13, id_zoocriadero: 4, nombre: "Tanque 4", descripcion: "" },
    { id: 14, id_zoocriadero: 4, nombre: "Tanque 5", descripcion: "" },
    { id: 15, id_zoocriadero: 5, nombre: "Tanque 1", descripcion: "" },
    { id: 16, id_zoocriadero: 5, nombre: "Tanque 2", descripcion: "" },
    { id: 17, id_zoocriadero: 5, nombre: "Tanque 3", descripcion: "" },
  ];

  function loadTanques() {
    try {
      var raw = localStorage.getItem(TANQUES_STORAGE_KEY);
      if (raw) return JSON.parse(raw);
    } catch (e) {}
    return TANQUES_SEED.slice();
  }

  function saveTanques(tanques) {
    try {
      localStorage.setItem(TANQUES_STORAGE_KEY, JSON.stringify(tanques));
    } catch (e) {}
  }

  var tanques = loadTanques(); // TODO API: reemplazar por GET /api/tanques

  function tanquesDe(idZoocriadero) {
    return tanques.filter(function (t) { return t.id_zoocriadero === idZoocriadero; });
  }
  function nextTanqueId() {
    return tanques.reduce(function (max, t) { return Math.max(max, t.id); }, 0) + 1;
  }

  // ---- Estado de la UI (filtros) ----
  var state = { q: "", estado: "todos" };

  // ---- Helpers ----
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
  function nextId() {
    return data.reduce(function (max, z) { return Math.max(max, z.id); }, 0) + 1;
  }
  function fmtFecha(iso) {
    var d = new Date(iso + "T00:00:00");
    if (isNaN(d)) return iso;
    return d.toLocaleDateString("es-CO", { day: "2-digit", month: "short", year: "numeric" });
  }
  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  // ---- Opciones del select "Persona a cargo" ----
  function fillUsuariosSelect(selectEl, selected) {
    selectEl.innerHTML = USUARIOS.map(function (u) {
      return '<option value="' + escapeHtml(u) + '"' + (u === selected ? " selected" : "") + ">" + escapeHtml(u) + "</option>";
    }).join("");
  }

  // ---- Opciones del select "Zoocriadero" (modal de Tanque) ----
  // Solo zoocriaderos activos, igual que zoocriaderosActivos() del backend real.
  function fillZoocriaderosSelect(selectEl, selected) {
    var activos = data.filter(function (z) { return z.estado === 1; });
    selectEl.innerHTML = activos.map(function (z) {
      return '<option value="' + z.id + '"' + (z.id === selected ? " selected" : "") + ">" + escapeHtml(z.nombre) + "</option>";
    }).join("");
  }

  // ---- Render de la fila de acciones según permiso ----
  function renderAcciones(z) {
    var p = permisos();
    var btns = "";
    btns +=
      '<button type="button" class="btn-icon" data-action="ver" data-id="' +
      z.id +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>';

    if (p.editar) {
      btns +=
        '<button type="button" class="btn-icon" data-action="editar" data-id="' +
        z.id +
        '" title="Editar"><i class="fas fa-pen"></i></button>';
    }
    if (p.inhabilitar) {
      if (z.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" data-action="inhabilitar" data-id="' +
          z.id +
          '" title="Inhabilitar"><i class="fas fa-ban"></i></button>';
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" data-action="habilitar" data-id="' +
          z.id +
          '" title="Habilitar"><i class="fas fa-check-circle"></i></button>';
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
      "<td>" + escapeHtml(z.persona_cargo) + "</td>" +
      '<td class="text-center">' + tanquesDe(z.id).length + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(z) + "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();
    return data.filter(function (z) {
      var matchesQ =
        !q ||
        z.nombre.toLowerCase().indexOf(q) !== -1 ||
        z.direccion.toLowerCase().indexOf(q) !== -1 ||
        z.barrio.toLowerCase().indexOf(q) !== -1 ||
        z.comuna.toLowerCase().indexOf(q) !== -1 ||
        z.persona_cargo.toLowerCase().indexOf(q) !== -1;
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
        '<tr class="sig-empty-row"><td colspan="6"><i class="fas fa-folder-open mb-2 d-block" style="font-size:22px;color:#ccc;"></i>No hay zoocriaderos que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }
    renderRegistrarBtn();
    renderRegistrarTanqueBtn();
    var countEl = document.getElementById("zoocriaderosCount");
    if (countEl) countEl.textContent = rows.length + " de " + data.length + " zoocriaderos";
  }

  // ---- Botón "Registrar Zoocriadero" según permiso ----
  function renderRegistrarBtn() {
    var wrap = document.getElementById("registrarZoocriaderoWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#zoocriaderoModal" id="btnAbrirRegistrar">' +
        '<i class="fas fa-plus me-1"></i> Registrar Zoocriadero</button>';
    } else {
      wrap.innerHTML =
        '<button type="button" class="btn btn-round btn-locked" disabled ' +
        'title="' + escapeHtml(lockedTitle("registrar zoocriaderos")) + '">' +
        '<i class="fas fa-lock me-1"></i> Registrar Zoocriadero</button>';
    }
  }

  // ---- Botón "Registrar Tanque" según permiso (mismo permiso que crear zoocriadero) ----
  function renderRegistrarTanqueBtn() {
    var wrap = document.getElementById("registrarTanqueWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-outline-primary btn-round" data-bs-toggle="modal" data-bs-target="#tanqueModal" id="btnAbrirRegistrarTanque">' +
        '<i class="fas fa-vial me-1"></i> Registrar Tanque</button>';
    } else {
      wrap.innerHTML =
        '<button type="button" class="btn btn-round btn-locked" disabled ' +
        'title="' + escapeHtml(lockedTitle("registrar tanques")) + '">' +
        '<i class="fas fa-lock me-1"></i> Registrar Tanque</button>';
    }
  }

  // ---- Modal Registrar/Editar ----
  var modalEl = document.getElementById("zoocriaderoModal");
  var form = document.getElementById("zoocriaderoForm");

  function openCreateModal() {
    form.reset();
    form.elements["id"].value = "";
    document.getElementById("zoocriaderoModalLabel").textContent = "Registrar Zoocriadero";
    document.getElementById("zoocriaderoSubmitBtn").textContent = "Guardar Registro";
    fillUsuariosSelect(form.elements["persona_cargo"], null);
  }

  function openEditModal(id) {
    var z = data.find(function (x) { return x.id === id; });
    if (!z) return;
    document.getElementById("zoocriaderoModalLabel").textContent = "Editar Zoocriadero";
    document.getElementById("zoocriaderoSubmitBtn").textContent = "Guardar Cambios";
    form.elements["id"].value = z.id;
    form.elements["nombre"].value = z.nombre;
    form.elements["direccion"].value = z.direccion;
    form.elements["comuna"].value = z.comuna;
    form.elements["barrio"].value = z.barrio;
    fillUsuariosSelect(form.elements["persona_cargo"], z.persona_cargo);
    var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
  }

  function openDetailModal(id) {
    var z = data.find(function (x) { return x.id === id; });
    if (!z) return;
    var tanquesZoo = tanquesDe(z.id);
    var body = document.getElementById("zoocriaderoDetailBody");
    body.innerHTML =
      "<dl class=\"row mb-0\">" +
      '<dt class="col-5">Nombre</dt><dd class="col-7">' + escapeHtml(z.nombre) + "</dd>" +
      '<dt class="col-5">Dirección</dt><dd class="col-7">' + escapeHtml(z.direccion) + "</dd>" +
      '<dt class="col-5">Comuna</dt><dd class="col-7">' + escapeHtml(z.comuna) + "</dd>" +
      '<dt class="col-5">Barrio</dt><dd class="col-7">' + escapeHtml(z.barrio) + "</dd>" +
      '<dt class="col-5">Persona a cargo</dt><dd class="col-7">' + escapeHtml(z.persona_cargo) + "</dd>" +
      '<dt class="col-5">Tanques</dt><dd class="col-7">' + tanquesZoo.length + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' +
      (z.estado === 1 ? '<span class="badge-estado activo">Activo</span>' : '<span class="badge-estado inactivo">Inhabilitado</span>') +
      "</dd>" +
      '<dt class="col-5">Registrado</dt><dd class="col-7">' + fmtFecha(z.creado_en) + "</dd>" +
      "</dl>" +
      '<hr class="my-3" />' +
      '<h6 class="fw-bold mb-2">Tanques registrados</h6>' +
      (tanquesZoo.length
        ? '<ul class="list-group list-group-flush">' +
          tanquesZoo.map(function (t) {
            return '<li class="list-group-item px-0">' +
              '<span class="fw-bold">' + escapeHtml(t.nombre) + "</span>" +
              (t.descripcion ? '<div class="small text-muted">' + escapeHtml(t.descripcion) + "</div>" : "") +
              "</li>";
          }).join("") +
          "</ul>"
        : '<p class="small text-muted mb-0">Este zoocriadero todavía no tiene tanques registrados.</p>');
    var bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("zoocriaderoDetailModal"));
    bsModal.show();
  }

  // ---- Modal Registrar Tanque ----
  var tanqueModalEl = document.getElementById("tanqueModal");
  var tanqueForm = document.getElementById("tanqueForm");

  function openCreateTanqueModal() {
    tanqueForm.reset();
    fillZoocriaderosSelect(tanqueForm.elements["id_zoocriadero"], null);
  }

  function handleTanqueSubmit(e) {
    e.preventDefault();
    if (!permisos().crear) return; // por si acaso
    var payload = {
      id_zoocriadero: Number(tanqueForm.elements["id_zoocriadero"].value),
      nombre: tanqueForm.elements["nombre"].value.trim(),
      descripcion: tanqueForm.elements["descripcion"].value.trim(),
    };
    if (!payload.id_zoocriadero || !payload.nombre) return;

    tanques.push(Object.assign({ id: nextTanqueId() }, payload)); // TODO API: POST /api/tanques
    saveTanques(tanques);
    render();
    bootstrap.Modal.getOrCreateInstance(tanqueModalEl).hide();
  }

  function handleSubmit(e) {
    e.preventDefault();
    if (!permisos().crear) return; // por si acaso
    var id = form.elements["id"].value;
    var payload = {
      nombre: form.elements["nombre"].value.trim(),
      direccion: form.elements["direccion"].value.trim(),
      comuna: form.elements["comuna"].value.trim(),
      barrio: form.elements["barrio"].value.trim(),
      persona_cargo: form.elements["persona_cargo"].value,
    };
    if (!payload.nombre || !payload.direccion) return;

    if (id) {
      var z = data.find(function (x) { return String(x.id) === String(id); });
      if (z) Object.assign(z, payload); // TODO API: PUT /api/zoocriaderos/:id
    } else {
      data.push(
        Object.assign({ id: nextId(), estado: 1, creado_en: new Date().toISOString().slice(0, 10) }, payload)
      ); // TODO API: POST /api/zoocriaderos
    }
    saveData(data);
    render();
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
  }

  function toggleEstado(id, nuevoEstado) {
    if (!permisos().inhabilitar) return;
    var z = data.find(function (x) { return x.id === id; });
    if (!z) return;
    var accion = nuevoEstado === 1 ? "habilitar" : "inhabilitar";
    if (!confirm('¿Seguro que deseas ' + accion + ' "' + z.nombre + '"?')) return;
    z.estado = nuevoEstado; // TODO API: PATCH /api/zoocriaderos/:id/estado
    saveData(data);
    render();
  }

  // ---- Eventos ----
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
    // Si el nuevo rol ya no puede crear/editar, cierra los modales de
    // registro/edición si habían quedado abiertos de la vista anterior.
    if (!permisos().crear && modalEl.classList.contains("show")) {
      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    }
    if (!permisos().crear && tanqueModalEl.classList.contains("show")) {
      bootstrap.Modal.getOrCreateInstance(tanqueModalEl).hide();
    }
    render();
  });

  render();
})();
