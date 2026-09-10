<?php
require_once __DIR__ . '/../../Controller/Reportes/ActividadesPorAuxiliarController.php';

$datos = obtenerDatosActividadesPorAuxiliar();
extract($datos);
?>

<div class="caja">
    <h2 class="titulo-pagina">Actividades De Terreno Por Auxiliar Responsable</h2>
    <h3>Filtros</h3>
    <form method="GET">
        <div class="fila-filtros">
            <div>
                <label>Auxiliar</label>
                <select name="auxiliar">
                    <option value="">Todos</option>
                    <?php foreach ($listaAuxiliares as $aux): ?>
                        <option value="<?= $aux ?>" <?= $filtroAuxiliar === $aux ? 'selected' : '' ?>><?= $aux ?></option>
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
                <button type="button" class="btn-reportes">Generar Reportes</button>
                <button type="submit" class="btn-aplicar">Aplicar Filtros</button>
            </div>
        </div>
    </form>
</div>

<div class="caja tarjetas">
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-azul">📋</div>
            <div>
                <p>Actividades Totales</p>
                <span class="numero azul"><?= $totalActividades ?></span>
            </div>
        </div>
        <p class="comparativa positivo">12.8% vs Periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,30 15,20 30,25 45,10 60,18 75,8 90,15 105,5 120,12" fill="none" stroke="#2f7dfa" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-verde">✔</div>
            <div>
                <p>Actividades Completas</p>
                <span class="numero verde"><?= $totalCompletas ?></span>
            </div>
        </div>
        <p class="comparativa positivo">13.8% vs Periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,28 15,18 30,24 45,12 60,20 75,10 90,16 105,6 120,14" fill="none" stroke="#21a666" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-naranja">⏱</div>
            <div>
                <p>En Progreso</p>
                <span class="numero naranja"><?= $totalEnProgreso ?></span>
            </div>
        </div>
        <p class="comparativa negativo">-8.3% vs Periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,15 15,25 30,10 45,22 60,8 75,20 90,6 105,18 120,12" fill="none" stroke="#e0952d" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-rojo">✖</div>
            <div>
                <p>Retrasadas</p>
                <span class="numero rojo"><?= $totalRetrasadas ?></span>
            </div>
        </div>
        <p class="comparativa negativo">-25.0% vs Periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,10 15,22 30,8 45,20 60,6 75,18 90,4 105,16 120,10" fill="none" stroke="#e64545" stroke-width="2" />
        </svg>
    </div>
</div>

<div class="fila-inferior">
    <div class="caja caja-gris caja-desempeno">
        <h3>Desempeño por Auxiliar</h3>
        <table>
            <thead>
                <tr>
                    <th>Auxiliar</th>
                    <th>Actividades Completadas</th>
                    <th>En progreso</th>
                    <th>Retrasadas</th>
                    <th>% Cumplimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($auxiliaresPagina) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#888;">No hay auxiliares con esos filtros</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($auxiliaresPagina as $fila): ?>
                    <tr>
                        <td><?= $fila['auxiliar'] ?></td>
                        <td><?= $fila['completadas'] ?></td>
                        <td><?= $fila['enProgreso'] ?></td>
                        <td><?= $fila['retrasadas'] ?></td>
                        <td>
                            <div class="celda-cumplimiento">
                                <span><?= $fila['cumplimiento'] ?>%</span>
                                <div class="barra-fondo">
                                    <div class="barra-relleno" style="width: <?= $fila['cumplimiento'] ?>%;"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fila-total">
                    <td>Total</td>
                    <td><?= $totalCompletas ?></td>
                    <td><?= $totalEnProgreso ?></td>
                    <td><?= $totalRetrasadas ?></td>
                    <td>
                        <div class="celda-cumplimiento">
                            <span><?= $cumplimientoGeneral ?>%</span>
                            <div class="barra-fondo">
                                <div class="barra-relleno" style="width: <?= $cumplimientoGeneral ?>%;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pie-tabla">
            <span>Mostrando <?= $desde ?>-<?= $hasta ?> de <?= $totalAuxiliares ?> auxiliares</span>
            <div class="paginacion">
                <a class="boton-pagina" href="?<?= http_build_query(array_merge($_GET, ['pagina' => max(1, $paginaActual - 1)])) ?>">&lt;</a>
                <?php for ($n = 1; $n <= $totalPaginas; $n++): ?>
                    <a class="boton-pagina <?= $n === $paginaActual ? 'activo' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $n])) ?>"><?= $n ?></a>
                <?php endfor; ?>
                <a class="boton-pagina" href="?<?= http_build_query(array_merge($_GET, ['pagina' => min($totalPaginas, $paginaActual + 1)])) ?>">&gt;</a>
            </div>
        </div>
    </div>

    <div class="caja caja-gris caja-donut">
        <h3>Distribución de Carga de Trabajo</h3>
        <div class="bloque-donut">
            <?php
                $gradiente = [];
                $acumulado = 0;
                foreach ($segmentosDonut as $segmento) {
                    $inicioSegmento = $acumulado;
                    $acumulado += $segmento['porcentaje'];
                    $gradiente[] = "{$segmento['color']} {$inicioSegmento}% {$acumulado}%";
                }
                $cssGradiente = implode(', ', $gradiente);
            ?>
            <div class="donut" style="background: conic-gradient(<?= $cssGradiente ?>);">
                <div class="donut-centro">
                    <span>Total</span>
                    <strong><?= $totalActividades ?></strong>
                </div>
            </div>
            <ul class="leyenda-donut">
                <?php foreach ($segmentosDonut as $segmento): ?>
                    <li>
                        <span class="punto-leyenda" style="background-color: <?= $segmento['color'] ?>;"></span>
                        <?= $segmento['auxiliar'] ?> — <?= $segmento['total'] ?> (<?= $segmento['porcentaje'] ?>%)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <p class="nota-donut">Los porcentajes representan la proporción de actividades asignadas a cada auxiliar.</p>
    </div>
