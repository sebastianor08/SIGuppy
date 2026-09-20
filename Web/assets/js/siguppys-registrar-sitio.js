(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "registrar-sitio") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=RegistrarSitio&controlador=RegistrarSitio";

  var form = document.getElementById("registrarSitioForm");
  var idSitio = document.getElementById("id_sitio");
  var titulo = document.getElementById("sitioFormTitulo");

  var departamento = document.getElementById("id_departamento");
  var ciudad = document.getElementById("id_ciudad");
  var comuna = document.getElementById("id_comuna");
  var barrio = document.getElementById("id_barrio");
  var nomenclatura = document.getElementById("id_nomenclatura");
  var numeroDireccion = document.getElementById("numero_direccion");

  var nombre = document.getElementById("nombre");
  var descripcion = document.getElementById("descripcion");
  var descripcionCount = document.getElementById("descripcionCount");
  var message = document.getElementById("registrarSitioMessage");
  var guardar = document.getElementById("btnGuardarSitio");

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
    message.className = "alert mb-4 alert-" + type;
    message.textContent = text;
  }

  function clearMessage() {
    message.className = "alert d-none mb-4";
    message.textContent = "";
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

  function fillSelect(select, items, valueKey, textKey, placeholder) {
    select.innerHTML =
      '<option value="">' +
      escapeHtml(placeholder) +
      "</option>" +
      (items || [])
        .map(function (item) {
          return (
            '<option value="' +
            escapeHtml(item[valueKey]) +
            '">' +
            escapeHtml(item[textKey]) +
            "</option>"
          );
        })
        .join("");
  }

  function bloquear(select, texto) {
    select.disabled = true;
    select.innerHTML = '<option value="">' + escapeHtml(texto) + "</option>";
  }

  async function cargarCatalogos() {
    var result = await getJson(
      AJAX_URL + "?" + MODULO + "&funcion=catalogos"
    );

    fillSelect(
      departamento,
      result.data.departamentos,
      "id_departamento",
      "nombre",
      "Seleccione el departamento"
    );

    fillSelect(
      nomenclatura,
      result.data.nomenclaturas,
      "id_nomenclatura",
      "nomenclatura",
      "Seleccione la nomenclatura"
    );
  }

  async function cargarCiudades(idDepartamento, seleccionado) {
    bloquear(ciudad, "Cargando ciudades...");
    bloquear(comuna, "Seleccione primero la ciudad");
    bloquear(barrio, "Seleccione primero la comuna");

    if (!idDepartamento) {
      bloquear(ciudad, "Seleccione primero el departamento");
      return;
    }

    var result = await getJson(
      AJAX_URL +
        "?" +
        MODULO +
        "&funcion=ciudades&id_departamento=" +
        encodeURIComponent(idDepartamento)
    );

    fillSelect(
      ciudad,
      result.data,
      "id_ciudad",
      "nombre",
      "Seleccione la ciudad"
    );

    ciudad.disabled = result.data.length === 0;

    if (seleccionado) {
      ciudad.value = seleccionado;
    }
  }

  async function cargarComunas(idCiudad, seleccionado) {
    bloquear(comuna, "Cargando comunas...");
    bloquear(barrio, "Seleccione primero la comuna");

    if (!idCiudad) {
      bloquear(comuna, "Seleccione primero la ciudad");
      return;
    }

    var result = await getJson(
      AJAX_URL +
        "?" +
        MODULO +
        "&funcion=comunas&id_ciudad=" +
        encodeURIComponent(idCiudad)
    );

    fillSelect(
      comuna,
      result.data,
      "id_comuna",
      "nombre",
      "Seleccione la comuna"
    );

    comuna.disabled = result.data.length === 0;

    if (seleccionado) {
      comuna.value = seleccionado;
    }
  }

  async function cargarBarrios(idComuna, seleccionado) {
    bloquear(barrio, "Cargando barrios...");

    if (!idComuna) {
      bloquear(barrio, "Seleccione primero la comuna");
      return;
    }

    var result = await getJson(
      AJAX_URL +
        "?" +
        MODULO +
        "&funcion=barrios&id_comuna=" +
        encodeURIComponent(idComuna)
    );

    fillSelect(
      barrio,
      result.data,
      "id_barrio",
      "nombre",
      "Seleccione el barrio"
    );

    barrio.disabled = result.data.length === 0;

    if (seleccionado) {
      barrio.value = seleccionado;
    }
  }

  async function cargarEdicion(id) {
    var result = await getJson(
      AJAX_URL +
        "?" +
        MODULO +
        "&funcion=buscar&id_sitio=" +
        encodeURIComponent(id)
    );

    var sitio = result.data;

    idSitio.value = sitio.id_sitio;
    nombre.value = sitio.nombre || "";
    descripcion.value = sitio.descripcion || "";
    descripcionCount.textContent = descripcion.value.length;

    departamento.value = sitio.id_departamento;
    await cargarCiudades(sitio.id_departamento, sitio.id_ciudad);
    await cargarComunas(sitio.id_ciudad, sitio.id_comuna);
    await cargarBarrios(sitio.id_comuna, sitio.id_barrio);

    nomenclatura.value = sitio.id_nomenclatura;

    var prefijo = "";
    // La columna direccion guarda "Calle 12 Oeste # 4-20".
    // Para editar se quita la nomenclatura que ya está almacenada
    // en el select y se deja al usuario solamente el resto.
    var direccionCompleta = String(sitio.direccion || "");
    var nomenclaturaTexto =
      nomenclatura.options[nomenclatura.selectedIndex]
        ? nomenclatura.options[nomenclatura.selectedIndex].text
        : "";

    prefijo = nomenclaturaTexto
      ? new RegExp("^" + nomenclaturaTexto.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "\\s*", "i")
      : null;

    numeroDireccion.value = prefijo
      ? direccionCompleta.replace(prefijo, "")
      : direccionCompleta;

    titulo.textContent = "Editar Sitio";
    guardar.innerHTML =
      '<i class="fas fa-save me-1"></i>Guardar cambios';
  }

  departamento.addEventListener("change", async function () {
    clearMessage();

    try {
      await cargarCiudades(this.value);
    } catch (error) {
      showMessage(error.message, "danger");
    }
  });

  ciudad.addEventListener("change", async function () {
    clearMessage();

    try {
      await cargarComunas(this.value);
    } catch (error) {
      showMessage(error.message, "danger");
    }
  });

  comuna.addEventListener("change", async function () {
    clearMessage();

    try {
      await cargarBarrios(this.value);
    } catch (error) {
      showMessage(error.message, "danger");
    }
  });

  descripcion.addEventListener("input", function () {
    descripcionCount.textContent = this.value.length;
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();
    clearMessage();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    guardar.disabled = true;
    guardar.innerHTML =
      '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    var payload = {
      nombre: nombre.value.trim(),
      descripcion: descripcion.value.trim(),
      id_departamento: Number(departamento.value),
      id_ciudad: Number(ciudad.value),
      id_comuna: Number(comuna.value),
      id_barrio: Number(barrio.value),
      id_nomenclatura: Number(nomenclatura.value),
      numero_direccion: numeroDireccion.value.trim()
    };

    var editando = idSitio.value !== "";

    if (editando) {
      payload.id_sitio = Number(idSitio.value);
    }

    var funcion = editando ? "postUpdate" : "postCreate";

    try {
      var response = await fetch(
        AJAX_URL + "?" + MODULO + "&funcion=" + funcion,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json"
          },
          body: JSON.stringify(payload)
        }
      );

      var result = await response.json().catch(function () {
        return null;
      });

      if (!response.ok || !result || result.ok === false) {
        throw new Error(
          (result && result.message) || "No fue posible guardar el sitio."
        );
      }

      window.location.href = "../Sitio/SitioView.php";
    } catch (error) {
      showMessage(error.message, "danger");
      guardar.disabled = false;
      guardar.innerHTML = editando
        ? '<i class="fas fa-save me-1"></i>Guardar cambios'
        : '<i class="fas fa-save me-1"></i>Guardar Sitio';
    }
  });

  (async function init() {
    try {
      await cargarCatalogos();

      var params = new URLSearchParams(window.location.search);
      var id = params.get("id_sitio");

      if (id) {
        await cargarEdicion(id);
      }
    } catch (error) {
      showMessage(error.message, "danger");
    }
  })();
})();