<?php
require_once __DIR__ . '/../../Controller/Reportes/SeguimientoDeActividadesController.php';

$datos = obtenerDatosSeguimientoDeActividades();
extract($datos);
?>

<div class="caja">
    <h2 class="titulo-pagina">Seguimiento de Actividades en los Zoocriaderos</h2>
    <h3>Filtros</h3>
    <form method="GET">
        <div class="fila-filtros">
            <div>
                <label>Zoocriadero</label>
                <select name="zoocriadero">
                    <option value="">Todos</option>
                    <?php foreach ($listaZoocriaderos as $zoo): ?>
                        <option value="<?= $zoo ?>" <?= $filtroZoocriadero === $zoo ? 'selected' : '' ?>>
                            <?= $zoo ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Actividad</label>
                <select name="actividad">
                    <option value="">Todas</option>
                    <?php foreach ($listaActividades as $act): ?>
                        <option value="<?= $act ?>" <?= $filtroActividad === $act ? 'selected' : '' ?>>
                            <?= $act ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?= $filtroFechaInicio ?>">
            </div>
            <div>
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?= $filtroFechaFin ?>">
            </div>
            <div>
                <button type="submit" class="btn-aplicar">Aplicar Filtros</button>
                <button type="button" class="btn-reportes">Generar Reportes</button>
            </div>
        </div>
    </form>
</div>

<div class="caja tarjetas">
    <div class="tarjeta">
        <div class="icono icono-azul">📋</div>
        <div>
            <p>Actividades Totales</p>
            <span class="numero azul"><?= $totalActividades ?></span>
        </div>
    </div>
    <div class="tarjeta">
        <div class="icono icono-verde">✔</div>
        <div>
            <p>Actividades Completas</p>
            <span class="numero verde"><?= $totalCompletas ?></span>
        </div>
    </div>
    <div class="tarjeta">
        <div class="icono icono-naranja">⏱</div>
        <div>
            <p>En Progreso</p>
            <span class="numero naranja"><?= $totalEnProgreso ?></span>
        </div>
    </div>
    <div class="tarjeta">
        <div class="icono icono-rojo">✖</div>
        <div>
            <p>Retrasadas</p>
            <span class="numero rojo"><?= $totalRetrasadas ?></span>
        </div>
    </div>
</div>

<div class="caja">
    <h3>Detalles de Actividades</h3>
    <table>
        <thead>
            <tr>
                <th>Actividad</th>
                <th>Zoocriadero</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Responsable</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($actividadesFiltradas) === 0): ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#999;">No hay actividades con esos filtros</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($actividadesFiltradas as $actividad): ?>
                <tr>
                    <td><?= $actividad['actividad'] ?></td>
                    <td><?= $actividad['zoocriadero'] ?></td>
                    <td><?= $actividad['inicio'] ?></td>
                    <td><?= $actividad['fin'] ?></td>
                    <td><?= $actividad['responsable'] ?></td>
                    <td>
                        <span class="badge <?= $actividad['estado'] === 'Completado' ? 'badge-verde' : ($actividad['estado'] === 'En proceso' ? 'badge-naranja' : 'badge-rojo') ?>">
                            <?= $actividad['estado'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    .caja {
        background-color: #ffffff;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.34);
        font-family: Arial, Helvetica, sans-serif;
    }

    .titulo-pagina {
        text-align: center;
        font-size: 22px;
        margin-top: 0;
        margin-bottom: 24px;
    }

    .caja h3 {
        margin-top: 0;
        margin-bottom: 16px;
    }

    .fila-filtros {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 20px;
    }

    .fila-filtros label {
        display: block;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .fila-filtros select,
    .fila-filtros input[type="date"] {
        padding: 8px 12px;
        border: 1px solid #d7dbe3;
        border-radius: 8px;
        min-width: 160px;
    }

    .btn-aplicar {
        background-color: #2f7dfa;
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-reportes {
        background-color: #ffffff;
        color: #2f7dfa;
        border: 1px solid #2f7dfa;
        padding: 10px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        margin-left: 8px;
    }

    .tarjetas {
        display: flex;
        gap: 20px;
    }

    .tarjeta {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .icono {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .icono-azul    { background-color: #e5f0ff; color: #2f7dfa; }
    .icono-verde   { background-color: #e3f9ec; color: #21a666; }
    .icono-naranja { background-color: #fff3df; color: #e0952d; }
    .icono-rojo    { background-color: #fde6e6; color: #e64545; }

    .tarjeta p {
        margin: 0 0 4px 0;
        color: #666;
        font-size: 14px;
    }

    .numero { font-size: 26px; font-weight: bold; }
    .azul    { color: #2f7dfa; }
    .verde   { color: #21a666; }
    .naranja { color: #e0952d; }
    .rojo    { color: #e64545; }

    table { width: 100%; border-collapse: collapse; }

    th {
        text-align: left;
        padding: 12px;
        color: #555;
        border-bottom: 2px solid #eef1f8;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #eef1f8;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        color: #ffffff;
        font-size: 13px;
        font-weight: bold;
    }

    .badge-verde   { background-color: #2ecc71; }
    .badge-naranja { background-color: #f5a623; }
    .badge-rojo    { background-color: #e74c3c; }
</style>