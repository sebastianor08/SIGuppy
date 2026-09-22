<?php
require_once __DIR__ . '/../../Controller/Reportes/GráficoDeSitiosPorDepósitoController.php';

$datos = obtenerDatosSitiosPorDeposito();
extract($datos);

$basePath  = '../../';
$pageTitle = 'Gráfico de Sitios por Tipo de Depósito';
$bodyPage  = 'rep-sitios-deposito';
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
                    <h2 class="titulo-pagina">Gráfico de Sitios por Tipo de Depósito</h2>
                    <form method="GET">
                        <div class="fila-filtros">
                            <div>
                                <label>Zoocriadero</label>
                                <select name="zoocriadero" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($listaZoocriaderos as $zoo): ?>
                                        <option value="<?= $zoo ?>" <?= $filtroZoocriadero === $zoo ? 'selected' : '' ?>><?= $zoo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Estado del sitio</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($listaEstados as $est): ?>
                                        <option value="<?= $est ?>" <?= $filtroEstado === $est ? 'selected' : '' ?>><?= $est ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Tipo de depósito</label>
                                <select name="tipo_deposito" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($listaTipos as $tipo): ?>
                                        <option value="<?= $tipo ?>" <?= $filtroTipo === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="<?= $filtroFechaInicio ?>">
                            </div>
                            <div>
                                <label>Fecha fin</label>
                                <input type="date" name="fecha_fin" class="form-control" value="<?= $filtroFechaFin ?>">
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
                            <div class="icono icono-azul">📍</div>
                            <div>
                                <p>Total de sitios</p>
                                <span class="numero azul"><?= $totalSitios ?></span>
                            </div>
                        </div>
                        <p class="comparativa <?= $comparativas['totalSitios']['clase'] ?>"><?= $comparativas['totalSitios']['texto'] ?></p>

                    </div>
                    <div class="tarjeta">
                        <div class="cabecera-tarjeta">
                            <div class="icono icono-verde">📦</div>
                            <div>
                                <p>Tipos de depósito</p>
                                <span class="numero verde"><?= $totalTiposDeposito ?></span>
                            </div>
                        </div>
                        <p class="comparativa <?= $comparativas['totalTiposDeposito']['clase'] ?>"><?= $comparativas['totalTiposDeposito']['texto'] ?></p>
                    </div>
                    <div class="tarjeta">
                        <div class="cabecera-tarjeta">
                            <div class="icono icono-azul">🗄️</div>
                            <div>
                                <p>Sitios con depósito</p>
                                <span class="numero azul"><?= $totalConDeposito ?></span>
                            </div>
                        </div>
                        <p class="comparativa <?= $comparativas['totalConDeposito']['clase'] ?>"><?= $comparativas['totalConDeposito']['texto'] ?></p>
                    </div>
                    <div class="tarjeta">
                        <div class="cabecera-tarjeta">
                            <div class="icono icono-morado">⊘</div>
                            <div>
                                <p>Sin depósito</p>
                                <span class="numero morado"><?= $totalSinDeposito ?></span>
                            </div>
                        </div>
                        <p class="comparativa <?= $comparativas['totalSinDeposito']['clase'] ?>"><?= $comparativas['totalSinDeposito']['texto'] ?></p>
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
                    <div class="tabla-responsive">
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
                                    <tr class="sig-empty-row">
                                        <td colspan="7">No hay sitios con esos filtros</td>

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
                                        <td>
                                            <div class="table-actions">
                                                <button type="button" class="btn-icon" data-action="ver-sitio"
                                                    data-id="<?= htmlspecialchars($sitio['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($sitio['nombre']) ?>"
                                                    data-zoocriadero="<?= htmlspecialchars($sitio['zoocriadero']) ?>"
                                                    data-tipo="<?= htmlspecialchars($sitio['tipo'] !== '' ? $sitio['tipo'] : 'Sin depósito') ?>"
                                                    data-tanques="<?= htmlspecialchars($sitio['tanques']) ?>"
                                                    data-estado="<?= htmlspecialchars($sitio['estado']) ?>"
                                                    data-fecha="<?= htmlspecialchars($sitio['fecha']) ?>"
                                                    title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

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

                <!-- Modal Ver Detalle (mismo patrón que el módulo de Zoocriaderos) -->
                <div class="modal fade" id="sitioDetailModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detalle del sitio</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body" id="sitioDetailBody"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                        margin-left: 8px;
                        margin-right: 8px;
                    }

                    .btn-excel:hover {
                        background-color: rgba(33, 166, 102, 0.1);
                    }

                    body[data-background-color="dark"] .btn-excel {
                        background-color: transparent;
                    }

                    .tarjetas {
                        display: flex;
                        gap: 20px;
                    }

                    .tarjeta {
                        flex: 1;
                        position: relative;
                    }

                    .cabecera-tarjeta {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                    }

                    .cabecera-tarjeta p {
                        margin: 0 0 4px 0;
                        color: #666;
                        font-size: 14px;
                    }

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

                    .icono-azul {
                        background-color: #e5f0ff;
                        color: #2f7dfa;
                    }

                    .icono-verde {
                        background-color: #e3f9ec;
                        color: #21a666;
                    }

                    .icono-morado {
                        background-color: #efe9fd;
                        color: #6c5ce7;
                    }

                    .numero {
                        font-size: 24px;
                        font-weight: bold;
                    }

                    .azul {
                        color: #2f7dfa;
                    }

                    .verde {
                        color: #21a666;
                    }

                    .morado {
                        color: #6c5ce7;
                    }

                    .comparativa {
                        font-size: 12px;
                        margin: 8px 0 0 0;
                    }

                    .positivo {
                        color: #21a666;
                    }

                    .negativo {
                        color: #e64545;
                    }

                    .neutro {
                        color: #888888;
                    }

                    .sparkline {
                        position: absolute;
                        top: 4px;
                        right: 4px;
                        width: 110px;
                        height: 40px;
                    }

                    .fila-inferior {
                        display: flex;
                        gap: 20px;
                        align-items: stretch;
                    }

                    .fila-inferior .caja {
                        margin-bottom: 0;
                        flex: 1;
                    }

                    .bloque-donut {
                        display: flex;
                        align-items: center;
                        gap: 20px;
                    }

                    .donut {
                        width: 150px;
                        height: 150px;
                        border-radius: 50%;
                        position: relative;
                        flex-shrink: 0;
                    }

                    .donut-centro {
                        position: absolute;
                        top: 20px;
                        left: 20px;
                        right: 20px;
                        bottom: 20px;
                        background-color: #e9e9ee;
                        border-radius: 50%;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                    }

                    .donut-centro span {
                        font-size: 12px;
                        color: #888;
                    }

                    .donut-centro strong {
                        font-size: 20px;
                    }

                    .leyenda-donut {
                        list-style: none;
                        margin: 0;
                        padding: 0;
                        font-size: 13px;
                    }

                    .leyenda-donut li {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 10px;
                    }

                    .punto-leyenda {
                        width: 10px;
                        height: 10px;
                        border-radius: 50%;
                        flex-shrink: 0;
                    }

                    .grafica-barras {
                        display: flex;
                        align-items: flex-end;
                        justify-content: space-around;
                        height: 160px;
                        border-bottom: 2px solid #c7c7cf;
                    }

                    .grupo-barra {
                        display: flex;
                        align-items: flex-end;
                        height: 100%;
                    }

                    .barra {
                        width: 40px;
                        border-radius: 4px 4px 0 0;
                        position: relative;
                    }

                    .barra span {
                        position: absolute;
                        top: -18px;
                        left: 0;
                        right: 0;
                        text-align: center;
                        font-size: 12px;
                        font-weight: bold;
                    }

                    .etiquetas-barras {
                        display: flex;
                        justify-content: space-around;
                        margin-top: 8px;
                        font-size: 12px;
                    }

                    .nota-grafica {
                        font-size: 12px;
                        color: #777;
                        margin-top: 14px;
                        margin-bottom: 0;
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

                    .badge {
                        padding: 5px 12px;
                        border-radius: 20px;
                        color: #ffffff;
                        font-size: 13px;
                        font-weight: bold;
                    }

                    .badge-verde {
                        background-color: #2ecc71;
                    }

                    .badge-rojo {
                        background-color: #e74c3c;
                    }

                    .accion-ver {
                        cursor: pointer;
                        font-size: 16px;
                    }

                    .pie-tabla {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 14px;
                        font-size: 13px;
                        color: #555;
                    }

                    .paginacion {
                        display: flex;
                        gap: 6px;
                    }

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

                    .tabla-responsive {
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                    }

                    @media (max-width: 768px) {
                        .tarjetas {
                            flex-direction: column;
                        }

                        .fila-inferior {
                            flex-direction: column;
                        }

                        .bloque-donut {
                            flex-wrap: wrap;
                        }

                        .pie-tabla {
                            flex-direction: column;
                            align-items: flex-start;
                            gap: 10px;
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
<script>
    (function() {
        "use strict";

        var modalEl = document.getElementById("sitioDetailModal");
        var modalBody = document.getElementById("sitioDetailBody");
        if (!modalEl || !modalBody) return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.querySelectorAll('[data-action="ver-sitio"]').forEach(function(btn) {
            btn.addEventListener("click", function() {
                var estadoBadge = btn.dataset.estado === "Activo" ?
                    '<span class="badge-estado activo">Activo</span>' :
                    '<span class="badge-estado inactivo">' + btn.dataset.estado + "</span>";

                modalBody.innerHTML =
                    '<dl class="row mb-0">' +
                    '<dt class="col-5">ID Sitio</dt><dd class="col-7">' + btn.dataset.id + "</dd>" +
                    '<dt class="col-5">Nombre del sitio</dt><dd class="col-7">' + btn.dataset.nombre + "</dd>" +
                    '<dt class="col-5">Zoocriadero</dt><dd class="col-7">' + btn.dataset.zoocriadero + "</dd>" +
                    '<dt class="col-5">Tipo de depósito</dt><dd class="col-7">' + btn.dataset.tipo + "</dd>" +
                    '<dt class="col-5">Cantidad de tanques</dt><dd class="col-7">' + btn.dataset.tanques + "</dd>" +
                    '<dt class="col-5">Estado</dt><dd class="col-7">' + estadoBadge + "</dd>" +
                    '<dt class="col-5">Fecha de registro</dt><dd class="col-7">' + btn.dataset.fecha + "</dd>" +
                    "</dl>";
                modal.show();
            });
        });
    })();
</script>
</body>

</html>