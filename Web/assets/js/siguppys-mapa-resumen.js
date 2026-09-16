(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "inicio") return;

  var mapaEl = document.getElementById("mapaResumen");
  if (!mapaEl) return;

  var COLORES = ["#2e7d32", "#e53935", "#1e88e5", "#fb8c00", "#8e24aa", "#00897b", "#6d4c41", "#c0ca33"];
  var coloresPorTipo = {};
  var capas = {}; // tipo -> L.layerGroup

  function colorPara(tipo) {
    if (!coloresPorTipo[tipo]) {
      var usados = Object.keys(coloresPorTipo).length;
      coloresPorTipo[tipo] = COLORES[usados % COLORES.length];
    }
    return coloresPorTipo[tipo];
  }

  function iconoColor(color) {
    return L.divIcon({
      className: "",
      html: '<span style="background:' + color + ';width:16px;height:16px;border-radius:50%;display:block;border:2px solid #fff;box-shadow:0 0 3px rgba(0,0,0,.5);"></span>',
      iconSize: [16, 16],
      iconAnchor: [8, 8]
    });
  }

  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  var map = L.map("mapaResumen").setView([3.4372, -76.5225], 12); // Santiago de Cali

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);

  var filtrosEl = document.getElementById("mapaFiltros");

  fetch("ajax.php?modulo=Mapa&controlador=Mapa&funcion=puntos", { headers: { Accept: "application/json" } })
    .then(function (r) { return r.json(); })
    .then(function (result) {
      if (!result || result.ok === false) throw new Error((result && result.message) || "No se pudo cargar el mapa.");

      var puntos = result.data || [];
      if (!puntos.length) {
        filtrosEl.innerHTML = '<span class="text-muted small">Aún no hay zoocriaderos ni depósitos con coordenadas registradas.</span>';
        return;
      }

      var bounds = [];

      puntos.forEach(function (p) {
        var color = colorPara(p.tipo);
        if (!capas[p.tipo]) capas[p.tipo] = L.layerGroup().addTo(map);

        var popup =
          "<strong>" + escapeHtml(p.nombre) + "</strong><br>" +
          "<span>" + (p.categoria === "zoocriadero" ? "Zoocriadero" : "Depósito/Sitio · " + escapeHtml(p.tipo)) + "</span><br>" +
          escapeHtml(p.direccion || "") +
          (p.barrio ? "<br>" + escapeHtml(p.barrio) : "") +
          (p.comuna ? " · " + escapeHtml(p.comuna) : "");

        L.marker([p.lat, p.lng], { icon: iconoColor(color) }).bindPopup(popup).addTo(capas[p.tipo]);
        bounds.push([p.lat, p.lng]);
      });

      if (bounds.length) map.fitBounds(bounds, { padding: [20, 20], maxZoom: 15 });

      filtrosEl.innerHTML = Object.keys(capas).map(function (tipo) {
        var color = coloresPorTipo[tipo];
        return (
          '<label class="form-check form-check-inline" style="margin-right:12px;">' +
          '<input type="checkbox" class="form-check-input" checked data-tipo="' + escapeHtml(tipo) + '">' +
          '<span class="form-check-label"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + color + ';margin-right:4px;"></span>' + escapeHtml(tipo) + "</span>" +
          "</label>"
        );
      }).join("");

      filtrosEl.querySelectorAll("input[data-tipo]").forEach(function (chk) {
        chk.addEventListener("change", function () {
          var tipo = this.getAttribute("data-tipo");
          if (this.checked) map.addLayer(capas[tipo]);
          else map.removeLayer(capas[tipo]);
        });
      });
    })
    .catch(function (error) {
      filtrosEl.innerHTML = '<span class="text-danger small">' + escapeHtml(error.message) + "</span>";
    });
})();