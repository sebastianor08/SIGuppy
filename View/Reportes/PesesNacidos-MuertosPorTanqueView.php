<?php
require_once __DIR__ . '/../../Controller/Reportes/PesesNacidos-MuertosPorTanqueController.php';

$datos = obtenerDatosPecesNacidosMuertosPorTanque();
extract($datos);

$basePath  = '../../';
$pageTitle = 'Peces nacidos o muertos por tanque';
$bodyPage  = 'rep-peces-tanque';
include '../partials/head.php';
?>
<div class="wrapper">
    <?php $rutaBase = '../../'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <div class="main-panel">
        <?php include '../partials/topbar.php'; ?>
        <div class="container">
            <div class="page-inner">

                <div class="caja">
                    <h2 class="titulo-pagina">Reporte de peces nacidos o muertos por tanque</h2>
                    <h3>Filtros</h3>
                    <form method="GET">
                        <div class="fila-filtros">
                            <div>
                                <label>Zoocriadero</label>
                                <select name="zoocriadero" class="form-select">
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
                                <select name="tanque" class="form-select">
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
                                <select name="sexo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Hembra" <?= $filtroSexo === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                                    <option value="Macho" <?= $filtroSexo === 'Macho'  ? 'selected' : '' ?>>Macho</option>
                                </select>
                            </div>
                            <div>
                                <label>Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="<?= $filtroFechaInicio ?>">
                            </div>
                            <div>
                                <label>Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" value="<?= $filtroFechaFin ?>">
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
                        <p class="comparativa <?= $comparativas['totalNacidos']['clase'] ?>"><?= $comparativas['totalNacidos']['texto'] ?></p>
                    </div>
                    <div class="tarjeta-resumen">
                        <p>Peces muertos</p>
                        <span class="numero rojo"><?= number_format($totalMuertos) ?></span>
                        <p class="comparativa <?= $comparativas['totalMuertos']['clase'] ?>"><?= $comparativas['totalMuertos']['texto'] ?></p>
                    </div>
                    <div class="tarjeta-resumen">
                        <p>Tasa de mortalidad</p>
                        <span class="numero morado"><?= number_format($tasaMortalidadGeneral, 2) ?>%</span>
                        <p class="comparativa <?= $comparativas['tasaMortalidadGeneral']['clase'] ?>"><?= $comparativas['tasaMortalidadGeneral']['texto'] ?></p>
                    </div>
                </div>

                <div class="fila-inferior">
                    <div class="caja caja-gris caja-tabla">
                        <h3>Tabla de datos</h3>
                        <div class="tabla-responsive">
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
                                        <tr class="sig-empty-row">
                                        <td colspan="5">No hay registros con esos filtros</td></tr>
                                    
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
                    </div>

                    <?php
                    // Ancho real que necesitan las barras (62px por tanque + 18px de separación entre ellas).
                    // No se usa como mínimo fijo de la caja (eso rompería en pantallas angostas);
                    // solo dimensiona el contenido que se desplaza con scroll dentro de .grafica-contenedor.
                    $cantidadTanques = count($resumenPorTanque);
                    $anchoMinimoGrafica = $cantidadTanques * 62 + max(0, $cantidadTanques - 1) * 18;
                    ?>
                    <div class="caja caja-gris caja-grafica">
                        <h3>Comparativa Nacidos vs. Muertos por Tanque</h3>
                        <div class="leyenda-grafica">
                            <span><span class="punto-leyenda punto-nacidos"></span>Nacidos</span>
                            <span><span class="punto-leyenda punto-muertos"></span>Muertos</span>
                        </div>
                        <div class="grafica-contenedor">
                            <div class="grafica-barras" style="min-width: <?= $anchoMinimoGrafica ?>px;">
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
                            <div class="etiquetas-tanques" style="min-width: <?= $anchoMinimoGrafica ?>px;">
                                <?php foreach ($resumenPorTanque as $fila): ?>
                                    <span class="etiqueta-tanque"><?= $fila['tanque'] ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .caja {
                        background-color: #ffffff;
                        border-radius: 14px;
                        padding: 24px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                        font-family: Arial, Helvetica, sans-serif;
                    }

                    .caja-gris {
                        background-color: #e9e9ee;
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

                    .btn-excel {
                        background-color: #ffffff;
                        color: #21a666;
                        border: 1px solid #21a666;
                        padding: 10px 18px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: bold;
                        margin-right: 8px;
                    }

                    .btn-excel:hover {
                        background-color: rgba(33, 166, 102, 0.1);
                    }

                    body[data-background-color="dark"] .btn-excel {
                        background-color: transparent;
                    }

                    /* Tarjetas de resumen (Nacidos / Muertos / Tasa) */
                    .tarjetas-resumen {
                        display: flex;
                        gap: 20px;
                    }

                    .tarjeta-resumen {
                        flex: 1;
                        position: relative;
                    }

                    .tarjeta-resumen p {
                        margin: 0 0 6px 0;
                        font-weight: bold;
                    }

                    .tarjeta-resumen .numero {
                        font-size: 26px;
                        font-weight: bold;
                    }

                    .tarjeta-resumen .comparativa {
                        font-size: 13px;
                        margin-top: 6px;
                    }

                    .tarjeta-resumen .comparativa.positivo {
                        color: #21a666;
                    }

                    .tarjeta-resumen .comparativa.negativo {
                        color: #e64545;
                    }

                    .tarjeta-resumen .comparativa.neutro {
                        color: #888888;
                    }

                    .tarjeta-resumen svg {
                        position: absolute;
                        top: 0;
                        right: 0;
                        width: 130px;
                        height: 50px;
                    }

                    .azul {
                        color: #2f7dfa;
                    }

                    .rojo {
                        color: #e64545;
                    }

                    .morado {
                        color: #6c5ce7;
                    }

                    /* Fila inferior: tabla + gráfica, en grilla (no flex).
                       auto-fit + minmax hace que, si las dos cajas no caben una al lado
                       de la otra (pantallas medianas, con la barra lateral abierta), la
                       gráfica baje a su propia fila sola, sin necesidad de calcular un
                       punto de quiebre a mano: se ajusta al ancho real disponible. */
                    .fila-inferior {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(min(380px, 100%), 1fr));
                        gap: 20px;
                        align-items: start;
                    }

                    .fila-inferior .caja {
                        margin-bottom: 0;
                    }

                    .caja-tabla,
                    .caja-grafica {
                        min-width: 0;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }

                    th {
                        text-align: left;
                        padding: 10px;
                        color: #555;
                        border-bottom: 2px solid #d7d7dc;
                    }

                    td {
                        padding: 10px;
                        border-bottom: 1px solid #d7d7dc;
                    }

                    tr.fila-total td {
                        font-weight: bold;
                        border-bottom: none;
                    }

                    /* Gráfica de barras hecha solo con CSS */
                    .leyenda-grafica {
                        display: flex;
                        gap: 18px;
                        margin-bottom: 16px;
                        font-size: 13px;
                    }

                    .punto-leyenda {
                        display: inline-block;
                        width: 12px;
                        height: 12px;
                        border-radius: 3px;
                        margin-right: 6px;
                    }

                    .punto-nacidos {
                        background-color: #5bc9e8;
                    }

                    .punto-muertos {
                        background-color: #6c5ce7;
                    }

                    /* Envuelve barras + etiquetas: si no caben en la caja, se desplazan
                       con scroll horizontal en vez de salirse de la pantalla. */
                    .grafica-contenedor {
                        overflow-x: auto;
                        padding-bottom: 2px;
                    }

                    .grafica-barras {
                        display: flex;
                        align-items: flex-end;
                        justify-content: space-around;
                        gap: 18px;
                        height: 220px;
                        border-bottom: 2px solid #c7c7cf;
                    }

                    .grupo-barras {
                        display: flex;
                        align-items: flex-end;
                        justify-content: center;
                        gap: 6px;
                        height: 100%;
                        flex: 0 0 auto;
                        width: 62px;
                    }

                    .barra {
                        width: 28px;
                        border-radius: 4px 4px 0 0;
                        position: relative;
                    }

                    .barra span {
                        position: absolute;
                        top: -18px;
                        left: 0;
                        right: 0;
                        text-align: center;
                        font-size: 11px;
                    }

                    .barra-nacidos {
                        background-color: #5bc9e8;
                    }

                    .barra-muertos {
                        background-color: #6c5ce7;
                    }

                    .etiquetas-tanques {
                        display: flex;
                        justify-content: space-around;
                        gap: 18px;
                        margin-top: 8px;
                        font-size: 13px;
                    }

                    .etiqueta-tanque {
                        flex: 0 0 auto;
                        width: 62px;
                        text-align: center;
                    }

                    .tabla-responsive {
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                    }

                    @media (max-width: 768px) {
                        .tarjetas-resumen {
                            flex-direction: column;
                        }

                        .fila-inferior {
                            flex-direction: column;
                        }
                    }
                </style>

            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = [];
include '../partials/footer.php';
?>
</body>

</html>