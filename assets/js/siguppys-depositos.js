/* =========================================================
   SIGuppys — Módulo Depósitos
   =========================================================
   Backend:
     Web/ajax.php?modulo=Deposito&controlador=Deposito&funcion=...

   Tabla principal:
     sitio

   Relaciones:
     sitio -> tipo_deposito
     sitio -> direccion
   ========================================================= */

(function () {
  "use strict";

  if (document.body.getAttribute("data-page") !== "terreno-depositos") {
    return;
  }

  var AJAX_URL = "../../Web/ajax.php";
  var MODULO = "modulo=Deposito&controlador=Deposito";

  var PERMISOS = {
    auxiliar: {
      crear: false,
      editar: false,
      inhabilitar: false
    },
    coordinador: {
      crear: true,
      editar: true,
      inhabilitar: true
    }
  };

  var data = [];
  var tipos = [];
  var direcciones = [];

  var state = {
    q: "",
    estado: "todos"
  };

  function escapeHtml(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, function (c) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
      }[c];
    });
  }

  function role() {
    return (window.SIGuppys && window.SIGuppys.getRole()) || "auxiliar";
  }

  function permisos() {
    return PERMISOS[role()];
  }

  function roleLabel() {
    var roles = window.SIGuppys && window.SIGuppys.ROLES;

    return (
      roles &&
      roles[role()] &&
      roles[role()].label
    ) || role();
  }

  function lockedTitle(accion) {
    return (
      "Tu rol (" +
      roleLabel() +
      ") no tiene permiso para " +
      accion +
      "."
    );
  }

  function showMessage(text, type) {
    var box = document.getElementById("depositosMessage");

    if (!box) return;

    box.className = "alert mb-3 alert-" + type;
    box.textContent = text;
  }

  function clearMessage() {
    var box = document.getElementById("depositosMessage");

    if (!box) return;

    box.className = "alert d-none mb-3";
    box.textContent = "";
  }

  async function getJson(funcion, extra) {
    var url =
      AJAX_URL +
      "?" +
      MODULO +
      "&funcion=" +
      funcion +
      (extra || "");

    var response = await fetch(url, {
      headers: {
        Accept: "application/json"
      }
    });

    var result = await response.json().catch(function () {
      return null;
    });

    if (
      !response.ok ||
      !result ||
      result.ok === false
    ) {
      throw new Error(
        (result && result.message) ||
        "No fue posible consultar la información."
      );
    }

    return result.data || [];
  }

  async function postJson(funcion, payload) {
    var response = await fetch(
      AJAX_URL +
        "?" +
        MODULO +
        "&funcion=" +
        funcion,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json"
        },
        body: JSON.stringify(payload)
      }
    );

    var result = await response.json().catch(function () {
      return null;
    });

    if (
      !response.ok ||
      !result ||
      result.ok === false
    ) {
      throw new Error(
        (result && result.message) ||
        "No fue posible guardar."
      );
    }

    return result;
  }

  function normalizar(d) {
    return {
      id: Number(d.id_sitio),
      id_tipo_deposito: Number(d.id_tipo_deposito),
      tipo_deposito: d.tipo_deposito || "",
      descripcion: d.descripcion || "",
      id_direccion: Number(d.id_direccion),
      direccion: d.direccion || "",
      barrio: d.barrio || "",
      estado: Number(d.estado),
      creado_en: d.creado_en || ""
    };
  }

  function renderAcciones(d) {
    var p = permisos();

    var btns = "";

    if (p.editar) {
      btns +=
        '<button type="button" class="btn-icon" ' +
        'data-action="editar" data-id="' +
        d.id +
        '" title="Editar">' +
        '<i class="fas fa-pen"></i>' +
        "</button>";
    }

    if (p.inhabilitar) {
      if (d.estado === 1) {
        btns +=
          '<button type="button" class="btn-icon text-danger" ' +
          'data-action="inhabilitar" data-id="' +
          d.id +
          '" title="Inhabilitar">' +
          '<i class="fas fa-ban"></i>' +
          "</button>";
      } else {
        btns +=
          '<button type="button" class="btn-icon text-success" ' +
          'data-action="habilitar" data-id="' +
          d.id +
          '" title="Habilitar">' +
          '<i class="fas fa-check-circle"></i>' +
          "</button>";
      }
    }

    /*
     * Para el auxiliar se conserva la tabla, pero no se muestran
     * acciones porque no tiene permisos de edición.
     */
    if (!btns) {
      return '<div class="table-actions"></div>';
    }

    return (
      '<div class="table-actions">' +
      btns +
      "</div>"
    );
  }

  function renderRow(d) {
    var estado =
      d.estado === 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inactivo</span>';

    var direccion =
      escapeHtml(d.direccion);

    if (d.barrio) {
      direccion +=
        '<div class="small text-muted">' +
        escapeHtml(d.barrio) +
        "</div>";
    }

    return (
      "<tr>" +

      "<td>" +
      '<span class="fw-bold">' +
      escapeHtml(d.tipo_deposito) +
      "</span>" +
      "</td>" +

      '<td class="deposito-descripcion">' +
      '<span class="descripcion">' +
      escapeHtml(d.descripcion) +
      "</span>" +
      "</td>" +

      '<td class="deposito-direccion">' +
      direccion +
      "</td>" +

      '<td class="text-center">' +
      estado +
      "</td>" +

      '<td class="text-center">' +
      renderAcciones(d) +
      "</td>" +

      "</tr>"
    );
  }

  function filteredData() {
    var q = state.q.trim().toLowerCase();

    return data.filter(function (d) {
      var texto =
        (
          d.tipo_deposito +
          " " +
          d.descripcion +
          " " +
          d.direccion +
          " " +
          d.barrio
        ).toLowerCase();

      var matchesQ =
        !q ||
        texto.indexOf(q) !== -1;

      var matchesEstado =
        state.estado === "todos" ||
        (
          state.estado === "activo" &&
          d.estado === 1
        ) ||
        (
          state.estado === "inactivo" &&
          d.estado === 0
        );

      return matchesQ && matchesEstado;
    });
  }

  function render() {
    var tbody =
      document.getElementById(
        "depositosTableBody"
      );

    var rows = filteredData();

    if (!rows.length) {
      tbody.innerHTML =
        '<tr class="sig-empty-row">' +
        '<td colspan="5">' +
        '<i class="fas fa-folder-open mb-2 d-block" ' +
        'style="font-size:22px;color:#ccc;"></i>' +
        "No hay depósitos que coincidan con el filtro." +
        "</td>" +
        "</tr>";
    } else {
      tbody.innerHTML =
        rows.map(renderRow).join("");
    }

    var count =
      document.getElementById(
        "depositosCount"
      );

    if (count) {
      count.textContent =
        rows.length +
        " de " +
        data.length +
        " depósitos";
    }

    renderCreateButton();
  }

  function renderCreateButton() {
    var btn =
      document.getElementById(
        "btnCrearDeposito"
      );

    if (!btn) return;

    if (permisos().crear) {
      btn.disabled = false;
      btn.classList.remove("btn-locked");
      btn.title = "";
    } else {
      btn.disabled = true;
      btn.classList.add("btn-locked");
      btn.title = lockedTitle(
        "crear depósitos"
      );
    }
  }

  function fillTipos(selectedId) {
    var select =
      document.getElementById(
        "idTipoDeposito"
      );

    select.innerHTML =
      '<option value="">Seleccione un tipo</option>' +
      tipos
        .map(function (t) {
          var selected =
            String(t.id_tipo_deposito) ===
            String(selectedId)
              ? " selected"
              : "";

          return (
            '<option value="' +
            t.id_tipo_deposito +
            '"' +
            selected +
            ">" +
            escapeHtml(t.nombre) +
            "</option>"
          );
        })
        .join("");
  }

  function fillDirecciones(selectedId) {
    var select =
      document.getElementById(
        "idDireccion"
      );

    if (!direcciones.length) {
      select.innerHTML =
        '<option value="">No hay direcciones registradas</option>';
      return;
    }

    select.innerHTML =
      '<option value="">Seleccione una dirección</option>' +
      direcciones
        .map(function (d) {
          var selected =
            String(d.id_direccion) ===
            String(selectedId)
              ? " selected"
              : "";

          var texto =
            d.direccion;

          if (d.barrio) {
            texto +=
              " · " +
              d.barrio;
          }

          if (d.ciudad) {
            texto +=
              " · " +
              d.ciudad;
          }

          return (
            '<option value="' +
            d.id_direccion +
            '"' +
            selected +
            ">" +
            escapeHtml(texto) +
            "</option>"
          );
        })
        .join("");
  }

  function openCreateModal() {
    var form =
      document.getElementById(
        "depositoForm"
      );

    form.reset();

    form.elements[
      "id_sitio"
    ].value = "";

    document.getElementById(
      "depositoModalLabel"
    ).textContent =
      "Crear Depósito";

    document.getElementById(
      "depositoSubmitBtn"
    ).textContent =
      "Guardar";

    fillTipos("");
    fillDirecciones("");
  }

  function openEditModal(id) {
    var d = data.find(function (item) {
      return item.id === id;
    });

    if (!d) return;

    var form =
      document.getElementById(
        "depositoForm"
      );

    form.elements[
      "id_sitio"
    ].value = d.id;

    document.getElementById(
      "depositoModalLabel"
    ).textContent =
      "Editar Depósito";

    document.getElementById(
      "depositoSubmitBtn"
    ).textContent =
      "Guardar Cambios";

    fillTipos(
      d.id_tipo_deposito
    );

    fillDirecciones(
      d.id_direccion
    );

    bootstrap.Modal
      .getOrCreateInstance(
        document.getElementById(
          "depositoModal"
        )
      )
      .show();
  }

  async function handleSubmit(e) {
    e.preventDefault();

    if (!permisos().crear &&
        !e.target.elements.id_sitio.value) {
      return;
    }

    if (!permisos().editar &&
        e.target.elements.id_sitio.value) {
      return;
    }

    var form = e.target;

    var id = form.elements.id_sitio.value;

    var payload = {
      id_tipo_deposito:
        Number(
          form.elements
            .id_tipo_deposito.value
        ),
      id_direccion:
        Number(
          form.elements
            .id_direccion.value
        )
    };

    if (
      !payload.id_tipo_deposito ||
      !payload.id_direccion
    ) {
      showMessage(
        "Debe seleccionar el tipo de depósito y la dirección.",
        "warning"
      );
      return;
    }

    try {
      var response;

      if (id) {
        payload.id_sitio =
          Number(id);

        response = await postJson(
          "postUpdate",
          payload
        );
      } else {
        response = await postJson(
          "postCreate",
          payload
        );
      }

      bootstrap.Modal
        .getOrCreateInstance(
          document.getElementById(
            "depositoModal"
          )
        )
        .hide();

      await recargar();

      showMessage(
        response.message,
        "success"
      );

    } catch (error) {
      showMessage(
        error.message,
        "danger"
      );
    }
  }

  async function toggleEstado(
    id,
    nuevoEstado
  ) {
    if (!permisos().inhabilitar) {
      return;
    }

    var d = data.find(function (item) {
      return item.id === id;
    });

    if (!d) return;

    var accion =
      nuevoEstado === 1
        ? "habilitar"
        : "inhabilitar";

    if (
      !confirm(
        "¿Seguro que deseas " +
        accion +
        ' el depósito "' +
        d.tipo_deposito +
        '"?'
      )
    ) {
      return;
    }

    try {
      var response =
        await postJson(
          "postEstado",
          {
            id_sitio: id,
            estado: nuevoEstado
          }
        );

      await recargar();

      showMessage(
        response.message,
        "success"
      );

    } catch (error) {
      showMessage(
        error.message,
        "danger"
      );
    }
  }

  async function recargar() {
    var lista =
      await getJson("lista");

    data =
      lista.map(normalizar);

    render();
  }

  document
    .getElementById(
      "depositosTableBody"
    )
    .addEventListener(
      "click",
      function (e) {
        var btn =
          e.target.closest(
            "[data-action]"
          );

        if (!btn) return;

        var id =
          Number(
            btn.getAttribute(
              "data-id"
            )
          );

        var action =
          btn.getAttribute(
            "data-action"
          );

        if (
          action === "editar" &&
          permisos().editar
        ) {
          openEditModal(id);
        }

        if (
          action === "inhabilitar"
        ) {
          toggleEstado(id, 0);
        }

        if (
          action === "habilitar"
        ) {
          toggleEstado(id, 1);
        }
      }
    );

  document
    .getElementById(
      "btnCrearDeposito"
    )
    .addEventListener(
      "click",
      function () {
        if (!permisos().crear) {
          return;
        }

        openCreateModal();

        bootstrap.Modal
          .getOrCreateInstance(
            document.getElementById(
              "depositoModal"
            )
          )
          .show();
      }
    );

  document
    .getElementById(
      "depositoForm"
    )
    .addEventListener(
      "submit",
      handleSubmit
    );

  document
    .getElementById(
      "depositosSearch"
    )
    .addEventListener(
      "input",
      function () {
        state.q = this.value;
        render();
      }
    );

  document
    .getElementById(
      "depositosEstadoFiltro"
    )
    .addEventListener(
      "change",
      function () {
        state.estado = this.value;
        render();
      }
    );

  document.addEventListener(
    "siguppys:role-changed",
    function () {
      render();
    }
  );

  (async function init() {
    try {
      var resultados =
        await Promise.all([
          getJson("lista"),
          getJson("tipos"),
          getJson("direcciones")
        ]);

      data =
        resultados[0].map(
          normalizar
        );

      tipos = resultados[1];
      direcciones =
        resultados[2];

      clearMessage();

      render();

    } catch (error) {
      document.getElementById(
        "depositosTableBody"
      ).innerHTML =
        '<tr class="sig-empty-row">' +
        '<td colspan="5">' +
        "No se pudieron cargar los depósitos." +
        "</td>" +
        "</tr>";

      showMessage(
        error.message +
        " Verifica la conexión con PostgreSQL.",
        "danger"
      );
    }
  })();

})();
