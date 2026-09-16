/*
 * =========================================================
 * SIGUppys — Módulo Tipos de Depósito
 * =========================================================
 *
 * Backend:
 *   Web/ajax.php?modulo=TipoDeposito&controlador=TipoDeposito&funcion=...
 *
 * Tabla principal:
 *   tipo_deposito
 *
 * Campos:
 *   id_tipo_deposito
 *   nombre
 *   descripcion
 *   estado
 *
 * =========================================================
 */

(function () {
    "use strict";

    /*
     * El script solamente se ejecuta en la vista
     * de tipos de depósito.
     */
    if (
        !document.body ||
        document.body.getAttribute("data-page") !== "terreno-tipo-depositos"
    ) {
        return;
    }

    /*
     * URL del archivo que procesa las peticiones AJAX.
     */
    var AJAX_URL = "../../Web/ajax.php";

    /*
     * Parámetros del módulo y controlador.
     *
     * Si el nombre real del controlador es diferente,
     * debes cambiar estos valores.
     */
    var MODULO = "modulo=TipoDeposito&controlador=TipoDeposito";

    /*
     * Permisos por rol.
     */
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

    /*
     * Información cargada desde el backend.
     */
    var data = [];

    /*
     * Estado de los filtros.
     */
    var state = {
        q: "",
        estado: "todos"
    };

    /*
     * Escapa caracteres HTML para evitar insertar
     * contenido directamente sin protección.
     */
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

    /*
     * Obtiene el rol actual desde el objeto global SIGUppys.
     */
    function role() {
        return (
            window.SIGuppys &&
            window.SIGuppys.getRole &&
            window.SIGuppys.getRole()
        ) || "auxiliar";
    }

    /*
     * Obtiene los permisos del rol actual.
     */
    function permisos() {
        return PERMISOS[role()] || PERMISOS.auxiliar;
    }

    /*
     * Obtiene el nombre visible del rol.
     */
    function roleLabel() {
        var roles = window.SIGuppys && window.SIGuppys.ROLES;

        return (
            roles &&
            roles[role()] &&
            roles[role()].label
        ) || role();
    }

    /*
     * Mensaje mostrado cuando el rol no tiene permisos.
     */
    function lockedTitle(accion) {
        return (
            "Tu rol (" +
            roleLabel() +
            ") no tiene permiso para " +
            accion +
            "."
        );
    }

    /*
     * Muestra un mensaje en la vista.
     */
    function showMessage(text, type) {
        var box = document.getElementById("tiposDepositoMessage");

        if (!box) {
            return;
        }

        box.className = "alert mb-3 alert-" + type;
        box.textContent = text;
    }

    /*
     * Oculta el mensaje.
     */
    function clearMessage() {
        var box = document.getElementById("tiposDepositoMessage");

        if (!box) {
            return;
        }

        box.className = "alert d-none mb-3";
        box.textContent = "";
    }

    /*
     * Realiza peticiones GET al backend.
     *
     * Ejemplo:
     * ajax.php?modulo=TipoDeposito&controlador=TipoDeposito&funcion=lista
     */
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

    /*
     * Realiza peticiones POST al backend.
     */
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
                "No fue posible guardar la información."
            );
        }

        return result;
    }

    /*
     * Normaliza los datos recibidos del backend.
     *
     * Se convierte el ID y el estado a números
     * para poder compararlos correctamente.
     */
    function normalizar(tipoDeposito) {
        return {
            id: Number(
                tipoDeposito.id_tipo_deposito ||
                tipoDeposito.id ||
                0
            ),

            nombre: tipoDeposito.nombre || "",

            descripcion: tipoDeposito.descripcion || "",

            estado: Number(tipoDeposito.estado),

            creado_en: tipoDeposito.creado_en || ""
        };
    }

    /*
     * Genera los botones de acciones.
     */
    function renderAcciones(tipoDeposito) {
        var p = permisos();
        var btns = "";

        /*
         * Botón editar.
         */
        if (p.editar) {
            btns +=
                '<button type="button" ' +
                'class="btn-icon btn-editar" ' +
                'data-action="editar" ' +
                'data-id="' +
                tipoDeposito.id +
                '" ' +
                'title="Editar tipo de depósito">' +
                '<i class="fas fa-pen"></i>' +
                "</button>";
        }

        /*
         * Botón activar o inactivar.
         */
        if (p.inhabilitar) {
            if (tipoDeposito.estado === 1) {
                btns +=
                    '<button type="button" ' +
                    'class="btn-icon text-danger" ' +
                    'data-action="inhabilitar" ' +
                    'data-id="' +
                    tipoDeposito.id +
                    '" ' +
                    'title="Inactivar tipo de depósito">' +
                    '<i class="fas fa-ban"></i>' +
                    "</button>";
            } else {
                btns +=
                    '<button type="button" ' +
                    'class="btn-icon text-success" ' +
                    'data-action="habilitar" ' +
                    'data-id="' +
                    tipoDeposito.id +
                    '" ' +
                    'title="Activar tipo de depósito">' +
                    '<i class="fas fa-check-circle"></i>' +
                    "</button>";
            }
        }

        /*
         * Para el auxiliar no se muestran acciones.
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

    /*
     * Genera una fila de la tabla.
     */
    function renderRow(tipoDeposito) {
        var estado =
            tipoDeposito.estado === 1
                ? '<span class="badge-estado activo">Activo</span>'
                : '<span class="badge-estado inactivo">Inactivo</span>';

        return (
            "<tr>" +

            /*
             * Nombre.
             */
            "<td>" +
            '<span class="fw-bold">' +
            escapeHtml(tipoDeposito.nombre) +
            "</span>" +
            "</td>" +

            /*
             * Descripción.
             */
            '<td class="tipo-deposito-descripcion">' +
            '<span class="descripcion">' +
            escapeHtml(tipoDeposito.descripcion) +
            "</span>" +
            "</td>" +

            /*
             * Estado.
             */
            '<td class="text-center">' +
            estado +
            "</td>" +

            /*
             * Acciones.
             */
            '<td class="text-center">' +
            renderAcciones(tipoDeposito) +
            "</td>" +

            "</tr>"
        );
    }

    /*
     * Aplica los filtros de búsqueda y estado.
     */
    function filteredData() {
        var q = state.q.trim().toLowerCase();

        return data.filter(function (tipoDeposito) {
            var texto = (
                tipoDeposito.nombre +
                " " +
                tipoDeposito.descripcion
            ).toLowerCase();

            var matchesQ =
                !q ||
                texto.indexOf(q) !== -1;

            var matchesEstado =
                state.estado === "todos" ||
                (
                    state.estado === "activo" &&
                    tipoDeposito.estado === 1
                ) ||
                (
                    state.estado === "inactivo" &&
                    tipoDeposito.estado === 0
                );

            return matchesQ && matchesEstado;
        });
    }

    /*
     * Renderiza la tabla y el contador.
     */
    function render() {
        var tbody = document.getElementById(
            "tiposDepositoTableBody"
        );

        if (!tbody) {
            return;
        }

        var rows = filteredData();

        if (!rows.length) {
            tbody.innerHTML =
                '<tr class="sig-empty-row">' +
                '<td colspan="4" class="text-center">' +
                '<i class="fas fa-folder-open mb-2 d-block" ' +
                'style="font-size:22px;color:#ccc;"></i>' +
                "No hay tipos de depósito que coincidan con el filtro." +
                "</td>" +
                "</tr>";
        } else {
            tbody.innerHTML = rows
                .map(renderRow)
                .join("");
        }

        var count = document.getElementById(
            "tiposDepositoCount"
        );

        if (count) {
            count.textContent =
                rows.length +
                " de " +
                data.length +
                " tipos de depósito";
        }

        renderCreateButton();
    }

    /*
     * Habilita o bloquea el botón de crear según el rol.
     */
    function renderCreateButton() {
        var btn = document.getElementById(
            "btnCrearTipoDeposito"
        );

        if (!btn) {
            return;
        }

        if (permisos().crear) {
            btn.disabled = false;
            btn.classList.remove("btn-locked");
            btn.title = "";
        } else {
            btn.disabled = true;
            btn.classList.add("btn-locked");
            btn.title = lockedTitle("crear tipos de depósito");
        }
    }

    /*
     * Limpia el formulario para crear un tipo de depósito.
     */
    function openCreateModal() {
        var form = document.getElementById(
            "tipoDepositoForm"
        );

        if (!form) {
            return;
        }

        form.reset();

        if (form.elements.id_tipo_deposito) {
            form.elements.id_tipo_deposito.value = "";
        }

        document.getElementById(
            "tipoDepositoModalLabel"
        ).textContent = "Crear Tipo de Depósito";

        document.getElementById(
            "tipoDepositoSubmitBtn"
        ).textContent = "Guardar";

        /*
         * Al crear, el estado predeterminado será Activo.
         */
        var estado = document.getElementById(
            "estadoTipoDeposito"
        );

        if (estado) {
            estado.value = "1";
        }
    }

    /*
     * Abre el modal con los datos del tipo de depósito seleccionado.
     */
    function openEditModal(id) {
        var tipoDeposito = data.find(function (item) {
            return item.id === id;
        });

        if (!tipoDeposito) {
            return;
        }

        var form = document.getElementById(
            "tipoDepositoForm"
        );

        if (!form) {
            return;
        }

        form.elements.id_tipo_deposito.value =
            tipoDeposito.id;

        form.elements.nombre.value =
            tipoDeposito.nombre;

        form.elements.descripcion.value =
            tipoDeposito.descripcion;

        /*
         * El select de estado utiliza valores 1 y 0.
         */
        form.elements.estado.value =
            String(tipoDeposito.estado);

        document.getElementById(
            "tipoDepositoModalLabel"
        ).textContent = "Editar Tipo de Depósito";

        document.getElementById(
            "tipoDepositoSubmitBtn"
        ).textContent = "Guardar Cambios";

        bootstrap.Modal
            .getOrCreateInstance(
                document.getElementById("tipoDepositoModal")
            )
            .show();
    }

    /*
     * Envía el formulario para crear o editar.
     */
    async function handleSubmit(e) {
        e.preventDefault();

        var form = e.target;

        var id =
            form.elements.id_tipo_deposito.value;

        /*
         * Validación de permisos.
         */
        if (!permisos().crear && !id) {
            showMessage(
                lockedTitle("crear tipos de depósito"),
                "warning"
            );
            return;
        }

        if (!permisos().editar && id) {
            showMessage(
                lockedTitle("editar tipos de depósito"),
                "warning"
            );
            return;
        }

        var nombre =
            form.elements.nombre.value.trim();

        var descripcion =
            form.elements.descripcion.value.trim();

        var estado =
            form.elements.estado.value;

        /*
         * Validación de campos.
         */
        if (!nombre || !descripcion) {
            showMessage(
                "Debe completar el nombre y la descripción.",
                "warning"
            );
            return;
        }

        var payload = {
            nombre: nombre,
            descripcion: descripcion,
            estado: Number(estado)
        };

        try {
            var response;

            /*
             * Si existe ID, se actualiza.
             * Si no existe ID, se crea.
             */
            if (id) {
                payload.id_tipo_deposito = Number(id);

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
                    document.getElementById("tipoDepositoModal")
                )
                .hide();

            await recargar();

            showMessage(
                response.message ||
                "Tipo de depósito guardado correctamente.",
                "success"
            );

        } catch (error) {
            showMessage(
                error.message,
                "danger"
            );
        }
    }

    /*
     * Cambia el estado de un tipo de depósito.
     *
     * nuevoEstado:
     *   1 = Activo
     *   0 = Inactivo
     */
    async function toggleEstado(id, nuevoEstado) {
        if (!permisos().inhabilitar) {
            showMessage(
                lockedTitle("cambiar el estado de tipos de depósito"),
                "warning"
            );
            return;
        }

        var tipoDeposito = data.find(function (item) {
            return item.id === id;
        });

        if (!tipoDeposito) {
            return;
        }

        var accion =
            nuevoEstado === 1
                ? "activar"
                : "inactivar";

        if (
            !confirm(
                "¿Seguro que deseas " +
                accion +
                ' el tipo de depósito "' +
                tipoDeposito.nombre +
                '"?'
            )
        ) {
            return;
        }

        try {
            var response = await postJson(
                "postEstado",
                {
                    id_tipo_deposito: id,
                    estado: nuevoEstado
                }
            );

            await recargar();

            showMessage(
                response.message ||
                "Estado actualizado correctamente.",
                "success"
            );

        } catch (error) {
            showMessage(
                error.message,
                "danger"
            );
        }
    }

    /*
     * Vuelve a consultar los tipos de depósito al backend.
     */
    async function recargar() {
        var lista = await getJson("lista");

        data = lista.map(normalizar);

        render();
    }

    /*
     * Evento para los botones de la tabla.
     */
    var tableBody = document.getElementById(
        "tiposDepositoTableBody"
    );

    if (tableBody) {
        tableBody.addEventListener("click", function (e) {
            var btn = e.target.closest("[data-action]");

            if (!btn) {
                return;
            }

            var id = Number(
                btn.getAttribute("data-id")
            );

            var action = btn.getAttribute(
                "data-action"
            );

            if (
                action === "editar" &&
                permisos().editar
            ) {
                openEditModal(id);
            }

            if (action === "inhabilitar") {
                toggleEstado(id, 0);
            }

            if (action === "habilitar") {
                toggleEstado(id, 1);
            }
        });
    }

    /*
     * Evento del botón Crear Tipo de Depósito.
     */
    var btnCrear = document.getElementById(
        "btnCrearTipoDeposito"
    );

    if (btnCrear) {
        btnCrear.addEventListener("click", function () {
            if (!permisos().crear) {
                showMessage(
                    lockedTitle("crear tipos de depósito"),
                    "warning"
                );
                return;
            }

            openCreateModal();

            bootstrap.Modal
                .getOrCreateInstance(
                    document.getElementById("tipoDepositoModal")
                )
                .show();
        });
    }

    /*
     * Evento del formulario.
     */
    var tipoDepositoForm = document.getElementById(
        "tipoDepositoForm"
    );

    if (tipoDepositoForm) {
        tipoDepositoForm.addEventListener(
            "submit",
            handleSubmit
        );
    }

    /*
     * Evento de búsqueda.
     */
    var search = document.getElementById(
        "tiposDepositoSearch"
    );

    if (search) {
        search.addEventListener("input", function () {
            state.q = this.value;
            render();
        });
    }

    /*
     * Evento del filtro de estado.
     */
    var filtroEstado = document.getElementById(
        "tiposDepositoEstadoFiltro"
    );

    if (filtroEstado) {
        filtroEstado.addEventListener("change", function () {
            state.estado = this.value;
            render();
        });
    }

    /*
     * Cuando cambia el rol, se actualizan los botones.
     */
    document.addEventListener(
        "siguppys:role-changed",
        function () {
            render();
        }
    );

    /*
     * Inicialización del módulo.
     */
    (async function init() {
        try {
            var resultados = await Promise.all([
                getJson("lista")
            ]);

            data = resultados[0].map(normalizar);

            clearMessage();

            render();

        } catch (error) {
            var tbody = document.getElementById(
                "tiposDepositoTableBody"
            );

            if (tbody) {
                tbody.innerHTML =
                    '<tr class="sig-empty-row">' +
                    '<td colspan="4" class="text-center">' +
                    "No se pudieron cargar los tipos de depósito." +
                    "</td>" +
                    "</tr>";
            }

            showMessage(
                error.message +
                " Verifica la conexión con PostgreSQL.",
                "danger"
            );
        }
    })();

})();