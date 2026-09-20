(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "usuarios-registrar") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Usuarios&controlador=Usuarios";

  var data = [];   // usuarios traídos de la base
  var roles = [];  // roles activos
  var tiposDocumento = [];
  var state = { q: "", rol: "", estado: "todos" };

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
  function roleLabel() {
    var rolesUi = window.SIGuppys && window.SIGuppys.ROLES;
    return (rolesUi && rolesUi[role()] && rolesUi[role()].label) || role();
  }
  function permisos() {
    // Por ahora, todos los roles ven habilitado crear/editar/inhabilitar
    // (sin filtrar por rol todavía, igual que en Zoocriaderos).
    return { crear: true, editar: true, inhabilitar: true };
  }
  function lockedTitle(accion) {
    return "Tu rol (" + roleLabel() + ") no tiene permiso para " + accion + ".";
  }

  function colorRol(nombreRol) {
    var n = (nombreRol || "").toLowerCase();
    if (n.indexOf("super") !== -1) return "badge-info";
    if (n.indexOf("administrador") !== -1) return "badge-primary";
    if (n.indexOf("coordinador") !== -1) return "badge-warning";
    return "badge-secondary";
  }

  function iniciales(nombre, apellido) {
    var a = (nombre || "").trim().charAt(0);
    var b = (apellido || "").trim().charAt(0);
    return (a + b).toUpperCase() || "?";
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

  // ---------- Reglas del documento y la contraseña ----------
  // Las mismas reglas están en PHP (lib/validaciones.php). Aquí se validan
  // solo para avisarle al usuario antes de enviar; el servidor vuelve a validar.

  // Sin espacios y en mayúscula, igual que normalizarDocumento() en PHP
  function normalizarDocumento(valor) {
    return String(valor || "").replace(/\s+/g, "").toUpperCase();
  }

  // Reglas por tipo de documento (debe coincidir con REGLAS_DOCUMENTO_POR_TIPO
  // en lib/validaciones.php). El PPT no viene en la referencia que
  // compartiste, así que se dejó igual que C.E./Pasaporte (alfanumérico,
  // hasta 15) como valor conservador; ajusta aquí y en el PHP si tienes el
  // rango oficial.
  var REGLAS_DOCUMENTO_POR_TIPO = {
    "cédula de ciudadanía": { min: 6, max: 10, soloNumeros: true },
    "tarjeta de identidad": { min: 10, max: 11, soloNumeros: false },
    "cédula de extranjería": { min: 6, max: 15, soloNumeros: false },
    "pasaporte": { min: 6, max: 15, soloNumeros: false },
    "permiso por protección temporal": { min: 6, max: 15, soloNumeros: false },
  };
  var REGLA_DOCUMENTO_DEFECTO = { min: 5, max: 20, soloNumeros: false };

  function reglasDocumentoPorTipo(nombreTipo) {
    var clave = String(nombreTipo || "").trim().toLowerCase();
    return REGLAS_DOCUMENTO_POR_TIPO[clave] || REGLA_DOCUMENTO_DEFECTO;
  }

  function reglasDocumentoTexto(nombreTipo) {
    var regla = reglasDocumentoPorTipo(nombreTipo);
    var tipoCaracteres = regla.soloNumeros ? "solo números" : "letras y/o números";
    if (regla.min === regla.max) {
      return "Debe tener exactamente " + regla.min + " caracteres (" + tipoCaracteres + ").";
    }
    return "Debe tener entre " + regla.min + " y " + regla.max + " caracteres (" + tipoCaracteres + ").";
  }

  function validarDocumento(valor, nombreTipo) {
    var doc = normalizarDocumento(valor);
    var regla = reglasDocumentoPorTipo(nombreTipo);

    if (!doc) return "El número de documento es obligatorio.";
    var patron = regla.soloNumeros ? /^[0-9]+$/ : /^[A-Z0-9]+$/;
    if (!patron.test(doc)) {
      return regla.soloNumeros
        ? "Para " + nombreTipo + ", el número de documento solo puede contener números."
        : "El número de documento solo puede contener letras y números, sin espacios ni signos.";
    }
    if (doc.length < regla.min) {
      return "Para " + nombreTipo + ", el número de documento debe tener al menos " + regla.min + " caracteres.";
    }
    if (doc.length > regla.max) {
      return "Para " + nombreTipo + ", el número de documento no puede superar " + regla.max + " caracteres.";
    }
    return null;
  }

  // Solo letras (con tildes/ñ), espacios simples entre palabras, y
  // apóstrofe/guion para casos como "O'Higgins" o "Pérez-Gómez". Nada de
  // números ni otros símbolos.
  var REGEX_NOMBRE_PROPIO = /^[\p{L}\p{M}'-]+(?: [\p{L}\p{M}'-]+)*$/u;
  var REGEX_CARACTERES_PERMITIDOS_NOMBRE = /[^\p{L}\p{M}\s'-]/gu;

  function validarNombrePropio(valor, etiqueta, min, max) {
    var limpio = String(valor || "").trim().replace(/\s+/g, " ");
    if (!limpio) return 'El campo "' + etiqueta + '" es obligatorio.';
    if (/[0-9]/.test(limpio)) return 'El campo "' + etiqueta + '" no puede contener números.';
    if (!REGEX_NOMBRE_PROPIO.test(limpio)) {
      return 'El campo "' + etiqueta + '" solo puede contener letras (sin números ni símbolos, aparte de apóstrofe o guion).';
    }
    if (limpio.length < min) return 'El campo "' + etiqueta + '" debe tener al menos ' + min + ' caracteres.';
    if (limpio.length > max) return 'El campo "' + etiqueta + '" no puede superar ' + max + ' caracteres.';
    return null;
  }

  // Solo se aceptan correos de estos dominios (debe ser la misma lista
  // que DOMINIOS_CORREO_PERMITIDOS en lib/validaciones.php: esta copia
  // solo avisa antes de tiempo, la que manda de verdad es la de PHP).
  var DOMINIOS_CORREO_PERMITIDOS = ["cali.gov.co", "gmail.com"];

  // Solo letras, números, punto, guion y guion bajo antes de la @ (así que
  // algo como "juan#23@gmail.com" o "juan 23@gmail.com" queda bloqueado
  // aquí mismo, sin esperar la respuesta del servidor).
  var REGEX_CORREO_ESTRICTO = /^[a-z0-9]+(?:[._-][a-z0-9]+)*@[a-z0-9]+(?:[.-][a-z0-9]+)*\.[a-z]{2,}$/;

  function validarCorreo(valor) {
    var correo = String(valor || "").replace(/\u00a0/g, " ").trim().toLowerCase();
    if (!correo) return "El correo electrónico es obligatorio.";
    if (/\s/.test(correo)) return "El correo electrónico no puede contener espacios.";
    if (!REGEX_CORREO_ESTRICTO.test(correo)) {
      return "El correo electrónico no es válido. Solo se permiten letras, números, puntos, guiones y guion bajo antes de la @.";
    }
    var dominio = correo.split("@").pop();
    if (DOMINIOS_CORREO_PERMITIDOS.indexOf(dominio) === -1) {
      return "El correo debe ser de uno de estos dominios: " + DOMINIOS_CORREO_PERMITIDOS.join(", ") + ".";
    }
    return null;
  }

  // Mínimo 8 caracteres, una minúscula, una mayúscula y un carácter especial
  function validarContrasena(valor) {
    var clave = String(valor || "");
    if (clave.length < 8) return "La contraseña debe tener al menos 8 caracteres.";
    if (!/[a-záéíóúñü]/.test(clave)) return "La contraseña debe incluir al menos una letra minúscula.";
    if (!/[A-ZÁÉÍÓÚÑÜ]/.test(clave)) return "La contraseña debe incluir al menos una letra mayúscula.";
    if (!/[^A-Za-z0-9ÁÉÍÓÚÑÜáéíóúñü\s]/.test(clave)) {
      return "La contraseña debe incluir al menos un carácter especial (por ejemplo: ! @ # $ % & * ?).";
    }
    if (/\s/.test(clave)) return "La contraseña no puede contener espacios.";
    // --- Descomenta si también quieres exigir un número ---
    // if (!/[0-9]/.test(clave)) return "La contraseña debe incluir al menos un número.";
    return null;
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
      documento: u.documento || "",
      estado: Number(u.estado),
      creado_en: u.creado_en || "",
      id_rol: Number(u.id_rol),
      nombre_rol: u.nombre_rol || "",
      id_tipodocumento: Number(u.id_tipodocumento),
      tipo_documento: u.tipo_documento || "",
    };
  }

  // ---------- Selects ----------
  function fillRolesSelect(selectEl, seleccionado) {
    selectEl.innerHTML =
      '<option value="">Seleccione el rol</option>' +
      roles
        .map(function (r) {
          var sel = String(r.id_rol) === String(seleccionado) ? " selected" : "";
          return '<option value="' + r.id_rol + '"' + sel + ">" + escapeHtml(r.nombre_rol) + "</option>";
        })
        .join("");
  }

  function fillTiposDocumentoSelect(selectEl, seleccionado) {
    selectEl.innerHTML =
      '<option value="">Seleccione el tipo de documento</option>' +
      tiposDocumento
        .map(function (t) {
          var sel = String(t.id_tipodocumento) === String(seleccionado) ? " selected" : "";
          return '<option value="' + t.id_tipodocumento + '"' + sel + ">" + escapeHtml(t.nombre) + "</option>";
        })
        .join("");
  }

  function fillRolFiltro() {
    var select = document.getElementById("usuariosRolFiltro");
    select.innerHTML =
      '<option value="">Todos los roles</option>' +
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
    if (p.inhabilitar) {
      if (u.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" data-action="inhabilitar" data-id="' +
          u.id + '" title="Inhabilitar"><i class="fas fa-ban"></i></button>';
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" data-action="habilitar" data-id="' +
          u.id + '" title="Habilitar"><i class="fas fa-check-circle"></i></button>';
      }
    }
    return '<div class="table-actions">' + btns + "</div>";
  }

  function renderRow(u) {
    var estadoBadge =
      u.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';
    return (
      "<tr>" +
      '<td><div class="d-flex align-items-center">' +
      '<span class="avatar-title rounded-circle bg-light text-dark border me-2" style="width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;">' +
      iniciales(u.nombre, u.apellido) + "</span>" +
      "<div><span class=\"fw-bold\">" + escapeHtml(u.nombre + " " + u.apellido) + "</span>" +
      '<div class="small text-muted">Registrado ' + fmtFecha(u.creado_en) + "</div></div>" +
      "</div></td>" +
      "<td>" + escapeHtml(u.correo) + "</td>" +
      "<td><span class=\"fw-bold\">" + escapeHtml(u.documento) + "</span>" +
      '<div class="small text-muted">' + escapeHtml(u.tipo_documento) + "</div></td>" +
      '<td><span class="badge ' + colorRol(u.nombre_rol) + '">' + escapeHtml(u.nombre_rol) + "</span></td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(u) + "</td>" +
      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();
    return data.filter(function (u) {
      var texto = (u.nombre + " " + u.apellido + " " + u.correo + " " + u.documento).toLowerCase();
      var matchesQ = !q || texto.indexOf(q) !== -1;
      var matchesRol = !state.rol || u.nombre_rol === state.rol;
      var matchesEstado =
        state.estado === "todos" ||
        (state.estado === "activo" && u.estado === 1) ||
        (state.estado === "inactivo" && u.estado === 0);
      return matchesQ && matchesRol && matchesEstado;
    });
  }

  function render() {
    var tbody = document.getElementById("usuariosTableBody");
    var rows = filteredData();
    if (!rows.length) {
      tbody.innerHTML =
        '<tr class="sig-empty-row"><td colspan="6"><i class="fas fa-folder-open mb-2 d-block" style="font-size:22px;color:#ccc;"></i>No hay usuarios que coincidan con el filtro.</td></tr>';
    } else {
      tbody.innerHTML = rows.map(renderRow).join("");
    }
    renderRegistrarBtn();
    var countEl = document.getElementById("usuariosCount");
    if (countEl) countEl.textContent = rows.length + " de " + data.length + " usuarios";
  }

  function renderRegistrarBtn() {
    var wrap = document.getElementById("registrarUsuarioWrap");
    if (!wrap) return;
    if (permisos().crear) {
      wrap.innerHTML =
        '<button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#usuarioModal" id="btnAbrirRegistrarUsuario">' +
        '<i class="fas fa-plus me-1"></i> Crear Usuario</button>';
    } else {
      wrap.innerHTML =
        '<button type="button" class="btn btn-round btn-locked" disabled title="' +
        escapeHtml(lockedTitle("crear usuarios")) + '">' +
        '<i class="fas fa-lock me-1"></i> Crear Usuario</button>';
    }
  }

  // ---------- Modal Registrar / Editar ----------
  var modalEl = document.getElementById("usuarioModal");
  var form = document.getElementById("usuarioForm");
  var passwordGroup = document.getElementById("usuarioPasswordGroup");
  var passwordInput = form.elements["contrasena"];
  var nombreInput = form.elements["nombre"];
  var apellidoInput = form.elements["apellido"];
  var documentoInput = form.elements["documento"];
  var tipoDocumentoSelect = form.elements["id_tipodocumento"];
  var documentoHint = document.getElementById("documentoHint");

  function tipoDocumentoSeleccionadoTexto() {
    var opcion = tipoDocumentoSelect.options[tipoDocumentoSelect.selectedIndex];
    return opcion ? opcion.text.trim() : "";
  }

  // No deja ni siquiera escribir números/símbolos en Nombres y Apellidos:
  // se limpia el valor en cada tecla, no solo al enviar el formulario.
  function filtrarSoloLetras(input) {
    input.addEventListener("input", function () {
      var limpio = this.value.replace(REGEX_CARACTERES_PERMITIDOS_NOMBRE, "");
      if (limpio !== this.value) this.value = limpio;
    });
  }
  filtrarSoloLetras(nombreInput);
  filtrarSoloLetras(apellidoInput);

  // El documento se filtra distinto según el tipo elegido: solo números
  // para Cédula de Ciudadanía, o alfanumérico (sin espacios/signos) para
  // el resto. También actualiza el texto de ayuda y el maxlength.
  function actualizarReglaDocumento() {
    var tieneTipoSeleccionado = !!tipoDocumentoSelect.value;
    var tipoTexto = tipoDocumentoSeleccionadoTexto();
    var regla = reglasDocumentoPorTipo(tieneTipoSeleccionado ? tipoTexto : "");
    documentoInput.maxLength = regla.max;
    if (documentoHint) {
      documentoHint.textContent = tieneTipoSeleccionado
        ? reglasDocumentoTexto(tipoTexto)
        : "Seleccione primero el tipo de documento.";
    }
    // Si el valor actual ya no cumple el patrón del nuevo tipo (por ejemplo,
    // tenía letras y ahora eligió Cédula de Ciudadanía), se recorta en vivo.
    var patron = regla.soloNumeros ? /[^0-9]/g : /[^A-Za-z0-9]/g;
    var limpio = documentoInput.value.replace(patron, "");
    if (limpio !== documentoInput.value) documentoInput.value = limpio;
  }
  documentoInput.addEventListener("input", actualizarReglaDocumento);
  tipoDocumentoSelect.addEventListener("change", actualizarReglaDocumento);

  function openCreateModal() {
    form.reset();
    form.elements["id_usuario"].value = "";
    document.getElementById("usuarioModalLabel").textContent = "Registrar Usuario";
    document.getElementById("usuarioSubmitBtn").textContent = "Guardar Registro";
    fillTiposDocumentoSelect(form.elements["id_tipodocumento"], null);
    fillRolesSelect(form.elements["id_rol"], null);
    passwordGroup.style.display = "";
    passwordInput.required = true;
    actualizarReglaDocumento();
  }

  function openEditModal(id) {
    var u = data.find(function (x) { return x.id === id; });
    if (!u) return;
    document.getElementById("usuarioModalLabel").textContent = "Editar Usuario";
    document.getElementById("usuarioSubmitBtn").textContent = "Guardar Cambios";
    form.elements["id_usuario"].value = u.id;
    form.elements["nombre"].value = u.nombre;
    form.elements["apellido"].value = u.apellido;
    form.elements["documento"].value = u.documento;
    form.elements["correo"].value = u.correo;
    fillTiposDocumentoSelect(form.elements["id_tipodocumento"], u.id_tipodocumento);
    fillRolesSelect(form.elements["id_rol"], u.id_rol);
    passwordGroup.style.display = "none";
    passwordInput.required = false;
    passwordInput.value = "";
    actualizarReglaDocumento();

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }

  async function handleSubmit(e) {
    e.preventDefault();
    if (!permisos().crear) return;

    var id = form.elements["id_usuario"].value;
    var tipoDocumentoTexto = tipoDocumentoSeleccionadoTexto();
    var payload = {
      nombre: nombreInput.value.trim().replace(/\s+/g, " "),
      apellido: apellidoInput.value.trim().replace(/\s+/g, " "),
      documento: normalizarDocumento(documentoInput.value),
      correo: form.elements["correo"].value.trim(),
      id_tipodocumento: Number(form.elements["id_tipodocumento"].value),
      id_rol: Number(form.elements["id_rol"].value),
    };

    var errorNombre = validarNombrePropio(payload.nombre, "Nombres", 2, 50);
    if (errorNombre) {
      alert(errorNombre);
      nombreInput.focus();
      return;
    }
    var errorApellido = validarNombrePropio(payload.apellido, "Apellidos", 2, 50);
    if (errorApellido) {
      alert(errorApellido);
      apellidoInput.focus();
      return;
    }
    if (!payload.id_tipodocumento) { alert("Debe seleccionar el tipo de documento."); return; }
    var errorDoc = validarDocumento(payload.documento, tipoDocumentoTexto);
    if (errorDoc) {
      alert(errorDoc);
      documentoInput.focus();
      return;
    }
    var errorCorreo = validarCorreo(payload.correo);
    if (errorCorreo) {
      alert(errorCorreo);
      form.elements["correo"].focus();
      return;
    }
    if (!payload.id_rol) { alert("Debe seleccionar el rol."); return; }

    try {
      var res;
      if (id) {
        payload.id_usuario = Number(id);
        res = await postJson("postUpdate", payload); // UPDATE
      } else {
        payload.contrasena = passwordInput.value;
        var errorClave = validarContrasena(payload.contrasena);
        if (errorClave) {
          alert(errorClave);
          passwordInput.focus();
          return;
        }
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
    var u = data.find(function (x) { return x.id === id; });
    if (!u) return;

    var accion = nuevoEstado === 1 ? "habilitar" : "inhabilitar";
    if (!confirm("¿Seguro que deseas " + accion + ' a "' + u.nombre + " " + u.apellido + '"?')) return;

    try {
      var res = await postJson("postEstado", { id_usuario: id, estado: nuevoEstado });
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
  document.getElementById("usuariosTableBody").addEventListener("click", function (e) {
    var btn = e.target.closest("[data-action]");
    if (!btn) return;
    var id = Number(btn.getAttribute("data-id"));
    var action = btn.getAttribute("data-action");
    if (action === "editar" && permisos().editar) openEditModal(id);
    if (action === "inhabilitar") toggleEstado(id, 0);
    if (action === "habilitar") toggleEstado(id, 1);
  });

  document.getElementById("registrarUsuarioWrap").addEventListener("click", function (e) {
    if (e.target.closest("#btnAbrirRegistrarUsuario")) openCreateModal();
  });

  form.addEventListener("submit", handleSubmit);

  document.getElementById("usuariosSearch").addEventListener("input", function () {
    state.q = this.value;
    render();
  });
  document.getElementById("usuariosRolFiltro").addEventListener("change", function () {
    state.rol = this.value;
    render();
  });
  document.getElementById("usuariosEstadoFiltro").addEventListener("change", function () {
    state.estado = this.value;
    render();
  });

  document.addEventListener("siguppys:role-changed", function () {
    if (!permisos().crear && modalEl.classList.contains("show")) {
      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    }
    render();
  });

  (async function init() {
    document.getElementById("usuariosTableBody").innerHTML =
      '<tr class="sig-empty-row"><td colspan="6">Cargando usuarios...</td></tr>';
    try {
      var resultados = await Promise.all([
        getJson("lista"),
        getJson("roles"),
        getJson("tiposDocumento"),
      ]);
      data = resultados[0].map(normalizar);
      roles = resultados[1];
      tiposDocumento = resultados[2];
      fillRolFiltro();
      clearMessage();
      render();
    } catch (error) {
      document.getElementById("usuariosTableBody").innerHTML =
        '<tr class="sig-empty-row"><td colspan="6">No se pudieron cargar los datos.</td></tr>';
      showMessage(error.message + " Verifica que PHP pueda conectarse a PostgreSQL.", "danger");
    }
  })();
})();