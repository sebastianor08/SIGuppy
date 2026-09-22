/* =========================================================
   SIGuppys — cierre de sesión por inactividad (15 minutos)
   =========================================================
   Complementa la revisión que ya hace el servidor en
   lib/requiere_sesion.php y lib/helpers.php: si el usuario deja
   la pestaña abierta sin navegar a otra página, este temporizador
   la cierra igual, sin esperar a la siguiente carga de página.

   Se incluye solo en páginas ya protegidas (View/partials/footer.php),
   nunca en las de login.
   ========================================================= */
(function () {
  "use strict";

  var MINUTOS_INACTIVIDAD = 15;
  var LIMITE_MS = MINUTOS_INACTIVIDAD * 60 * 1000;
  var AVISO_MS = LIMITE_MS - 60 * 1000; // aviso 1 minuto antes de cerrar

  var basePath = window.SIG_BASE_PATH || "../../";
  var timerCierre = null;
  var timerAviso = null;
  var avisoMostrado = false;

  function cerrarSesion() {
    window.location.href = basePath + "Controller/login/logout.php";
  }

  function ocultarAviso() {
    var caja = document.getElementById("sigAvisoInactividad");
    if (caja) caja.remove();
    avisoMostrado = false;
  }

  function mostrarAviso() {
    if (avisoMostrado) return;
    avisoMostrado = true;
    var caja = document.createElement("div");
    caja.id = "sigAvisoInactividad";
    caja.setAttribute("role", "alert");
    caja.style.cssText =
      "position:fixed;bottom:20px;right:20px;z-index:2000;max-width:320px;" +
      "background:#fff3cd;color:#664d03;border:1px solid #ffe69c;" +
      "border-radius:8px;padding:14px 16px;box-shadow:0 4px 16px rgba(0,0,0,.15);" +
      "font-size:14px;";
    caja.innerHTML =
      "<strong>Tu sesión está por cerrarse</strong><br>" +
      "Por seguridad, se cerrará en 1 minuto por inactividad. Mueve el mouse o presiona una tecla para continuar.";
    document.body.appendChild(caja);
  }

  function reiniciarTemporizadores() {
    ocultarAviso();
    if (timerAviso) clearTimeout(timerAviso);
    if (timerCierre) clearTimeout(timerCierre);
    timerAviso = setTimeout(mostrarAviso, AVISO_MS);
    timerCierre = setTimeout(cerrarSesion, LIMITE_MS);
  }

  var EVENTOS = ["mousemove", "mousedown", "keydown", "scroll", "touchstart", "click"];
  var pendienteReinicio = false;

  function alDetectarActividad() {
    // Se agrupan varios eventos seguidos (p. ej. mousemove) en un solo
    // reinicio de temporizador, para no recrear timers en cada pixel.
    if (pendienteReinicio) return;
    pendienteReinicio = true;
    setTimeout(function () {
      pendienteReinicio = false;
      reiniciarTemporizadores();
    }, 1000);
  }

  EVENTOS.forEach(function (evento) {
    document.addEventListener(evento, alDetectarActividad, { passive: true });
  });

  reiniciarTemporizadores();
})();
