/* =========================================================
   SIGuppys — navegación activa + selector de rol (demo)
   =========================================================
   No hay backend todavía, así que el rol de sesión se simula
   aquí con localStorage y un selector en la barra superior.
   Cuando exista login real, reemplazar getRole()/setRole() por
   el rol que venga de la sesión del servidor.
   ========================================================= */
(function () {
  "use strict";

  var ROLES = {
    auxiliar: { label: "Auxiliar de campo" },
    coordinador: { label: "Coordinador" },
  };

  function getRole() {
    var r = null;
    try {
      r = localStorage.getItem("siguppys_role");
    } catch (e) {}
    return ROLES[r] ? r : "auxiliar";
  }

  function applyRole(role) {
    document.body.setAttribute("data-role", role);
    var badge = document.getElementById("sigRoleBadge");
    if (badge) badge.textContent = ROLES[role].label;
    var select = document.getElementById("sigRoleSelect");
    if (select) select.value = role;
  }

  function setRole(role) {
    if (!ROLES[role]) return;
    try {
      localStorage.setItem("siguppys_role", role);
    } catch (e) {}
    applyRole(role);
    document.dispatchEvent(
      new CustomEvent("siguppys:role-changed", { detail: { role: role } })
    );
  }

  function initRoleSwitcher() {
    applyRole(getRole());
    var select = document.getElementById("sigRoleSelect");
    if (select) {
      select.addEventListener("change", function () {
        setRole(this.value);
      });
    }
  }

  // Marca como activo (pastilla azul) SOLO el módulo/submódulo de la
  // página actual. La página declara cuál es con <body data-page="...">
  // y el link correspondiente en el sidebar lleva el mismo data-page.
  // Nada de esto se escribe a mano por página: por eso "Tipo Depósitos"
  // ya no queda azul en todas las pantallas, solo cuando corresponde.
  function highlightActiveNav() {
    var page = document.body.getAttribute("data-page");
    if (!page) return;
    var sidebar = document.querySelector(".siguppys-sidebar");
    if (!sidebar) return;
    var link = sidebar.querySelector('[data-page="' + page + '"]');
    if (!link) return;

    var li = link.closest("li");
    if (li) li.classList.add("active");

    var collapseParent = link.closest(".collapse");
    if (collapseParent) {
      collapseParent.classList.add("show");
      var parentLi = collapseParent.closest("li.nav-item");
      if (parentLi) {
        parentLi.classList.add("active");
        var toggle = parentLi.querySelector(":scope > a");
        if (toggle) {
          toggle.setAttribute("aria-expanded", "true");
          toggle.classList.remove("collapsed");
        }
      }
    }
  }

  function getBoolPref(key) {
    try {
      return localStorage.getItem(key) === "1";
    } catch (e) {
      return false;
    }
  }

  function setBoolPref(key, value) {
    try {
      localStorage.setItem(key, value ? "1" : "0");
    } catch (e) {}
  }

  var DALTONISMO_TIPOS = [
    "ninguno",
    "protanopia",
    "deuteranopia",
    "tritanopia",
    "acromatopsia",
  ];

  function getDaltonismoTipo() {
    var tipo = null;
    try {
      tipo = localStorage.getItem("siguppys_daltonismo_tipo");
    } catch (e) {}
    return DALTONISMO_TIPOS.indexOf(tipo) !== -1 ? tipo : "ninguno";
  }

  function setDaltonismoTipo(tipo) {
    if (DALTONISMO_TIPOS.indexOf(tipo) === -1) return;
    try {
      localStorage.setItem("siguppys_daltonismo_tipo", tipo);
    } catch (e) {}
  }

  // Reutiliza el propio "skin" oscuro que ya trae kaiadmin.css:
  // se activa con el atributo data-background-color="dark" en el
  // body, el sidebar, los logo-header y la barra superior.
  function applyDarkMode(enabled) {
    var sidebar = document.querySelector(".sidebar");
    var logoHeaders = document.querySelectorAll(".logo-header");
    var navbarHeader = document.querySelector(".main-header .navbar-header");
    if (enabled) {
      document.body.setAttribute("data-background-color", "dark");
      if (sidebar) sidebar.setAttribute("data-background-color", "dark");
      logoHeaders.forEach(function (el) {
        el.setAttribute("data-background-color", "dark");
      });
      if (navbarHeader) navbarHeader.setAttribute("data-background-color", "dark");
    } else {
      document.body.removeAttribute("data-background-color");
      if (sidebar) sidebar.setAttribute("data-background-color", "white");
      logoHeaders.forEach(function (el) {
        el.removeAttribute("data-background-color");
      });
      if (navbarHeader) navbarHeader.removeAttribute("data-background-color");
    }
  }

  // Modo daltonismo: según el tipo elegido, cambia los pares
  // rojo/verde (estado activo/inhabilitado, iconos de éxito o
  // peligro) por una paleta adecuada a ese tipo. Las reglas de
  // color viven en siguppys.css, sobre body[data-daltonismo="tipo"].
  function applyDaltonismo(tipo) {
    if (DALTONISMO_TIPOS.indexOf(tipo) === -1) tipo = "ninguno";
    if (tipo === "ninguno") {
      document.body.removeAttribute("data-daltonismo");
    } else {
      document.body.setAttribute("data-daltonismo", tipo);
    }
  }

  function initPreferences() {
    applyDarkMode(getBoolPref("siguppys_dark_mode"));
    applyDaltonismo(getDaltonismoTipo());
  }

  document.addEventListener("DOMContentLoaded", function () {
    highlightActiveNav();
    initRoleSwitcher();
    initPreferences();
  });

  window.SIGuppys = window.SIGuppys || {};
  window.SIGuppys.getRole = getRole;
  window.SIGuppys.setRole = setRole;
  window.SIGuppys.ROLES = ROLES;
  window.SIGuppys.getBoolPref = getBoolPref;
  window.SIGuppys.setBoolPref = setBoolPref;
  window.SIGuppys.applyDarkMode = applyDarkMode;
  window.SIGuppys.applyDaltonismo = applyDaltonismo;
  window.SIGuppys.DALTONISMO_TIPOS = DALTONISMO_TIPOS;
  window.SIGuppys.getDaltonismoTipo = getDaltonismoTipo;
  window.SIGuppys.setDaltonismoTipo = setDaltonismoTipo;
})();
