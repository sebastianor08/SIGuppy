(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "resumen") return;

  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function renderFila(z) {
    var comunaBarrio = [z.barrio, z.comuna].filter(Boolean).join(" · ");
    var estadoBadge =
      Number(z.estado) === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inhabilitado</span>';
    return (
      "<tr>" +
      "<td>" + escapeHtml(z.nombre) + "</td>" +
      "<td>" + escapeHtml(comunaBarrio) + "</td>" +
      '<td class="text-center">' + estadoBadge + "</td>" +
      "</tr>"
    );
  }

  fetch("ajax.php?modulo=Dashboard&controlador=Dashboard&funcion=resumen", {
    headers: { Accept: "application/json" },
  })
    .then(function (r) { return r.json(); })
    .then(function (result) {
      if (!result || result.ok === false) {
        throw new Error((result && result.message) || "No se pudo cargar el resumen.");
      }
      var d = result.data || {};

      var elActivos = document.getElementById("kpiZoocriaderosActivos");
      var elSeguimientos = document.getElementById("kpiSeguimientosMes");
      var elDepositos = document.getElementById("kpiDepositosInspeccionados");
      if (elActivos) elActivos.textContent = d.zoocriaderos_activos ?? 0;
      if (elSeguimientos) elSeguimientos.textContent = d.seguimientos_mes ?? 0;
      if (elDepositos) elDepositos.textContent = d.depositos_inspeccionados ?? 0;

      var tbody = document.getElementById("resumenZoocriaderosBody");
      if (!tbody) return;
      var recientes = d.recientes || [];
      tbody.innerHTML = recientes.length
        ? recientes.map(renderFila).join("")
        : '<tr class="sig-empty-row"><td colspan="3">Todavía no hay seguimientos registrados.</td></tr>';
    })
    .catch(function (error) {
      ["kpiZoocriaderosActivos", "kpiSeguimientosMes", "kpiDepositosInspeccionados"].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.textContent = "—";
      });
      var tbody = document.getElementById("resumenZoocriaderosBody");
      if (tbody) {
        tbody.innerHTML =
          '<tr class="sig-empty-row"><td colspan="3" class="text-danger">' +
          escapeHtml(error.message) + "</td></tr>";
      }
    });
})();
