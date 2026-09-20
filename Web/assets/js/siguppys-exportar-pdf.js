(function () {
  "use strict";

  var botones = document.querySelectorAll(".btn-reportes");

  botones.forEach(function (boton) {
    boton.addEventListener("click", function () {
      window.print();
    });
  });
})();
