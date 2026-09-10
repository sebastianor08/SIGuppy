<?php
require_once __DIR__ . '/../../Controller/Reportes/GráficoDeSitiosPorDepósitoController.php';

$datos = obtenerDatosSitiosPorDeposito();
extract($datos);
?>

<div class="caja">
    <h2 class="titulo-pagina">Gráfico de Sitios por Tipo de Depósito</h2>
    <form method="GET">
        <div class="fila-filtros">
            <div>
                <label>Zoocriadero</label>
                <select name="zoocriadero">
                    <option value="">Todos</option>
                    <?php foreach ($listaZoocriaderos as $zoo): ?>
                        <option value="<?= $zoo ?>" <?= $filtroZoocriadero === $zoo ? 'selected' : '' ?>><?= $zoo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Estado del sitio</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <?php foreach ($listaEstados as $est): ?>
                        <option value="<?= $est ?>" <?= $filtroEstado === $est ? 'selected' : '' ?>><?= $est ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Tipo de depósito</label>
                <select name="tipo_deposito">
                    <option value="">Todos</option>
                    <?php foreach ($listaTipos as $tipo): ?>
                        <option value="<?= $tipo ?>" <?= $filtroTipo === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="<?= $filtroFechaInicio ?>">
            </div>
            <div>
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="<?= $filtroFechaFin ?>">
            </div>
            <div>
                <button type="submit" class="btn-aplicar">🔽 Filtrar</button>
            </div>
        </div>
    </form>
</div>

<div class="caja tarjetas">
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-azul">📍</div>
            <div>
                <p>Total de sitios</p>
                
            </div>
        </div>
        <p class="comparativa positivo">+12.0% vs periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,30 15,20 30,25 45,10 60,18 75,8 90,15 105,5 120,12" fill="none" stroke="#2f7dfa" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-verde">📦</div>
            <div>
                <p>Tipos de depósito</p>
                <span class="numero verde"><?= $totalTiposDeposito ?></span>
            </div>
        </div>
        <p class="comparativa positivo">+33.3% vs periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,28 15,18 30,24 45,12 60,20 75,10 90,16 105,6 120,14" fill="none" stroke="#21a666" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-azul">🗄️</div>
            <div>
                <p>Sitios con depósito</p>
                <span class="numero azul"><?= $totalConDeposito ?></span>
            </div>
        </div>
        <p class="comparativa positivo">+21.4% vs periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,26 15,16 30,22 45,10 60,18 75,8 90,14 105,4 120,12" fill="none" stroke="#2f7dfa" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-morado">⊘</div>
            <div>
                <p>Sin depósito</p>
                <span class="numero morado"><?= $totalSinDeposito ?></span>
            </div>
        </div>
        <p class="comparativa positivo">+0.0% vs periodo anterior</p>
        <svg viewBox="0 0 120 40" class="sparkline">
            <polyline points="0,20 15,20 30,20 45,20 60,20 75,20 90,20 105,20 120,20" fill="none" stroke="#6c5ce7" stroke-width="2" />
        </svg>
    </div>
</div>