</div>

<div class="caja nota-inferior">
    El % de cumplimiento se calcula como: (Actividades completadas / Total de actividades asignadas) x 100
</div>

<style>
    .caja {
        background-color: #ffffff;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        font-family: Arial, Helvetica, sans-serif;
    }

    .caja-gris { background-color: #e9e9ee; }

    .titulo-pagina {
        text-align: center;
        font-size: 22px;
        margin-top: 0;
        margin-bottom: 24px;
    }

    .caja h3 { margin-top: 0; margin-bottom: 16px; }

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
        margin-right: 8px;
    }

    .tarjetas { display: flex; gap: 20px; }

    .tarjeta { flex: 1; position: relative; }

    .cabecera-tarjeta { display: flex; align-items: center; gap: 12px; }
    .cabecera-tarjeta p { margin: 0 0 4px 0; color: #666; font-size: 14px; }

    .icono {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .icono-azul    { background-color: #e5f0ff; color: #2f7dfa; }
    .icono-verde   { background-color: #e3f9ec; color: #21a666; }
    .icono-naranja { background-color: #fff3df; color: #e0952d; }
    .icono-rojo    { background-color: #fde6e6; color: #e64545; }

    .numero { font-size: 24px; font-weight: bold; }
    .azul    { color: #2f7dfa; }
    .verde   { color: #21a666; }
    .naranja { color: #e0952d; }
    .rojo    { color: #e64545; }

    .comparativa { font-size: 12px; margin: 8px 0 0 0; }
    .positivo { color: #21a666; }
    .negativo { color: #e64545; }

    .sparkline {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 110px;
        height: 40px;
    }

    .fila-inferior { display: flex; gap: 20px; align-items: flex-start; }
    .fila-inferior .caja { margin-bottom: 0; }
    .caja-desempeno { flex: 3; }
    .caja-donut { flex: 2; }

    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 10px; color: #555; border-bottom: 2px solid #d7d7dc; }
    td { padding: 10px; border-bottom: 1px solid #d7d7dc; }
    tr.fila-total td { font-weight: bold; border-bottom: none; }

    .celda-cumplimiento { display: flex; align-items: center; gap: 10px; }
    .barra-fondo { flex: 1; height: 8px; background-color: #dcdce3; border-radius: 6px; overflow: hidden; }
    .barra-relleno { height: 100%; background-color: #2f7dfa; }

    .pie-tabla {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 14px;
        font-size: 13px;
        color: #555;
    }

    .paginacion { display: flex; gap: 6px; }

    .boton-pagina {
        display: inline-block;
        min-width: 26px;
        text-align: center;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #cfcfd6;
        color: #333;
        text-decoration: none;
        font-size: 13px;
    }

    .boton-pagina.activo {
        background-color: #2f7dfa;
        border-color: #2f7dfa;
        color: #ffffff;
    }

    .bloque-donut { display: flex; align-items: center; gap: 20px; }

    .donut {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        position: relative;
        flex-shrink: 0;
    }

    .donut-centro {
        position: absolute;
        top: 20px; left: 20px; right: 20px; bottom: 20px;
        background-color: #e9e9ee;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .donut-centro span { font-size: 12px; color: #888; }
    .donut-centro strong { font-size: 20px; }

    .leyenda-donut { list-style: none; margin: 0; padding: 0; font-size: 13px; }
    .leyenda-donut li { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
    .punto-leyenda { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    .nota-donut { font-size: 12px; color: #777; margin-top: 14px; margin-bottom: 0; }

    .nota-inferior { font-size: 13px; color: #555; }
</style>