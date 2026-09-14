<?php
require_once __DIR__ . '/../../Controller/Reportes/TanquesPorZoocriaderoController.php';

$datos = obtenerDatosTanquesPorZoocriadero();
extract($datos);
?>

<div class="caja">
    <h2 class="titulo-pagina">Reporte de Tanques por Zoocriadero</h2>
    <h3>Filtros</h3>
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
                <label>Tanque</label>
                <select name="tanque">
                    <option value="">Todos</option>
                    <?php foreach ($listaTipos as $tipo): ?>
                        <option value="<?= $tipo ?>" <?= $filtroTipo === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                    <?php endforeach; ?>
                </select>
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
            <div class="icono icono-azul">🏠</div>
            <p>Zoocriaderos</p>
        </div>
        <span class="numero azul"><?= $totalZoocriaderos ?></span>
        <p class="descripcion">Total de Zoocriaderos registrados</p>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-azul">🛢️</div>
            <p>Tanques totales</p>
        </div>
        <span class="numero azul"><?= $totalTanques ?></span>
        <p class="descripcion">Todos los tanques registrados</p>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-azul">💧</div>
            <p>Capacidad instalada total</p>
        </div>
        <span class="numero azul"><?= number_format($capacidadTotal) ?>L</span>
        <p class="descripcion">Litros de capacidad instalada</p>
    </div>
    <div class="tarjeta">
        <div class="cabecera-tarjeta">
            <div class="icono icono-morado">🌊</div>
            <p>Tanques totales</p>
        </div>
        <span class="numero morado"><?= $totalTanquesActivos ?></span>
        <p class="descripcion">Tanques en estado activo</p>
    </div>
</div>

<div class="caja caja-gris">
    <h3>Tanques por Zoocriaderos</h3>
    <div class="contenido-tabla-grafica">
        <table>
            <thead>
                <tr>
                    <th>Zoocriadero</th>
                    <th>Cantidad</th>
                    <th>Tipo de tanques</th>
                    <th>Capacidad total (L)</th>
                    <th>Encargado</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($registrosFiltrados) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#888;">No hay registros con esos filtros</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($registrosFiltrados as $registro): ?>
                    <tr>
                        <td><?= $registro['zoocriadero'] ?></td>
                        <td><?= $registro['cantidad'] ?></td>
                        <td><?= $registro['tipo'] ?></td>
                        <td><?= number_format($registro['capacidad']) ?></td>
                        <td><?= $registro['encargado'] ?></td>
                        <td>
                            <span class="badge <?= $registro['estado'] === 'Activo' ? 'badge-verde' : 'badge-rojo' ?>">
                                <?= $registro['estado'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fila-total">
                    <td>Total</td>
                    <td><?= $totalTanques ?></td>
                    <td>-</td>
                    <td><?= number_format($capacidadTotal) ?>L</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>

        <div class="bloque-donut">
            <?php
            // Vamos armando el texto que necesita el conic-gradient.
            // Cada color necesita en qué % EMPIEZA y en qué % TERMINA.
            $gradiente = [];
            $acumulado = 0;
            foreach ($segmentosDonut as $segmento) {
                $inicio = $acumulado;
                $acumulado += $segmento['porcentaje'];
                $gradiente[] = "{$segmento['color']} {$inicio}% {$acumulado}%";
            }
            $cssGradiente = implode(', ', $gradiente);
            ?>
            <div class="donut" style="background: conic-gradient(<?= $cssGradiente ?>);">
                <div class="donut-centro">
                    <span>Total</span>
                    <strong><?= $totalTanques ?></strong>
                </div>
            </div>
            <ul class="leyenda-donut">
                <?php foreach ($segmentosDonut as $segmento): ?>
                    <li>
                        <span class="punto-leyenda" style="background-color: <?= $segmento['color'] ?>;"></span>
                        <?= $segmento['tipo'] ?> — <?= $segmento['cantidad'] ?> (<?= $segmento['porcentaje'] ?>%)
                    </li>
                <?php endforeach; ?>
            </ul>
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

    .fila-filtros select {
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

    .tarjetas {
        display: flex;
        gap: 20px;
    }

    .tarjeta {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .cabecera-tarjeta {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cabecera-tarjeta p {
        margin: 0;
        color: #444;
        font-weight: bold;
        font-size: 14px;
    }

    .icono {
        width: 40px;
        height: 40px;
        border-radius: 10px;
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

    .icono-morado {
        background-color: #efe9fd;
        color: #6c5ce7;
    }

    .numero {
        font-size: 26px;
        font-weight: bold;
    }

    .azul {
        color: #2f7dfa;
    }

    .morado {
        color: #6c5ce7;
    }

    .descripcion {
        color: #999;
        font-size: 12px;
        margin: 0;
    }

    .contenido-tabla-grafica {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .contenido-tabla-grafica table {
        flex: 3;
        min-width: 320px;
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

    /* --- Gráfica de dona --- */
    .bloque-donut {
        flex: 2;
        display: flex;
        align-items: center;
        gap: 20px;
        min-width: 260px;
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
        background-color: #ffffff;
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
        font-size: 22px;
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
</style>