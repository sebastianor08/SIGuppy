<?php

    $basePath  = '../../';
    $pageTitle = 'Actividades';
    $bodyPage  = 'terreno-actividades';

    include '../partials/head.php';

?>

<div class="wrapper">

    <?php $rutaBase = '../../'; ?>
    <?php include '../partials/sidebar.php'; ?>

    <div class="main-panel">

        <?php include '../partials/topbar.php'; ?>

        <div class="container">

            <div class="page-inner">

                <!-- Encabezado -->
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">

                    <div>
                        <h3 class="fw-bold mb-3">Trabajo de terreno - Actividades</h3>
                        <h6 class="op-7 mb-2">Trabajo de terreno / Actividades</h6>
                    </div>

                    <div class="ms-md-auto py-2 py-md-0 d-flex gap-2 align-items-center">

                        <button
                            type="button"
                            id="btnCrearActividad"
                            class="btn btn-primary btn-round btn-crear"
                        >
                            <i class="fas fa-plus me-1"></i>
                            Crear Actividad
                        </button>

                    </div>

                </div>

                <!-- Tabla de actividades -->
                <div class="card">

                    <div class="card-body">

                        <!-- Barra de búsqueda y filtro -->
                        <div class="sig-table-toolbar">

                            <div class="sig-search">
                                <i class="fas fa-search"></i>

                                <input
                                    type="text"
                                    id="actividadesSearch"
                                    class="form-control"
                                    placeholder="Buscar por nombre o descripción..."
                                />

                            </div>

                            <div class="d-flex align-items-center gap-2">

                                <select
                                    id="actividadesEstadoFiltro"
                                    class="form-select form-select-sm"
                                    style="width:auto;"
                                >
                                    <option value="todos">Todos los estados</option>
                                    <option value="activo">Activos</option>
                                    <option value="inactivo">Inactivos</option>
                                </select>

                                <span
                                    class="small text-muted"
                                    id="actividadesCount"
                                ></span>

                            </div>

                        </div>

                        <!-- Mensajes -->
                        <div
                            id="actividadesMessage"
                            class="alert d-none mb-3"
                            role="alert"
                        ></div>

                        <!-- Tabla -->
                        <div class="table-responsive">

                            <table class="table align-items-center mb-0">

                                <thead class="table-light">

                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>

                                </thead>

                                <tbody id="actividadesTableBody">
                                    <tr class="sig-empty-row">
                                        <td colspan="4">Cargando actividades...</td>
                                    </tr>
                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Modal Crear / Editar Actividad -->
<div
    class="modal fade"
    id="actividadModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <form id="actividadForm">

                <input
                    type="hidden"
                    name="id_actividad"
                    id="idActividad"
                />

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="actividadModalLabel"
                    >
                        Crear Actividad
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>

                </div>

                <div class="modal-body">

                    <!-- Nombre -->
                    <div class="mb-3">

                        <label
                            for="nombreActividad"
                            class="form-label"
                        >
                            Nombre de la actividad
                        </label>

                        <input
                            type="text"
                            name="nombre"
                            id="nombreActividad"
                            class="form-control"
                            placeholder="Ejemplo: Limpieza de tanques"
                            required
                        />

                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">

                        <label
                            for="descripcionActividad"
                            class="form-label"
                        >
                            Descripción
                        </label>

                        <textarea
                            name="descripcion"
                            id="descripcionActividad"
                            class="form-control"
                            rows="4"
                            placeholder="Ingrese una descripción de la actividad"
                            required
                        ></textarea>

                    </div>

                    <!-- Estado -->
                    <div class="mb-3">

                        <label
                            for="estadoActividad"
                            class="form-label"
                        >
                            Estado
                        </label>

                        <select
                            name="estado"
                            id="estadoActividad"
                            class="form-select"
                            required
                        >
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-label-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="actividadSubmitBtn"
                    >
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php

    $pageScripts = ['Web/assets/js/siguppys-actividades.js'];

    include '../partials/footer.php';

?>

</body>
</html>
