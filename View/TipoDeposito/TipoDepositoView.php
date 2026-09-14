<?php
// Datos de prueba (puedes reemplazar este arreglo por tu consulta SQL / PDO)
$depositos = [
    [
        'id' => 1,
        'deposito' => 'Depósito 01',
        'tipo_deposito' => 'Tanque elevado',
        'direccion' => 'Calle 12 # 45-67',
        'estado' => 'Activo',
        'fecha' => '2026-03-01'
    ],
    [
        'id' => 2,
        'deposito' => 'Depósito 02',
        'tipo_deposito' => 'Piscina',
        'direccion' => 'Carrera 8 # 12-34',
        'estado' => 'Inactivo',
        'fecha' => '2026-02-15'
    ],
    [
        'id' => 3,
        'deposito' => 'Depósito 03',
        'tipo_deposito' => 'Lote bajo',
        'direccion' => 'Avenida 5 # 22-10',
        'estado' => 'Activo',
        'fecha' => '2026-03-04'
    ],
    [
        'id' => 4,
        'deposito' => 'Depósito 04',
        'tipo_deposito' => 'Canaleta',
        'direccion' => 'Transversal 3 # 10-05',
        'estado' => 'Activo',
        'fecha' => '2026-03-05'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajo de terreno - Depósitos | SIGuppy</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #F8FAFC;
            color: #334155;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Título Principal */
        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 28px;
        }

        /* Contenedor de la Tabla */
        .table-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .custom-table th {
            background-color: #FAFAFA;
            color: #64748B;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 16px 20px;
            border-bottom: 1px solid #E2E8F0;
        }

        .custom-table td {
            padding: 16px 20px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        /* Badges de Estado */
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .status-badge.activo {
            background-color: #DCFCE7;
            color: #166534;
        }

        .status-badge.inactivo {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        /* Botones de Acción */
        .actions-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.1s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        /* Botón Inferior "Crear Depósito" */
        .action-bar {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-primary {
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

        .btn-primary:hover {
            background-color: #1D4ED8;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="page-title">Trabajo de terreno - Depositos</h1>

    <!-- Tabla principal -->
    <div class="table-card">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>DEPOSITO</th>
                    <th>TIPO DEPOSITO</th>
                    <th>DIRECCIÓN</th>
                    <th>ESTADO</th>
                    <th>FECHA</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($depositos as $deposito): ?>
                    <tr>
                        <td><?= htmlspecialchars($deposito['deposito']) ?></td>
                        <td><?= htmlspecialchars($deposito['tipo_deposito']) ?></td>
                        <td><?= htmlspecialchars($deposito['direccion']) ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($deposito['estado']) ?>">
                                <?= htmlspecialchars($deposito['estado']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($deposito['fecha']) ?></td>
                        <td class="actions-cell">
                            <!-- Botón Editar -->
                            <a href="editar_deposito.php?id=<?= $deposito['id'] ?>" class="btn-action" title="Editar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>

                            <!-- Botón Cambiar Estado (Inactivar / Activar) -->
                            <?php if ($deposito['estado'] === 'Activo'): ?>
                                <a href="cambiar_estado.php?id=<?= $deposito['id'] ?>&accion=desactivar" class="btn-action" title="Desactivar">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <a href="cambiar_estado.php?id=<?= $deposito['id'] ?>&accion=activar" class="btn-action" title="Activar">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

    <!-- Botón de Acción Principal -->
    <div class="action-bar">
        <a href="crear_deposito.php" class="btn-primary">Crear Deposito</a>
    </div>
</div>

</body>
</html>