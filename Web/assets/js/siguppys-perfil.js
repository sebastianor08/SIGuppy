(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "perfil") return;

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Perfil&controlador=Perfil";

  var cargando = document.getElementById("perfilCargando");
  var form = document.getElementById("perfilForm");
  var message = document.getElementById("perfilMessage");
  var submitBtn = document.getElementById("perfilSubmitBtn");
  var correoInput = document.getElementById("perfilCorreo");

  function showMessage(text, type) {
    message.className = "alert mb-3 alert-" + type;
    message.textContent = text;
  }
  function clearMessage() {
    message.className = "alert d-none mb-3";
    message.textContent = "";
  }

  function fmtFecha(iso) {
    if (!iso) return "—";
    var d = new Date(iso + "T00:00:00");
    if (isNaN(d)) return iso;
    return d.toLocaleDateString("es-CO", { day: "2-digit", month: "long", year: "numeric" });
  }

  // Misma regla que en Usuarios (lib/validaciones.php validarCorreo):
  // solo letras, números, punto, guion y guion bajo antes de la @, y
  // dominio @cali.gov.co o @gmail.com. Esto solo avisa antes de tiempo;
  // el servidor vuelve a validar de verdad.
  var DOMINIOS_PERMITIDOS = ["cali.gov.co", "gmail.com"];
  var REGEX_CORREO_ESTRICTO = /^[a-z0-9]+(?:[._-][a-z0-9]+)*@[a-z0-9]+(?:[.-][a-z0-9]+)*\.[a-z]{2,}$/;

  function validarCorreo(valor) {
    var correo = String(valor || "").replace(/\u00a0/g, " ").trim().toLowerCase();
    if (!correo) return "El correo electrónico es obligatorio.";
    if (/\s/.test(correo)) return "El correo electrónico no puede contener espacios.";
    if (!REGEX_CORREO_ESTRICTO.test(correo)) {
      return "El correo electrónico no es válido. Solo se permiten letras, números, puntos, guiones y guion bajo antes de la @.";
    }
    var dominio = correo.split("@").pop();
    if (DOMINIOS_PERMITIDOS.indexOf(dominio) === -1) {
      return "El correo debe ser de uno de estos dominios: " + DOMINIOS_PERMITIDOS.join(", ") + ".";
    }
    return null;
  }

  async function cargarPerfil() {
    var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=datos", {
      headers: { Accept: "application/json" },
    });
    var result = await response.json().catch(function () { return null; });
    if (!response.ok || !result || result.ok === false) {
      throw new Error((result && result.message) || "No fue posible cargar tu perfil.");
    }
    var u = result.data;

    document.getElementById("perfilNombre").value = u.nombre || "";
    document.getElementById("perfilApellido").value = u.apellido || "";
    document.getElementById("perfilTipoDocumento").value = u.tipo_documento || "";
    document.getElementById("perfilDocumento").value = u.documento || "";
    document.getElementById("perfilRol").value = u.nombre_rol || "";
    document.getElementById("perfilCreadoEn").value = fmtFecha(u.creado_en);
    correoInput.value = u.correo || "";

    cargando.classList.add("d-none");
    form.classList.remove("d-none");
  }

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    clearMessage();

    var correo = correoInput.value.trim();
    var error = validarCorreo(correo);
    if (error) {
      showMessage(error, "danger");
      correoInput.focus();
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    try {
      var response = await fetch(AJAX_URL + "?" + MODULO + "&funcion=postActualizarCorreo", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ correo: correo }),
      });
      var result = await response.json().catch(function () { return null; });
      if (!response.ok || !result || result.ok === false) {
        throw new Error((result && result.message) || "No fue posible actualizar el correo.");
      }
      correoInput.value = result.correo || correo;
      showMessage(result.message, "success");
    } catch (error2) {
      showMessage(error2.message, "danger");
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar correo';
    }
  });

  cargarPerfil().catch(function (error) {
    cargando.textContent = "No fue posible cargar tu perfil.";
    showMessage(error.message, "danger");
  });
})();
