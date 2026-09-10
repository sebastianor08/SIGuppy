<?php
require_once __DIR__ . '/../../Controller/Reportes/PesesNacidos-MuertosPorTanqueController.php';

$datos = obtenerDatosPecesNacidosMuertosPorTanque();
extract($datos);
?>

<div class="caja">
    <h2 class="titulo-pagina">Reporte de peces nacidos o muertos por tanque</h2>
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
                <label>Tanque</label>
                <select name="tanque">
                    <option value="">Todos</option>
                    <?php foreach ($listaTanques as $tq): ?>
                        <option value="<?= $tq ?>" <?= $filtroTanque === $tq ? 'selected' : '' ?>>
                            <?= $tq ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Hembra/Macho</label>
                <select name="sexo">
                    <option value="">Todos</option>
                    <option value="Hembra" <?= $filtroSexo === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                    <option value="Macho"  <?= $filtroSexo === 'Macho'  ? 'selected' : '' ?>>Macho</option>
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

<div class="caja tarjetas-resumen">
    <div class="tarjeta-resumen">
        <p>Peces nacidos</p>
        <span class="numero azul"><?= number_format($totalNacidos) ?></span>
        <p class="comparativa">15.4% vs Periodo anterior</p>
        <!-- Esta línea (sparkline) es decorativa por ahora, con puntos fijos -->
        <svg viewBox="0 0 120 40">
            <polyline points="0,30 15,20 30,25 45,10 60,18 75,8 90,15 105,5 120,12"
                      fill="none" stroke="#2f7dfa" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta-resumen">
        <p>Peces muertos</p>
        <span class="numero rojo"><?= number_format($totalMuertos) ?></span>
        <p class="comparativa">6.3% vs Periodo anterior</p>
        <svg viewBox="0 0 120 40">
            <polyline points="0,25 15,15 30,22 45,12 60,20 75,10 90,18 105,8 120,15"
                      fill="none" stroke="#6c5ce7" stroke-width="2" />
        </svg>
    </div>
    <div class="tarjeta-resumen">
        <p>Tasa de mortalidad</p>
        <span class="numero morado"><?= number_format($tasaMortalidadGeneral, 2) ?>%</span>
        <p class="comparativa">-1.2% vs Periodo anterior</p>
        <svg viewBox="0 0 120 40">
            <polyline points="0,20 15,10 30,18 45,8 60,16 75,6 90,14 105,4 120,10"
                      fill="none" stroke="#6c5ce7" stroke-width="2" />
        </svg>
    </div>
</div>

<div class="fila-inferior">
    <div class="caja caja-gris caja-tabla">
        <h3>Tabla de datos</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanques</th>
                    <th>Zoocriadero</th>
                    <th>Nacidos</th>
                    <th>Muertos</th>
                    <th>Tasa de mortalidad</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($resumenPorTanque) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#888;">No hay registros con esos filtros</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($resumenPorTanque as $fila): ?>
                    <?php
                        $totalFila = $fila['nacidos'] + $fila['muertos'];
                        $tasaFila = $totalFila > 0 ? ($fila['muertos'] / $totalFila) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= $fila['tanque'] ?></td>
                        <td><?= $fila['zoocriadero'] ?></td>
                        <td><?= $fila['nacidos'] ?></td>
                        <td><?= $fila['muertos'] ?></td>
                        <td><?= number_format($tasaFila, 1) ?>%</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fila-total">
                    <td>Total</td>
                    <td></td>
                    <td><?= number_format($totalNacidos) ?></td>
                    <td><?= number_format($totalMuertos) ?></td>
                    <td><?= number_format($tasaMortalidadGeneral, 2) ?>%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="caja caja-gris caja-grafica">
        <h3>Comparativa Nacidos vs. Muertos por Tanque</h3>
        <div class="leyenda-grafica">
            <span><span class="punto-leyenda punto-nacidos"></span>Nacidos</span>
            <span><span class="punto-leyenda punto-muertos"></span>Muertos</span>
        </div>
        <div class="grafica-barras">
            <?php foreach ($resumenPorTanque as $fila): ?>
                <?php
                    $alturaNacidos = round(($fila['nacidos'] / $valorMaximoGrafico) * 100, 1);
                    $alturaMuertos = round(($fila['muertos'] / $valorMaximoGrafico) * 100, 1);
                ?>
                <div class="grupo-barras">
                    <div class="barra barra-nacidos" style="height: <?= $alturaNacidos ?>%;">
                        <span><?= $fila['nacidos'] ?></span>
                    </div>
                    <div class="barra barra-muertos" style="height: <?= $alturaMuertos ?>%;">
                        <span><?= $fila['muertos'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="etiquetas-tanques">
            <?php foreach ($resumenPorTanque as $fila): ?>
                <span><?= $fila['tanque'] ?></span>
            <?php endforeach; ?>
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

    /* Tarjetas de resumen (Nacidos / Muertos / Tasa) */
    .tarjetas-resumen { display: flex; gap: 20px; }

    .tarjeta-resumen { flex: 1; position: relative; }
    .tarjeta-resumen p { margin: 0 0 6px 0; font-weight: bold; }
    .tarjeta-resumen .numero { font-size: 26px; font-weight: bold; }
    .tarjeta-resumen .comparativa { color: #21a666; font-size: 13px; margin-top: 6px; }
    .tarjeta-resumen svg { position: absolute; top: 0; right: 0; width: 130px; height: 50px; }

    .azul   { color: #2f7dfa; }
    .rojo   { color: #e64545; }
    .morado { color: #6c5ce7; }

    /* Fila inferior: tabla + gráfica */
    .fila-inferior { display: flex; gap: 20px; align-items: flex-start; }
    .fila-inferior .caja { margin-bottom: 0; }
    .caja-tabla { flex: 3; }
    .caja-grafica { flex: 2; }

    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 10px; color: #555; border-bottom: 2px solid #d7d7dc; }
    td { padding: 10px; border-bottom: 1px solid #d7d7dc; }
    tr.fila-total td { font-weight: bold; border-bottom: none; }

    /* Gráfica de barras hecha solo con CSS */
    .leyenda-grafica { display: flex; gap: 18px; margin-bottom: 16px; font-size: 13px; }
    .punto-leyenda { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 6px; }
    .punto-nacidos { background-color: #5bc9e8; }
    .punto-muertos { background-color: #6c5ce7; }

    .grafica-barras {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        height: 220px;
        border-bottom: 2px solid #c7c7cf;
    }

    .grupo-barras { display: flex; align-items: flex-end; gap: 6px; height: 100%; }
    .barra { width: 28px; border-radius: 4px 4px 0 0; position: relative; }
    .barra span { position: absolute; top: -18px; left: 0; right: 0; text-align: center; font-size: 11px; }
    .barra-nacidos { background-color: #5bc9e8; }
    .barra-muertos { background-color: #6c5ce7; }

    .etiquetas-tanques { display: flex; justify-content: space-around; margin-top: 8px; font-size: 13px; }
</style>