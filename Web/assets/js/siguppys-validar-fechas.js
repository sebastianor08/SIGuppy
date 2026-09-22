(function () {
  "use strict";

  // Se activa en cualquier formulario que tenga "Fecha Inicio" y "Fecha Fin"
  // (los filtros de los reportes). Si una página no tiene esos dos campos,
  // no hace nada.
  document.querySelectorAll("form").forEach(function (form) {
    var fechaInicio = form.querySelector('input[name="fecha_inicio"]');
    var fechaFin = form.querySelector('input[name="fecha_fin"]');
    if (!fechaInicio || !fechaFin) return;

    // El valor de un <input type="date"> siempre viene como "AAAA-MM-DD",
    // así que compararlo como texto ya da el orden cronológico correcto.
    function validar() {
      if (fechaInicio.value && fechaFin.value && fechaInicio.value > fechaFin.value) {
        fechaFin.setCustomValidity("La Fecha Fin no puede ser anterior a la Fecha Inicio.");
      } else {
        fechaFin.setCustomValidity("");
      }
    }

    fechaInicio.addEventListener("change", validar);
    fechaFin.addEventListener("change", validar);
    validar(); // por si el navegador ya trae fechas cargadas al abrir la página

    form.addEventListener("submit", function (event) {
      validar();
      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity(); // muestra el globo de aviso nativo sobre el campo
      }
    });
  });
})();
