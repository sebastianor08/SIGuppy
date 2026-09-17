/* =========================================================
   SIGuppys — Página Configuraciones
   Conecta los interruptores de Modo oscuro y Modo daltonismo con
   las funciones compartidas de assets/js/siguppys-nav.js, para que
   el cambio se vea de inmediato y quede guardado para las demás
   páginas.
   ========================================================= */
(function () {
  "use strict";
  if (document.body.getAttribute("data-page") !== "configuraciones") return;

  var darkSwitch = document.getElementById("switchDarkMode");
  var daltonismoSelect = document.getElementById("selectDaltonismo");

  darkSwitch.checked = window.SIGuppys.getBoolPref("siguppys_dark_mode");
  daltonismoSelect.value = window.SIGuppys.getDaltonismoTipo();

  darkSwitch.addEventListener("change", function () {
    window.SIGuppys.setBoolPref("siguppys_dark_mode", this.checked);
    window.SIGuppys.applyDarkMode(this.checked);
  });

  daltonismoSelect.addEventListener("change", function () {
    window.SIGuppys.setDaltonismoTipo(this.value);
    window.SIGuppys.applyDaltonismo(this.value);
  });
})();
