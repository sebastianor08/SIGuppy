(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "terreno-seguimiento-deposito") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=SeguimientoDeposito&controlador=SeguimientoDeposito";

  // Permisos reales del rol de la sesión sobre este módulo (ver
  // lib/permisos.php / View/partials/footer.php).
  var PERMISOS_VACIOS = { ver: false, consultar: false, crear: false, editar: false, inhabilitar: false, exportar: false };
  function permisos() {
    return window.SIG_PERMISOS || PERMISOS_VACIOS;
  }

  // ---------- Referencias al DOM ----------
  var form = document.getElementById("seguimientoDepositoForm");
  var idSeguimiento = document.getElementById("id_seguimiento_deposito");
  var depositoSelect = document.getElementById("sdDeposito");
  var direccion = document.getElementById("sdDireccion");
  var fecha = document.getElementById("sdFecha");
  var actividadSelect = document.getElementById("sdActividad");
  var larvasSelect = document.getElementById("sdLarvas");
  var peces = document.getElementById("sdPeces");
  var observaciones = document.getElementById("sdObservaciones");
  var observacionesCount = document.getElementById("sdObservacionesCount");
  var formMessage = document.getElementById("sdFormMessage");
  var formTitulo = document.getElementById("sdFormTitulo");
  var btnGuardar = document.getElementById("sdBtnGuardar");
  var btnCancelar = document.getElementById("sdBtnCancelarEdicion");

  var mapaEl = document.getElementById("mapaSeguimientoDeposito");
  var leyenda = document.getElementById("sdLeyenda");
  var sinUbicacion = document.getElementById("sdSinUbicacion");

  var tbody = document.getElementById("sdTableBody");
  var search = document.getElementById("sdSearch");
  var filtroEstado = document.getElementById("sdEstadoFiltro");
  var count = document.getElementById("sdCount");
  var message = document.getElementById("sdMessage");
  var detailModalEl = document.getElementById("sdDetailModal");

  // ---------- Estado ----------
  // Colores (y formas) pensados también para daltonismo: rojo/azul/gris y rombo/círculo.
  var COLORES = { sin: "#8d99a6", positivo: "#e53935", negativo: "#1e88e5" };
  var ETIQUETAS = {
    positivo: "Con larvas en el último seguimiento",
    negativo: "Sin larvas en el último seguimiento",
    sin: "Sin seguimiento aún"
  };
  var CALI = [3.4372, -76.5225];

  var depositos = [];
  var actividades = [];
  var historial = [];

  var map = null;
  var capas = {}; // estado del mapa -> L.layerGroup
  var marcadores = {}; // id_deposito -> { marker, estado }
  var seleccionadoId = null;
  var primerAjuste = true; // solo la primera vez que hay puntos se encuadra el mapa
  var editandoId = null;

  // ---------- Utilidades ----------
  function escapeHtml(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function hoyLocal() {
    var h = new Date();
    return (
      h.getFullYear() + "-" + String(h.getMonth() + 1).padStart(2, "0") + "-" + String(h.getDate()).padStart(2, "0")
    );
  }

  function showMessage(text, type) {
    message.className = "alert mb-3 alert-" + type;
    message.textContent = text;
  }

  function showFormMessage(text, type) {
    formMessage.className = "alert mt-3 mb-0 alert-" + type;
    formMessage.textContent = text;
  }

  function clearFormMessage() {
    formMessage.className = "alert d-none mt-3 mb-0";
    formMessage.textContent = "";
  }

  async function getJson(url) {
    var response = await fetch(url, { headers: { Accept: "application/json" } });
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
      headers: { "Content-Type": "application/json", Accept: "application/json" },
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

  function ubicacion(d) {
    return [d.direccion, d.barrio, d.comuna]
      .filter(function (parte) {
        return parte;
      })
      .join(" · ");
  }

  function etiquetaDeposito(d) {
    return d.tipo_deposito + " — " + (d.descripcion || "sin descripción") + " · " + d.sitio;
  }

  function buscarDeposito(id) {
    return depositos.find(function (d) {
      return String(d.id_deposito) === String(id);
    });
  }

  function buscarHistorial(id) {
    return historial.find(function (f) {
      return String(f.id_seguimiento_deposito) === String(id);
    });
  }

  // Estado que define el color del punto: según el ÚLTIMO seguimiento activo del depósito.
  function estadoMapa(d) {
    if (!d.ultima_fecha) return "sin";
    return Number(d.ultima_presencia_larvas) === 1 ? "positivo" : "negativo";
  }

  // Coordenadas válidas del depósito (las de la dirección de su sitio) o null.
  function coordenadas(d) {
    var lat = parseFloat(d.latitud);
    var lng = parseFloat(d.longitud);
    if (!isFinite(lat) || !isFinite(lng)) return null;
    if (lat === 0 && lng === 0) return null;
    if (Math.abs(lat) > 90 || Math.abs(lng) > 180) return null;
    return { lat: lat, lng: lng };
  }

  // ---------- Mapa ----------
  function estiloPunto(estado, tamano, seleccionado) {
    var forma =
      estado === "positivo"
        ? "border-radius:3px;transform:rotate(45deg);" // rombo = con larvas
        : "border-radius:50%;";
    var borde = seleccionado ? "3px solid #fff" : "2px solid #fff";
    var sombra = seleccionado
      ? "0 0 0 3px rgba(0,0,0,.45),0 0 8px rgba(0,0,0,.6)"
      : "0 0 3px rgba(0,0,0,.5)";
    return (
      "display:block;width:" + tamano + "px;height:" + tamano + "px;background:" + COLORES[estado] + ";" +
      forma + "border:" + borde + ";box-shadow:" + sombra + ";"
    );
  }

  function iconoMarcador(estado, seleccionado) {
    var t = seleccionado ? 22 : 16;
    return L.divIcon({
      className: "",
      html: '<span style="' + estiloPunto(estado, t, seleccionado) + '"></span>',
      iconSize: [t, t],
      iconAnchor: [t / 2, t / 2]
    });
  }

  // Varios depósitos pueden estar en el MISMO sitio (mismas coordenadas) y se
  // taparían entre sí. Los que comparten punto se reparten en un círculo de
  // ~10 m para que cada uno se vea y se pueda seleccionar.
  function posicionesSeparadas(lista) {
    var grupos = {};
    lista.forEach(function (item) {
      var clave = item.c.lat.toFixed(6) + "," + item.c.lng.toFixed(6);
      (grupos[clave] = grupos[clave] || []).push(item);
    });

    var posiciones = {};
    Object.keys(grupos).forEach(function (clave) {
      var grupo = grupos[clave];

      if (grupo.length === 1) {
        posiciones[grupo[0].d.id_deposito] = [grupo[0].c.lat, grupo[0].c.lng];
        return;
      }

      var radio = 0.00008 + 0.00002 * grupo.length; // grados (~9 m + 2 m por depósito)
      grupo.forEach(function (item, i) {
        var angulo = (2 * Math.PI * i) / grupo.length;
        posiciones[item.d.id_deposito] = [
          item.c.lat + radio * Math.sin(angulo),
          item.c.lng + radio * Math.cos(angulo)
        ];
      });
    });

    return posiciones;
  }

  function popupHtml(d, estado) {
    var ultimo =
      estado === "sin"
        ? "Sin seguimientos registrados"
        : "Último seguimiento: " + escapeHtml(d.ultima_fecha) + " · " + (estado === "positivo" ? "con larvas" : "sin larvas");

    return (
      "<strong>" + escapeHtml(d.tipo_deposito) + "</strong><br>" +
      (d.descripcion ? escapeHtml(d.descripcion) + "<br>" : "") +
      '<span style="color:#6b7280;">' + escapeHtml(d.sitio) + "</span><br>" +
      escapeHtml(ubicacion(d)) + "<br>" +
      "<em>" + ultimo + "</em> (" + Number(d.total_seguimientos || 0) + " en total)<br>" +
      '<button type="button" class="btn btn-primary btn-sm mt-2" data-seleccionar="' +
      escapeHtml(d.id_deposito) +
      '">Registrar seguimiento</button>'
    );
  }

  function iniciarMapa() {
    if (map) return;

    if (typeof L === "undefined") {
      mapaEl.innerHTML = '<div class="text-danger small p-3">No se pudo cargar el componente del mapa.</div>';
      return;
    }

    map = L.map("mapaSeguimientoDeposito").setView(CALI, 12); // Santiago de Cali

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 19
    }).addTo(map);

    ["positivo", "negativo", "sin"].forEach(function (estado) {
      capas[estado] = L.layerGroup().addTo(map);
    });

    // El botón "Registrar seguimiento" vive dentro del popup (HTML que crea Leaflet
    // cada vez que se abre): se atiende por delegación en el contenedor del mapa.
    mapaEl.addEventListener("click", function (event) {
      var boton = event.target.closest("[data-seleccionar]");
      if (!boton) return;

      seleccionarDeposito(boton.getAttribute("data-seleccionar"), false);
      map.closePopup();
      depositoSelect.scrollIntoView({ behavior: "smooth", block: "center" });
    });

    // El contenedor puede terminar de acomodarse después de crear el mapa.
    setTimeout(function () {
      map.invalidateSize();
    }, 250);
    window.addEventListener("load", function () {
      map.invalidateSize();
    });
  }

  function pintarLeyenda(conteo, sinCoordenadas) {
    var total = conteo.positivo + conteo.negativo + conteo.sin;

    if (!total && !sinCoordenadas.length) {
      leyenda.innerHTML = '<span class="text-muted small">Aún no hay depósitos registrados.</span>';
    } else {
      leyenda.innerHTML = ["positivo", "negativo", "sin"]
        .map(function (estado) {
          var visible = map && capas[estado] ? map.hasLayer(capas[estado]) : true;
          return (
            '<label class="form-check form-check-inline" style="margin-right:12px;">' +
            '<input type="checkbox" class="form-check-input" data-capa="' + estado + '"' + (visible ? " checked" : "") + ">" +
            '<span class="form-check-label"><span class="sd-punto sd-punto-' + estado + '"></span>' +
            escapeHtml(ETIQUETAS[estado]) + " (" + conteo[estado] + ")</span>" +
            "</label>"
          );
        })
        .join("");
    }

    if (sinCoordenadas.length) {
      var sitios = [];
      sinCoordenadas.forEach(function (d) {
        if (sitios.indexOf(d.sitio) === -1) sitios.push(d.sitio);
      });
      var lista = sitios.slice(0, 5).join(", ") + (sitios.length > 5 ? "…" : "");

      sinUbicacion.className = "alert alert-warning small mt-2 mb-0";
      sinUbicacion.textContent =
        sinCoordenadas.length +
        (sinCoordenadas.length === 1 ? " depósito no aparece" : " depósitos no aparecen") +
        " en el mapa porque la dirección de su sitio no tiene coordenadas (sitio: " + lista + "). " +
        "Edite el sitio para volver a ubicar su dirección; mientras tanto puede registrarle seguimiento desde la lista.";
    } else {
      sinUbicacion.className = "alert alert-warning small d-none mt-2 mb-0";
      sinUbicacion.textContent = "";
    }
  }

  function pintarMapa() {
    var conCoordenadas = [];
    var sinCoordenadas = [];

    depositos.forEach(function (d) {
      var c = coordenadas(d);
      if (c) conCoordenadas.push({ d: d, c: c });
      else sinCoordenadas.push(d);
    });

    var conteo = { positivo: 0, negativo: 0, sin: 0 };
    marcadores = {};

    if (map) {
      Object.keys(capas).forEach(function (estado) {
        capas[estado].clearLayers();
      });

      var posiciones = posicionesSeparadas(conCoordenadas);
      var limites = [];

      conCoordenadas.forEach(function (item) {
        var d = item.d;
        var estado = estadoMapa(d);
        var esSeleccionado = String(d.id_deposito) === String(seleccionadoId);
        var posicion = posiciones[d.id_deposito];

        var marker = L.marker(posicion, {
          icon: iconoMarcador(estado, esSeleccionado),
          zIndexOffset: esSeleccionado ? 1000 : 0,
          title: d.tipo_deposito
        }).bindPopup(popupHtml(d, estado));

        marker.addTo(capas[estado]);
        marcadores[d.id_deposito] = { marker: marker, estado: estado };
        conteo[estado] += 1;
        limites.push(posicion);
      });

      if (primerAjuste && limites.length) {
        map.fitBounds(limites, { padding: [30, 30], maxZoom: 16 });
        primerAjuste = false;
      }
    }

    pintarLeyenda(conteo, sinCoordenadas);
  }

  function marcarSeleccionado(id) {
    var previo = seleccionadoId;
    seleccionadoId = id == null || id === "" ? null : String(id);

    [previo, seleccionadoId].forEach(function (clave) {
      if (clave == null || !marcadores[clave]) return;
      var info = marcadores[clave];
      var esSeleccionado = clave === seleccionadoId;
      info.marker.setIcon(iconoMarcador(info.estado, esSeleccionado));
      info.marker.setZIndexOffset(esSeleccionado ? 1000 : 0);
    });
  }

  // ---------- Formulario ----------
  function fillSelect(select, opciones, placeholder, seleccionado) {
    select.innerHTML =
      '<option value="">' + escapeHtml(placeholder) + "</option>" +
      opciones
        .map(function (o) {
          return '<option value="' + escapeHtml(o.value) + '">' + escapeHtml(o.text) + "</option>";
        })
        .join("");

    select.value = seleccionado ? String(seleccionado) : "";
  }

  // "extra" = fila del historial cuyo depósito puede estar ya inhabilitado: al
  // editar se incluye igualmente para no perderlo.
  function llenarDepositos(seleccionado, extra) {
    var opciones = depositos.map(function (d) {
      return { value: d.id_deposito, text: etiquetaDeposito(d) };
    });

    if (extra && !buscarDeposito(extra.id_deposito)) {
      opciones.push({
        value: extra.id_deposito,
        text:
          extra.tipo_deposito + " — " + (extra.deposito_descripcion || "sin descripción") + " · " + extra.sitio +
          " (inhabilitado)"
      });
    }

    fillSelect(
      depositoSelect,
      opciones,
      opciones.length ? "Seleccione el depósito" : "No hay depósitos habilitados",
      seleccionado
    );
  }

  function llenarActividades(seleccionado, extra) {
    var opciones = actividades.map(function (a) {
      return { value: a.id_actividad, text: a.nombre };
    });

    if (extra && !opciones.some(function (o) { return String(o.value) === String(extra.id_actividad); })) {
      opciones.push({ value: extra.id_actividad, text: extra.actividad + " (inhabilitada)" });
    }

    fillSelect(
      actividadSelect,
      opciones,
      opciones.length ? "Seleccione la acción" : "No hay acciones de terreno habilitadas",
      seleccionado
    );
  }

  function alSeleccionarDeposito(abrirPopup) {
    var d = buscarDeposito(depositoSelect.value);
    direccion.value = d ? ubicacion(d) : "";
    marcarSeleccionado(d ? d.id_deposito : null);

    var info = d && marcadores[d.id_deposito];
    if (map && info) {
      map.setView(info.marker.getLatLng(), Math.max(map.getZoom(), 16));
      if (abrirPopup && map.hasLayer(info.marker)) info.marker.openPopup();
    }
  }

  function seleccionarDeposito(id, abrirPopup) {
    if (editandoId) salirDeEdicion();

    depositoSelect.value = String(id);
    if (depositoSelect.value !== String(id)) depositoSelect.value = "";

    alSeleccionarDeposito(abrirPopup);
  }

  function reiniciarFormulario() {
    form.reset();
    idSeguimiento.value = "";
    fecha.value = hoyLocal();
    direccion.value = "";
    observacionesCount.textContent = "0";
    marcarSeleccionado(null);
  }

  function entrarEnEdicion(id) {
    var fila = buscarHistorial(id);
    if (!fila) return;

    editandoId = String(id);
    clearFormMessage();
    form.reset();

    idSeguimiento.value = fila.id_seguimiento_deposito;
    llenarDepositos(fila.id_deposito, fila);
    depositoSelect.disabled = true; // el depósito y la fecha identifican el seguimiento: no se cambian
    fecha.value = fila.fecha;
    fecha.disabled = true;
    llenarActividades(fila.id_actividad, fila);
    larvasSelect.value = String(Number(fila.presencia_larvas));
    peces.value = fila.numero_peces_sembrados;
    observaciones.value = fila.observaciones || "";
    observacionesCount.textContent = observaciones.value.length;
    direccion.value = ubicacion(fila);
    marcarSeleccionado(fila.id_deposito);

    formTitulo.textContent = "Editar seguimiento #" + fila.id_seguimiento_deposito;
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i>Guardar cambios';
    btnCancelar.classList.remove("d-none");

    form.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  function salirDeEdicion() {
    editandoId = null;
    depositoSelect.disabled = false;
    fecha.disabled = false;

    reiniciarFormulario();
    llenarDepositos("");
    llenarActividades("");

    formTitulo.textContent = "Registrar seguimiento";
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
    btnCancelar.classList.add("d-none");
    clearFormMessage();
  }

  // ---------- Historial ----------
  function renderAcciones(f) {
    var activo = Number(f.estado) === 1;
    var p = permisos();

    return (
      '<div class="table-actions">' +
      '<button type="button" class="btn-icon" data-action="ver" data-id="' + f.id_seguimiento_deposito +
      '" title="Ver detalle"><i class="fas fa-eye"></i></button>' +
      (activo && p.editar
        ? '<button type="button" class="btn-icon" data-action="editar" data-id="' + f.id_seguimiento_deposito +
          '" title="Editar"><i class="fas fa-pen"></i></button>'
        : "") +
      (p.inhabilitar
        ? '<button type="button" class="btn-icon ' + (activo ? "text-danger" : "text-success") +
          '" data-action="estado" data-id="' + f.id_seguimiento_deposito +
          '" data-estado="' + (activo ? 0 : 1) +
          '" title="' + (activo ? "Inhabilitar" : "Habilitar") + '">' +
          '<i class="fas ' + (activo ? "fa-ban" : "fa-check-circle") + '"></i></button>'
        : "") +
      "</div>"
    );
  }

  function renderFila(f) {
    var activo = Number(f.estado) === 1;
    var estadoBadge = activo
      ? '<span class="badge-estado activo">Activo</span>'
      : '<span class="badge-estado inactivo">Inhabilitado</span>';

    return (
      "<tr>" +
      "<td>" + escapeHtml(f.fecha) + "</td>" +
      '<td><span class="fw-bold">' + escapeHtml(f.tipo_deposito) + "</span>" +
      '<div class="small text-muted">' + escapeHtml(f.deposito_descripcion) + "</div></td>" +
      "<td>" + escapeHtml(f.sitio) + '<div class="small text-muted">' + escapeHtml(ubicacion(f)) + "</div></td>" +
      "<td>" + escapeHtml(f.actividad) + "</td>" +
      "<td>" + escapeHtml(f.usuario) + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      '<td class="text-center">' + renderAcciones(f) + "</td>" +
      "</tr>"
    );
  }

  function filasFiltradas() {
    var q = search.value.trim().toLowerCase();
    var estadoFiltro = filtroEstado.value;

    return historial.filter(function (f) {
      var texto = [
        f.fecha, f.tipo_deposito, f.deposito_descripcion, f.sitio, f.direccion,
        f.barrio, f.comuna, f.actividad, f.usuario, f.observaciones
      ]
        .join(" ")
        .toLowerCase();

      var coincideTexto = !q || texto.indexOf(q) !== -1;
      var coincideEstado =
        estadoFiltro === "todos" ||
        (estadoFiltro === "activo" && Number(f.estado) === 1) ||
        (estadoFiltro === "inactivo" && Number(f.estado) === 0);

      return coincideTexto && coincideEstado;
    });
  }

  function renderHistorial() {
    var filas = filasFiltradas();

    tbody.innerHTML = filas.length
      ? filas.map(renderFila).join("")
      : '<tr><td colspan="7" class="text-center text-muted py-4">' +
        (historial.length
          ? "No hay seguimientos que coincidan con el filtro."
          : "Todavía no hay seguimientos registrados.") +
        "</td></tr>";

    count.textContent = filas.length + " de " + historial.length + " seguimientos";
  }

  function abrirDetalle(id) {
    var f = buscarHistorial(id);
    if (!f) return;

    document.getElementById("sdDetailBody").innerHTML =
      '<dl class="row mb-0">' +
      '<dt class="col-5">Fecha</dt><dd class="col-7">' + escapeHtml(f.fecha) + "</dd>" +
      '<dt class="col-5">Tipo de depósito</dt><dd class="col-7">' + escapeHtml(f.tipo_deposito) + "</dd>" +
      '<dt class="col-5">Descripción</dt><dd class="col-7">' + escapeHtml(f.deposito_descripcion || "—") + "</dd>" +
      '<dt class="col-5">Sitio</dt><dd class="col-7">' + escapeHtml(f.sitio) + "</dd>" +
      '<dt class="col-5">Ubicación</dt><dd class="col-7">' + escapeHtml(ubicacion(f)) + "</dd>" +
      '<dt class="col-5">Acción realizada</dt><dd class="col-7">' + escapeHtml(f.actividad) + "</dd>" +
      '<dt class="col-5">¿Larvas?</dt><dd class="col-7">' + (Number(f.presencia_larvas) === 1 ? "Sí" : "No") + "</dd>" +
      '<dt class="col-5">Peces sembrados</dt><dd class="col-7">' + Number(f.numero_peces_sembrados || 0) + "</dd>" +
      '<dt class="col-5">Observaciones</dt><dd class="col-7">' + escapeHtml(f.observaciones || "—") + "</dd>" +
      '<dt class="col-5">Responsable</dt><dd class="col-7">' + escapeHtml(f.usuario || "—") + "</dd>" +
      '<dt class="col-5">Registrado</dt><dd class="col-7">' + escapeHtml(f.registrado) + "</dd>" +
      '<dt class="col-5">Estado</dt><dd class="col-7">' + (Number(f.estado) === 1 ? "Activo" : "Inhabilitado") + "</dd>" +
      "</dl>";

    bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
  }

  async function cambiarEstado(id, estado) {
    var fila = buscarHistorial(id);
    var texto = estado === 1 ? "habilitar" : "inhabilitar";
    var etiqueta = fila ? ' del ' + fila.fecha + ' (' + fila.tipo_deposito + ' · ' + fila.sitio + ')' : "";

    if (!window.confirm("¿Desea " + texto + " el seguimiento" + etiqueta + "?")) return;

    try {
      var result = await postJson("postEstado", {
        id_seguimiento_deposito: Number(id),
        estado: Number(estado)
      });

      if (editandoId && String(editandoId) === String(id)) salirDeEdicion();
      showMessage(result.message, "success");
      await Promise.all([cargarHistorial(), cargarCatalogos().catch(function () {})]);
    } catch (error) {
      showMessage(error.message, "danger");
    }
  }

  // ---------- Carga de datos ----------
  async function cargarCatalogos() {
    var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=catalogos");

    depositos = result.data.depositos || [];
    actividades = result.data.actividades || [];

    // Si se está editando, los <select> ya tienen sus opciones especiales: no se tocan.
    if (!editandoId) {
      llenarDepositos(depositoSelect.value);
      llenarActividades(actividadSelect.value);
    }

    pintarMapa();
  }

  async function cargarHistorial() {
    try {
      var result = await getJson(AJAX_URL + "?" + MODULO + "&funcion=historial");
      historial = result.data || [];
      renderHistorial();
    } catch (error) {
      tbody.innerHTML =
        '<tr><td colspan="7" class="text-center text-danger py-4">' + escapeHtml(error.message) + "</td></tr>";
    }
  }

  // ---------- Eventos ----------
  depositoSelect.addEventListener("change", function () {
    alSeleccionarDeposito(true);
  });

  observaciones.addEventListener("input", function () {
    observacionesCount.textContent = this.value.length;
  });

  btnCancelar.addEventListener("click", salirDeEdicion);

  search.addEventListener("input", renderHistorial);
  filtroEstado.addEventListener("change", renderHistorial);

  // Mostrar / ocultar capas del mapa desde la leyenda.
  leyenda.addEventListener("change", function (event) {
    var check = event.target.closest("[data-capa]");
    if (!check || !map) return;

    var capa = capas[check.getAttribute("data-capa")];
    if (!capa) return;

    if (check.checked) map.addLayer(capa);
    else map.removeLayer(capa);
  });

  tbody.addEventListener("click", function (event) {
    var boton = event.target.closest("[data-action]");
    if (!boton) return;

    var accion = boton.getAttribute("data-action");
    var id = boton.getAttribute("data-id");

    if (accion === "ver") abrirDetalle(id);
    else if (accion === "editar") entrarEnEdicion(id);
    else if (accion === "estado") cambiarEstado(id, Number(boton.getAttribute("data-estado")));
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    clearFormMessage();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var editando = !!editandoId;

    var payload = {
      id_actividad: Number(actividadSelect.value),
      presencia_larvas: Number(larvasSelect.value),
      numero_peces_sembrados: Number(peces.value || 0),
      observaciones: observaciones.value.trim()
    };

    if (editando) {
      payload.id_seguimiento_deposito = Number(idSeguimiento.value);
    } else {
      payload.id_deposito = Number(depositoSelect.value);
      payload.fecha = fecha.value;
    }

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    try {
      var result = await postJson(editando ? "postUpdate" : "postCreate", payload);

      if (editando) salirDeEdicion();
      else reiniciarFormulario();

      showMessage(result.message, "success");
      await Promise.all([cargarHistorial(), cargarCatalogos().catch(function () {})]);
    } catch (error) {
      showFormMessage(error.message, "danger");
    } finally {
      btnGuardar.disabled = false;
      btnGuardar.innerHTML =
        '<i class="fas fa-save me-1"></i>' + (editandoId ? "Guardar cambios" : "Guardar");
    }
  });

  // ---------- Arranque ----------
  var hoy = hoyLocal();
  fecha.value = hoy;
  fecha.min = hoy; // el backend exige la fecha actual: se bloquea aquí también
  fecha.max = hoy;

  // ---------- Aplicar permisos reales a la interfaz ----------
  function aplicarPermisosUI() {
    var p = permisos();

    if (!p.crear) {
      Array.prototype.forEach.call(form.elements, function (el) { el.disabled = true; });
      btnGuardar.disabled = true;
      btnGuardar.title = "No tienes permiso para registrar seguimientos.";
      showFormMessage("Tu rol no tiene permiso para registrar seguimientos de depósito. Puedes consultar el historial abajo.", "warning");
    }
  }
  aplicarPermisosUI();

  iniciarMapa();

  cargarCatalogos().catch(function (error) {
    leyenda.innerHTML = '<span class="text-danger small">' + escapeHtml(error.message) + "</span>";
    showFormMessage(error.message, "danger");
  });
  cargarHistorial();
})();