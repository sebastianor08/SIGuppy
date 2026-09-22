/* =========================================================
   SIGuppys — validación genérica de rangos de fecha
   =========================================================
   Cualquier formulario que tenga un input name="fecha_inicio" y
   otro name="fecha_fin" (los filtros de Auditoría y de los
   Reportes, por ejemplo) queda cubierto automáticamente: no hace
   falta repetir esta lógica en cada módulo ni tocar este archivo
   cuando se agregue un nuevo formulario con un rango de fechas.
   ========================================================= */
(function () {
  "use strict";

  function mensajeError(input) {
    var contenedor = document.createElement("div");
    contenedor.className = "invalid-feedback sig-rango-fechas-error d-block";
    contenedor.textContent = 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".';
    input.insertAdjacentElement("afterend", contenedor);
  }

  function limpiarError(form) {
    form.querySelectorAll(".sig-rango-fechas-error").forEach(function (el) { el.remove(); });
    form.querySelectorAll(".sig-rango-fechas-invalido").forEach(function (el) {
      el.classList.remove("is-invalid", "sig-rango-fechas-invalido");
    });
  }

  function esRangoInvalido(inicio, fin) {
    return !!(inicio.value && fin.value && inicio.value > fin.value);
  }

  function sincronizarLimites(inicio, fin) {
    // Además de validar al enviar, se acota lo que el propio selector de
    // fecha del navegador permite elegir: si ya hay una fecha de inicio,
    // "Hasta" no deja elegir un día anterior, y viceversa.
    if (inicio.value) {
      fin.min = inicio.value;
    } else {
      fin.removeAttribute("min");
    }
    if (fin.value) {
      inicio.max = fin.value;
    } else {
      inicio.removeAttribute("max");
    }
  }

  function inicializarPar(inicio, fin) {
    var form = inicio.form;
    sincronizarLimites(inicio, fin);

    [inicio, fin].forEach(function (input) {
      input.addEventListener("change", function () {
        sincronizarLimites(inicio, fin);
        limpiarError(form);
      });
    });

    if (form && !form.__sigRangoFechasEnganchado) {
      form.__sigRangoFechasEnganchado = true;
      form.addEventListener("submit", function (ev) {
        limpiarError(form);
        if (esRangoInvalido(inicio, fin)) {
          ev.preventDefault();
          inicio.classList.add("is-invalid", "sig-rango-fechas-invalido");
          fin.classList.add("is-invalid", "sig-rango-fechas-invalido");
          mensajeError(fin);
          fin.focus();
        }
      });
    }
  }

  function iniciar() {
    var inicios = document.querySelectorAll('input[name="fecha_inicio"]');
    inicios.forEach(function (inicio) {
      var form = inicio.form;
      if (!form) return;
      var fin = form.querySelector('input[name="fecha_fin"]');
      if (!fin) return;
      inicializarPar(inicio, fin);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciar);
  } else {
    iniciar();
  }
})();
