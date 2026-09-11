<?php

// Datos de prueba
// Puedes reemplazar este arreglo por tu consulta SQL o PDO
$actividades = [
    [
        'id' => 1,
        'nombre' => 'Alimentación de especies',
        'descripcion' => 'Actividad relacionada con la alimentación diaria de los animales.',
        'estado' => 'Activo'
    ],
    [
        'id' => 2,
        'nombre' => 'Limpieza de tanques',
        'descripcion' => 'Limpieza y mantenimiento de los tanques del zoocriadero.',
        'estado' => 'Inactivo'
    ],
    [
        'id' => 3,
        'nombre' => 'Revisión de especies',
        'descripcion' => 'Inspección del estado general de las especies.',
        'estado' => 'Activo'
    ],
    [
        'id' => 4,
        'nombre' => 'Control sanitario',
        'descripcion' => 'Verificación de las condiciones sanitarias de los animales.',
        'estado' => 'Activo'
    ]
];

?>

<style>
    .actividad-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: #334155;
    }

    .actividad-container .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 28px;
    }

    .actividad-container .table-card {
        background-color: #FFFFFF;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .actividad-container .custom-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        margin: 0;
    }

    .actividad-container .custom-table th {
        background-color: #FAFAFA;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 16px 20px;
        border-bottom: 1px solid #E2E8F0;
    }

    .actividad-container .custom-table td {
        padding: 16px 20px;
        font-size: 14px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }

    .actividad-container .custom-table tr:last-child td {
        border-bottom: none;
    }

    /* Descripción */
    .actividad-container .description-cell {
        max-width: 400px;
        line-height: 1.5;
    }

    /* Badges de estado */
    .actividad-container .status-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
    }

    .actividad-container .status-badge.activo {
        background-color: #DCFCE7;
        color: #166534;
    }

    .actividad-container .status-badge.inactivo {
        background-color: #FEE2E2;
        color: #991B1B;
    }

    /* Botones de acción */
    .actividad-container .actions-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .actividad-container .btn-action {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.1s ease;
    }

    .actividad-container .btn-action:hover {
        transform: scale(1.1);
    }

    /* Botón inferior "Crear Actividad" */
    .actividad-container .action-bar {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
    }

    .actividad-container .btn-actividad-primary {
        background-color: #2563EB;
        color: #FFFFFF;
        font-weight: 600;
        font-size: 14px;
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        transition: background-color 0.2s ease, transform 0.1s ease;
        text-decoration: none;
        display: inline-block;
    }

    .actividad-container .btn-actividad-primary:hover {
        background-color: #1D4ED8;
        color: #FFFFFF;
    }
</style>

<div class="actividad-container">

    <h1 class="page-title">Trabajo de terreno - Actividades</h1>

    <!-- Tabla principal -->
    <div class="table-card">

        <table class="custom-table">

            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($actividades as $actividad): ?>

                    <tr>

                        <!-- Nombre -->
                        <td>
                            <?= htmlspecialchars($actividad['nombre']) ?>
                        </td>

                        <!-- Descripción -->
                        <td class="description-cell">
                            <?= htmlspecialchars($actividad['descripcion']) ?>
                        </td>

                        <!-- Estado -->
                        <td>
                            <span class="status-badge <?= strtolower($actividad['estado']) ?>">
                                <?= htmlspecialchars($actividad['estado']) ?>
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="actions-cell">

                            <!-- Botón Editar -->
                            <a
                                href="editar_actividad.php?id=<?= $actividad['id'] ?>"
                                class="btn-action"
                                title="Editar"
                                style="display: none;"
                            >
                                <svg
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="#4F46E5"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-9.5-9.5z"></path>
                                </svg>
                            </a>

                            <!-- Botón Cambiar Estado -->
                            <?php if ($actividad['estado'] === 'Activo'): ?>

                                <a
                                    href="cambiar_estado_actividad.php?id=<?= $actividad['id'] ?>&accion=desactivar"
                                    class="btn-action"
                                    title="Desactivar"
                                >
                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#EF4444"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                    </svg>
                                </a>

                            <?php else: ?>

                                <a
                                    href="cambiar_estado_actividad.php?id=<?= $actividad['id'] ?>&accion=activar"
                                    class="btn-action"
                                    title="Activar"
                                >
                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#22C55E"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <!-- Botón de acción principal -->
    <div class="action-bar">
        <a href="crear_actividad.php" class="btn-actividad-primary">
            Crear Actividad
        </a>
    </div>

</div>