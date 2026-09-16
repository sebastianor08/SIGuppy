(function () {
  "use strict";

  // Este script se incluye de forma global (ver partials/footer.php), pero
  // solo hace algo en las páginas de Reportes: las que tienen el título
  // "<h2 class='titulo-pagina'>". En cualquier otra página no hace nada.
  var titulo = document.querySelector("h2.titulo-pagina");
  if (!titulo) return;

  var boton = document.createElement("button");
  boton.type = "button";
  boton.className = "btn btn-outline-primary btn-round sig-btn-exportar-pdf";
  boton.innerHTML = '<i class="fas fa-file-pdf me-1"></i> Exportar a PDF';

  boton.addEventListener("click", function () {
    // No se genera el PDF en el servidor: se usa el diálogo de impresión
    // del propio navegador (Guardar como PDF), que ya deja el resultado
    // limpio gracias a las reglas @media print de siguppys.css. Así el
    // botón funciona siempre, sin depender de ninguna librería externa
    // ni de conexión a internet.
    window.print();
  });

  titulo.insertAdjacentElement("afterend", boton);
})();
