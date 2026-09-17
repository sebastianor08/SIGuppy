/* =========================================================
   SIGuppys — Módulo Usuarios
   =========================================================
   Los datos se leen de PostgreSQL a través del router MVC:
     Web/ajax.php?modulo=Usuario&controlador=Usuario&funcion=...

   Endpoints usados:
     lista       -> GET   usuarios + su rol
     roles       -> GET   para el <select> "Rol"
     postCreate  -> POST  INSERT en usuario
     postUpdate  -> POST  UPDATE en usuario
     postEstado  -> POST  UPDATE del campo estado (activar/desactivar)
   ========================================================= */

(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "usuarios") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Usuario&controlador=Usuario";

  // Solo Administrador / Super Administrador pueden crear, editar o
  // activar/desactivar usuarios; los demás roles solo consultan.
  var PERMISOS = {
    auxiliar: { crear: false, editar: false, estado: false },
    coordinador: { crear: false, editar: false, estado: false },
    administrador: { crear: true, editar: true, estado: true },
    superadministrador: { crear: true, editar: true, estado: true },
  };

  var data = [];   // usuarios traídos de la base
  var roles = [];  // roles para el select y el filtro
  var state = { q: "", rol: "todos" };

  // ---------- Utilidades ----------
  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function role() {
    return (window.SIGuppys && window.SIGuppys.getRole()) || "auxiliar";
  }
  function permisos() {
    return PERMISOS[role()] || PERMISOS.auxiliar;
  }

  function showMessage(text, type) {
    var box = document.getElementById("usuariosMessage");
    if (!box) return;
    box.className = "alert mb-3 alert-" + type;
    box.textContent = text;
  }
  function clearMessage() {
    var box = document.getElementById("usuariosMessage");
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
  function normalizar(u) {
    return {
      id: Number(u.id_usuario),
      nombre: u.nombre || "",
      apellido: u.apellido || "",
      correo: u.correo || "",
      telefono: u.telefono || "",
      estado: Number(u.estado),
      id_rol: Number(u.id_rol),
      nombre_rol: u.nombre_rol || "",
    };
  }

  // ---------- Selects ----------
  function fillRolesSelect(selectEl, seleccionado) {
    selectEl.innerHTML =
      '<option value="">Seleccione...</option>' +
      roles
        .map(function (r) {
          var sel = Number(r.id_rol) === Number(seleccionado) ? " selected" : "";
          return '<option value="' + r.id_rol + '"' + sel + ">" + escapeHtml(r.nombre_rol) + "</option>";
        })
        .join("");
  }

  function fillRolesFiltro() {
    var selectEl = document.getElementById("usuariosRolFiltro");
    selectEl.innerHTML =
      '<option value="todos">Todos los roles</option>' +
      roles
        .map(function (r) {
          return '<option value="' + escapeHtml(r.nombre_rol) + '">' + escapeHtml(r.nombre_rol) + "</option>";
        })
        .join("");
  }

  // ---------- Render ----------
  function renderAcciones(u) {
    var p = permisos();
    var btns = "";

    if (p.editar) {
      btns +=
        '<button type="button" class="btn-icon" data-action="editar" data-id="' +
        u.id + '" title="Editar"><i class="fas fa-pen"></i></button>';
    }
    if (p.estado) {
      if (u.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" data-action="desactivar" data-id="' +
          u.id + '" title="Desactivar"><i class="fas fa-ban"></i></button>';
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" data-action="activar" data-id="' +
          u.id + '" title="Activar"><i class="fas fa-check-circle"></i></button>';
      }
    }
    return '<div class="table-actions">' + (btns || '<span class="text-muted small">Sin permisos</span>') + "</div>";
  }

  function renderRow(u) {
    var estadoBadge =
      u.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inactivo</span>';
    return (
      "<tr>" +
      '<td><span class="fw-bold">' + escapeHtml(u.nombre + " " + u.apellido) + "</span>" +
      '<div class="small text-muted">' + escapeHtml(u.telefono) + "</div></td>" +
      "<td>" + escapeHtml(u.correo) + "</td>" +
      "<td>" + escapeHtml(u.nombre_rol) + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(u) + "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();
    return data.filter(function (u) {
      var texto = (u.nombre + " " + u.apellido + " " + u.correo).toLowerCase();
      var matchesQ = !q || texto.indexOf(q) !== -1;
      var matchesRol = state.rol === "todos" || u.nombre_rol === state.rol;
      return matchesQ && matchesRol;
    });
  }

  function render() {
    var tbody = document.getElementById("usuariosTableBody");
    var rows = filteredData();
    if (!rows.length) {
      tbody.innerHTML =
        '<tr class="sig-empty-row"><td colspan="5"><i class="fas fa-folder-open mb-2 d-block" style="font-size:22px;color:#ccc;"></i>No hay usuarios que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }
    renderCrearBtn();
    var countEl = document.getElementById("usuariosCount");
    if (countEl) countEl.textContent = rows.length + " de " + data.length + " usuarios";
  }

  function renderCrearBtn() {
    var wrap = document.getElementById("crearUsuarioWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#usuarioModal" id="btnAbrirCrear">' +
        '<i class="fas fa-plus me-1"></i> Crear Usuario</button>';
    } else {
      wrap.innerHTML = "";
    }
  }

  // ---------- Cargar datos iniciales ----------
  async function cargarTodo() {
    try {
      var [usuarios, rolesBd] = await Promise.all([getJson("lista"), getJson("roles")]);
      data = usuarios.map(normalizar);
      roles = rolesBd;
      fillRolesFiltro();
      render();
    } catch (err) {
      showMessage(err.message, "danger");
    }
  }

  // ---------- Abrir modal para crear ----------
  function abrirModalCrear() {
    var form = document.getElementById("usuarioForm");
    form.reset();
    form.querySelector('[name="id_usuario"]').value = "";
    document.getElementById("usuarioModalLabel").textContent = "Registrar Usuario";
    document.getElementById("contrasenaWrap").style.display = "block";
    form.querySelector('[name="contrasena"]').required = true;
    fillRolesSelect(document.getElementById("rolSelect"), "");
  }

  // ---------- Abrir modal para editar ----------
  function abrirModalEditar(id) {
    var u = data.find(function (x) { return x.id === id; });
    if (!u) return;

    var form = document.getElementById("usuarioForm");
    form.reset();
    form.querySelector('[name="id_usuario"]').value = u.id;
    form.querySelector('[name="nombre"]').value = u.nombre;
    form.querySelector('[name="apellido"]').value = u.apellido;
    form.querySelector('[name="correo"]').value = u.correo;
    form.querySelector('[name="telefono"]').value = u.telefono;

    document.getElementById("usuarioModalLabel").textContent = "Editar Usuario";
    document.getElementById("contrasenaWrap").style.display = "none";
    form.querySelector('[name="contrasena"]').required = false;

    fillRolesSelect(document.getElementById("rolSelect"), u.id_rol);

    var modal = new bootstrap.Modal(document.getElementById("usuarioModal"));
    modal.show();
  }

  // ---------- Guardar (crear o actualizar) ----------
  async function guardar(e) {
    e.preventDefault();
    clearMessage();

    var form = e.target;
    var idUsuario = form.querySelector('[name="id_usuario"]').value;

    var payload = {
      nombre: form.querySelector('[name="nombre"]').value,
      apellido: form.querySelector('[name="apellido"]').value,
      correo: form.querySelector('[name="correo"]').value,
      telefono: form.querySelector('[name="telefono"]').value,
      id_rol: form.querySelector('[name="id_rol"]').value,
    };

    try {
      if (idUsuario) {
        payload.id_usuario = idUsuario;
        await postJson("postUpdate", payload);
        showMessage("Usuario actualizado correctamente.", "success");
      } else {
        payload.contrasena = form.querySelector('[name="contrasena"]').value;
        await postJson("postCreate", payload);
        showMessage("Usuario registrado correctamente.", "success");
      }

      bootstrap.Modal.getInstance(document.getElementById("usuarioModal")).hide();
      await cargarTodo();
    } catch (err) {
      showMessage(err.message, "danger");
    }
  }

  // ---------- Activar / Desactivar ----------
  async function cambiarEstado(id, nuevoEstado) {
    try {
      await postJson("postEstado", { id_usuario: id, estado: nuevoEstado });
      showMessage(nuevoEstado === 1 ? "Usuario activado." : "Usuario desactivado.", "success");
      await cargarTodo();
    } catch (err) {
      showMessage(err.message, "danger");
    }
  }

  // ---------- Eventos ----------
  document.addEventListener("DOMContentLoaded", function () {
    cargarTodo();

    document.getElementById("usuariosSearch").addEventListener("input", function (e) {
      state.q = e.target.value;
      render();
    });

    document.getElementById("usuariosRolFiltro").addEventListener("change", function (e) {
      state.rol = e.target.value;
      render();
    });

    document.getElementById("usuarioForm").addEventListener("submit", guardar);

    document.getElementById("usuarioModal").addEventListener("show.bs.modal", function (e) {
      // Si lo abrió el botón "Crear Usuario", prepara el formulario en blanco.
      // (Si lo abrió "editar", abrirModalEditar ya lo dejó listo antes.)
      if (e.relatedTarget && e.relatedTarget.id === "btnAbrirCrear") {
        abrirModalCrear();
      }
    });

    document.getElementById("usuariosTableBody").addEventListener("click", function (e) {
      var btn = e.target.closest("[data-action]");
      if (!btn) return;
      var id = Number(btn.dataset.id);
      var accion = btn.dataset.action;

      if (accion === "editar") abrirModalEditar(id);
      if (accion === "activar") cambiarEstado(id, 1);
      if (accion === "desactivar") cambiarEstado(id, 0);
    });
  });
})();