(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "resumen") return;

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

  // Lista de tanques que se muestra dentro del popup de un zoocriadero al
  // seleccionarlo en el mapa (los depósitos no tienen tanques, por eso
  // solo se llama con p.categoria === "zoocriadero").
  function renderTanques(tanques) {
    if (!tanques || !tanques.length) {
      return '<hr class="my-1"><span class="text-muted" style="font-size:12px;">Sin tanques registrados.</span>';
    }
    var filas = tanques
      .map(function (t) {
        var estadoTexto = Number(t.estado) === 1 ? "Activo" : "Inhabilitado";
        var estadoColor = Number(t.estado) === 1 ? "#2e7d32" : "#9e9e9e";
        return (
          '<li style="margin-bottom:2px;">' +
          "<strong>" + escapeHtml(t.nombre) + "</strong> — " + escapeHtml(t.tipo) +
          ' <span style="color:' + estadoColor + ';font-size:11px;">(' + estadoTexto + ")</span>" +
          "</li>"
        );
      })
      .join("");
    return (
      '<hr class="my-1">' +
      '<div style="font-size:12px;"><strong>Tanques (' + tanques.length + ')</strong>' +
      '<ul style="padding-left:16px;margin:4px 0 0;">' + filas + "</ul></div>"
    );
  }

  // Varios depósitos de un mismo sitio comparten coordenadas y se taparían entre sí:
  // se reparten en un pequeño círculo (~10 m) para que todos se vean y se puedan abrir.
  function separarSuperpuestos(puntos) {
    var grupos = {};
    puntos.forEach(function (p, i) {
      var clave = p.lat.toFixed(6) + "," + p.lng.toFixed(6);
      (grupos[clave] = grupos[clave] || []).push(i);
    });

    var posiciones = [];
    Object.keys(grupos).forEach(function (clave) {
      var indices = grupos[clave];
      var radio = 0.00008 + 0.00002 * indices.length; // grados (~9 m + 2 m por punto)

      indices.forEach(function (idx, k) {
        var p = puntos[idx];
        if (indices.length === 1) {
          posiciones[idx] = [p.lat, p.lng];
          return;
        }
        var angulo = (2 * Math.PI * k) / indices.length;
        posiciones[idx] = [p.lat + radio * Math.sin(angulo), p.lng + radio * Math.cos(angulo)];
      });
    });
    return posiciones;
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
      var posiciones = separarSuperpuestos(puntos);

      puntos.forEach(function (p, i) {
        var color = colorPara(p.tipo);
        if (!capas[p.tipo]) capas[p.tipo] = L.layerGroup().addTo(map);

        var popup =
          "<strong>" + escapeHtml(p.nombre) + "</strong><br>" +
          "<span>" + (p.categoria === "zoocriadero" ? "Zoocriadero" : "Depósito · " + escapeHtml(p.tipo)) + "</span><br>" +
          (p.sitio ? "Sitio: " + escapeHtml(p.sitio) + "<br>" : "") +
          escapeHtml(p.direccion || "") +
          (p.barrio ? "<br>" + escapeHtml(p.barrio) : "") +
          (p.comuna ? " · " + escapeHtml(p.comuna) : "") +
          (p.categoria === "zoocriadero" ? renderTanques(p.tanques) : "");

        L.marker(posiciones[i], { icon: iconoColor(color) }).bindPopup(popup, { maxWidth: 260 }).addTo(capas[p.tipo]);
        bounds.push(posiciones[i]);
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