<div class="fila-inferior">
    <div class="caja caja-gris caja-donut">
        <h3>Distribución de Sitios por Tipo de Depósito</h3>
        <div class="bloque-donut">
            <?php
                $gradiente = [];
                $acumulado = 0;
                foreach ($segmentos as $segmento) {
                    $inicioSegmento = $acumulado;
                    $acumulado += $segmento['porcentaje'];
                    $gradiente[] = "{$segmento['color']} {$inicioSegmento}% {$acumulado}%";
                }
                $cssGradiente = implode(', ', $gradiente);
            ?>
            <div class="donut" style="background: conic-gradient(<?= $cssGradiente ?>);">
                <div class="donut-centro">
                    <span>Total</span>
                    <strong><?= $totalConDeposito ?></strong>
                </div>
            </div>
            <ul class="leyenda-donut">
                <?php foreach ($segmentos as $segmento): ?>
                    <li>
                        <span class="punto-leyenda" style="background-color: <?= $segmento['color'] ?>;"></span>
                        <?= $segmento['tipo'] ?> — <?= $segmento['cantidad'] ?> (<?= $segmento['porcentaje'] ?>%)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="caja caja-gris caja-barras">
        <h3>Cantidad de Sitios por Tipo de Depósito</h3>
        <div class="grafica-barras">
            <?php foreach ($segmentos as $segmento): ?>
                <?php $alturaBarra = round(($segmento['cantidad'] / $valorMaximoBarra) * 100, 1); ?>
                <div class="grupo-barra">
                    <div class="barra" style="height: <?= $alturaBarra ?>%; background-color: <?= $segmento['color'] ?>;">
                        <span><?= $segmento['cantidad'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="etiquetas-barras">
            <?php foreach ($segmentos as $segmento): ?>
                <span><?= $segmento['tipo'] ?></span>
            <?php endforeach; ?>
        </div>
        <p class="nota-grafica">El gráfico muestra la cantidad y porcentaje de sitios de terreno según el tipo de depósito asignado.</p>
    </div>
</div>

<div class="caja caja-gris">
    <h3>Detalle por Sitio</h3>
    <table>
        <thead>
            <tr>
                <th>ID Sitio</th>
                <th>Nombre del Sitio</th>
                <th>Zoocriadero</th>
                <th>Tipo de depósito</th>
                <th>Cantidad de tanques</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sitiosPagina) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center; color:#888;">No hay sitios con esos filtros</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($sitiosPagina as $sitio): ?>
                <tr>
                    <td><?= $sitio['id'] ?></td>
                    <td><?= $sitio['nombre'] ?></td>
                    <td><?= $sitio['zoocriadero'] ?></td>
                    <td><?= $sitio['tipo'] !== '' ? $sitio['tipo'] : 'Sin depósito' ?></td>
                    <td><?= $sitio['tanques'] ?></td>
                    <td>
                        <span class="badge <?= $sitio['estado'] === 'Activo' ? 'badge-verde' : 'badge-rojo' ?>">
                            <?= $sitio['estado'] ?>
                        </span>
                    </td>
                    <td><span class="accion-ver">👁</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pie-tabla">
        <span>Mostrando <?= $desde ?>-<?= $hasta ?> de <?= $totalSitios ?> sitios</span>
        <div class="paginacion">
            <a class="boton-pagina" href="?<?= http_build_query(array_merge($_GET, ['pagina' => max(1, $paginaActual - 1)])) ?>">&lt;</a>
            <?php for ($n = 1; $n <= $totalPaginas; $n++): ?>
                <a class="boton-pagina <?= $n === $paginaActual ? 'activo' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $n])) ?>"><?= $n ?></a>
            <?php endfor; ?>
            <a class="boton-pagina" href="?<?= http_build_query(array_merge($_GET, ['pagina' => min($totalPaginas, $paginaActual + 1)])) ?>">&gt;</a>
        </div>
    </div>
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
        gap: 16px;
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
        min-width: 140px;
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

    .icono-azul   { background-color: #e5f0ff; color: #2f7dfa; }
    .icono-verde  { background-color: #e3f9ec; color: #21a666; }
    .icono-morado { background-color: #efe9fd; color: #6c5ce7; }

    .numero { font-size: 24px; font-weight: bold; }
    .azul   { color: #2f7dfa; }
    .verde  { color: #21a666; }
    .morado { color: #6c5ce7; }

    .comparativa { font-size: 12px; margin: 8px 0 0 0; }
    .positivo { color: #21a666; }

    .sparkline {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 110px;
        height: 40px;
    }

    .fila-inferior { display: flex; gap: 20px; align-items: stretch; }
    .fila-inferior .caja { margin-bottom: 0; flex: 1; }

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

    .grafica-barras {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        height: 160px;
        border-bottom: 2px solid #c7c7cf;
    }

    .grupo-barra { display: flex; align-items: flex-end; height: 100%; }
    .barra { width: 40px; border-radius: 4px 4px 0 0; position: relative; }
    .barra span { position: absolute; top: -18px; left: 0; right: 0; text-align: center; font-size: 12px; font-weight: bold; }

    .etiquetas-barras { display: flex; justify-content: space-around; margin-top: 8px; font-size: 12px; }

    .nota-grafica { font-size: 12px; color: #777; margin-top: 14px; margin-bottom: 0; }

    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 10px; color: #555; border-bottom: 2px solid #d7d7dc; }
    td { padding: 10px; border-bottom: 1px solid #d7d7dc; }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        color: #ffffff;
        font-size: 13px;
        font-weight: bold;
    }
    .badge-verde { background-color: #2ecc71; }
    .badge-rojo  { background-color: #e74c3c; }

    .accion-ver { cursor: pointer; font-size: 16px; }

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
</style>