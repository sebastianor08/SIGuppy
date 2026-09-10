<?php
require_once __DIR__ . '/../../Controller/Reportes/ActividadesDeTerrenoPorTipoController.php';

$datos = obtenerDatosActividadesDeTerrenoPorTipo();
extract($datos);
?>

<div class="caja">
    <h2 class="titulo-pagina">Reporte de Actividad de Terreno por Tipo</h2>
    <h3>Filtros</h3>
    <form method="GET">
        <div class="fila-filtros">
            <div>
                <label>Tipo de actividad</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <?php foreach ($listaTipos as $tipo): ?>
                        <option value="<?= $tipo ?>" <?= $filtroTipo === $tipo ? 'selected' : '' ?>>
                            <?= $tipo ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Zoocriadero</label>
                <select name="zoocriadero">
                    <option value="">Todas</option>
                    <?php foreach ($listaZoocriaderos as $zoo): ?>
                        <option value="<?= $zoo ?>" <?= $filtroZoocriadero === $zoo ? 'selected' : '' ?>>
                            <?= $zoo ?>
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

<div class="fila-inferior">
    <div class="caja caja-tabla">
        <h3>Actividades por Tipo</h3>
        <table>
            <thead>
                <tr>
                    <th>Tipo de actividad</th>
                    <th>Completadas</th>
                    <th>En progreso</th>
                    <th>Retrasadas</th>
                    <th>Total</th>
                    <th>% del total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumenPorTipo as $fila): ?>
                    <tr>
                        <td>
                            <span class="punto-tipo" style="background-color: <?= $fila['color'] ?>;"></span>
                            <?= $fila['tipo'] ?>
                        </td>
                        <td><?= $fila['completadas'] ?></td>
                        <td><?= $fila['enProgreso'] ?></td>
                        <td><?= $fila['retrasadas'] ?></td>
                        <td><?= $fila['total'] ?></td>
                        <td><?= $fila['porcentaje'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fila-total">
                    <td>Total</td>
                    <td><?= $totalCompletas ?></td>
                    <td><?= $totalEnProgreso ?></td>
                    <td><?= $totalRetrasadas ?></td>
                    <td><?= $totalActividades ?></td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
        <p class="pie-tabla">Mostrando 1 - <?= count($resumenPorTipo) ?> de <?= count($resumenPorTipo) ?> tipos</p>
    </div>

    <div class="caja caja-grafica">
        <h3>Evolución de Actividades por Tipo</h3>
        <div class="leyenda-grafica">
            <?php foreach ($seriesEvolucion as $serie): ?>
                <span>
                    <span class="punto-leyenda" style="background-color: <?= $serie['color'] ?>;"></span>
                    <?= $serie['tipo'] ?>
                </span>
            <?php endforeach; ?>
        </div>
        <svg viewBox="0 0 560 200" class="grafica-lineas">
            <line x1="40" y1="10" x2="40" y2="170" class="linea-eje" />
            <line x1="40" y1="170" x2="540" y2="170" class="linea-eje" />
            <text x="30" y="14" class="texto-eje" text-anchor="end">40</text>
            <text x="30" y="54" class="texto-eje" text-anchor="end">30</text>
            <text x="30" y="94" class="texto-eje" text-anchor="end">20</text>
            <text x="30" y="134" class="texto-eje" text-anchor="end">10</text>
            <text x="30" y="174" class="texto-eje" text-anchor="end">0</text>
            <?php foreach ($seriesEvolucion as $serie): ?>
                <polyline points="<?= $serie['puntos'] ?>" fill="none" stroke="<?= $serie['color'] ?>" stroke-width="2.5" />
                <?php foreach (explode(' ', $serie['puntos']) as $punto): ?>
                    <?php [$x, $y] = explode(',', $punto); ?>
                    <circle cx="<?= $x ?>" cy="<?= $y ?>" r="3" fill="<?= $serie['color'] ?>" />
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php foreach ($fechasEvolucion as $indice => $fecha): ?>
                <text x="<?= 40 + ($indice * 125) ?>" y="190" class="texto-eje" text-anchor="middle">
                    <?= $fecha ?>
                </text>
            <?php endforeach; ?>
        </svg>
        <p class="nota-grafica">Las cifras representan el número de actividades completadas por día.</p>
    </div>
</div>

<style>
    .caja {
        background-color: #ffffff;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
        min-width: 150px;
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

    .fila-inferior {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .fila-inferior .caja {
        margin-bottom: 0;
    }

    .caja-tabla {
        flex: 3;
    }

    .caja-grafica {
        flex: 2;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

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

    tr.fila-total td {
        font-weight: bold;
        border-bottom: none;
    }

    .punto-tipo {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .pie-tabla {
        margin: 16px 0 0 0;
        color: #888;
        font-size: 13px;
    }

    .leyenda-grafica {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .punto-leyenda {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .grafica-lineas {
        width: 100%;
        height: auto;
    }

    .linea-eje {
        stroke: #d7d7dc;
        stroke-width: 1;
    }

    .texto-eje {
        font-size: 10px;
        fill: #999;
    }

    .nota-grafica {
        margin: 10px 0 0 0;
        color: #999;
        font-size: 12px;
        font-style: italic;
    }
</style>