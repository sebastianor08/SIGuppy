(function () {
  "use strict";

  var botonPdf = document.querySelector(".btn-reportes");
  if (!botonPdf) return; // solo actúa en las vistas de Reportes

  // SheetJS pesa ~900 KB: se carga solo cuando el usuario elige "Excel sencillo".
  var carpetaJs = document.currentScript.src.replace(/siguppys-exportar-excel\.js.*$/, "");
  var RUTA_SHEETJS = carpetaJs + "plugin/sheetjs/xlsx.full.min.js";

  // ---------- Botón "Exportar a Excel" (al lado de "Generar Reportes") ----------
  var botonExcel = document.createElement("button");
  botonExcel.type = "button";
  botonExcel.className = "btn-excel";
  botonExcel.innerHTML = '<i class="fas fa-file-excel me-1"></i> Exportar a Excel';
  botonPdf.insertAdjacentElement("afterend", botonExcel);

  // ---------- Modal ----------
  document.body.insertAdjacentHTML(
    "beforeend",
    '<div class="modal fade" id="modalExportarExcel" tabindex="-1" aria-hidden="true">' +
      '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
      '<div class="modal-header">' +
      '<h5 class="modal-title">Exportar a Excel</h5>' +
      '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>' +
      "</div>" +
      '<div class="modal-body">' +
      '<p class="mb-3">¿Qué desea?</p>' +
      '<div class="d-grid gap-2">' +
      '<button type="button" class="btn btn-outline-primary text-start" id="btnExcelSencillo">' +
      "<strong>Excel sencillo</strong><br>" +
      "<small>Las tablas tal como se ven en pantalla, sin estilo ni graficas, para informacion general.</small>" +
      "</button>" +
      '<button type="button" class="btn btn-outline-success text-start" id="btnExcelCompleto">' +
      "<strong>Excel completo</strong><br>" +
      "<small>Todos los datos con los filtros aplicados, con resumen y formato.</small>" +
      "</button>" +
      "</div></div></div></div></div>"
  );

  var modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("modalExportarExcel")
  );

  botonExcel.addEventListener("click", function () {
    modal.show();
  });

  document.getElementById("btnExcelSencillo").addEventListener("click", function () {
    modal.hide();
    cargarSheetJS(excelSencillo);
  });

  document.getElementById("btnExcelCompleto").addEventListener("click", function () {
    modal.hide();
    excelCompleto();
  });

  // ---------- Utilidades ----------
  function nombreArchivo(tipo) {
    var pagina = document.body.getAttribute("data-page") || "reporte";
    var hoy = new Date();
    var fecha =
      hoy.getFullYear() + "-" +
      String(hoy.getMonth() + 1).padStart(2, "0") + "-" +
      String(hoy.getDate()).padStart(2, "0"); // fecha local, no UTC
    return "reporte_" + pagina + "_" + tipo + "_" + fecha + ".xlsx";
  }

  function cargarSheetJS(despues) {
    if (window.XLSX) return despues();
    var s = document.createElement("script");
    s.src = RUTA_SHEETJS;
    s.onload = despues;
    s.onerror = function () {
      alert("No se pudo cargar la librería de Excel (SheetJS).");
    };
    document.head.appendChild(s);
  }

  // Quita una columna (ej. "Acciones", que solo trae un ícono) de una copia de la tabla.
  function quitarColumna(tabla, encabezado) {
    var indice = -1;
    tabla.querySelectorAll("thead th").forEach(function (th, i) {
      if (th.textContent.trim() === encabezado) indice = i;
    });
    if (indice === -1) return;
    tabla.querySelectorAll("tr").forEach(function (tr) {
      var celda = tr.children[indice];
      if (celda && !celda.hasAttribute("colspan")) tr.removeChild(celda);
    });
  }

  // ---------- Excel sencillo (navegador) ----------
  function excelSencillo() {
    var tablas = document.querySelectorAll(".caja table");
    if (!tablas.length) {
      alert("No hay tablas para exportar.");
      return;
    }
    var libro = XLSX.utils.book_new();
    tablas.forEach(function (tabla, i) {
      var copia = tabla.cloneNode(true);
      quitarColumna(copia, "Acciones");
      XLSX.utils.book_append_sheet(libro, XLSX.utils.table_to_sheet(copia), "Hoja " + (i + 1));
    });
    XLSX.writeFile(libro, nombreArchivo("sencillo"));
  }

  // ---------- Excel completo (servidor) ----------
  function excelCompleto() {
    var pagina = document.body.getAttribute("data-page");
    var filtros = window.location.search.replace(/^\?/, ""); // los filtros ya van en la URL (GET)
    window.location.href =
      "../../Web/ajax.php?modulo=ExportarExcel&controlador=ExportarExcel&funcion=descargar" +
      "&reporte=" + encodeURIComponent(pagina) +
      (filtros ? "&" + filtros : "");
  }
})();
