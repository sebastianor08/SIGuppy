<?php

// ============================================================
// Comparativa "vs el mes anterior" de las tarjetas de resumen de los reportes.
//
// Siempre compara un mes completo contra el mes completo anterior, así que
// la tarjeta nunca queda vacía ni pide nada:
//   - Si el filtro "Fecha Inicio" trae una fecha, se usa el mes de esa fecha
//     (el día exacto no importa, solo el mes. Ej: cualquier día de octubre
//     se compara contra septiembre completo).
//   - Si no hay Fecha Inicio (recién se entra al reporte), se usa el mes
//     actual (hoy) contra el mes anterior.
// ============================================================

// Devuelve ['actual' => 'AAAA-MM', 'anterior' => 'AAAA-MM'].
function mesesComparativa($filtroFechaInicio)
{
    $referencia = $filtroFechaInicio !== ''
        ? DateTime::createFromFormat('Y-m-d', $filtroFechaInicio)
        : null;
    if (!$referencia) {
        $referencia = new DateTime(); // sin filtro -> hoy
    }

    $actual = new DateTime($referencia->format('Y-m-01'));
    $anterior = (clone $actual)->modify('-1 month');

    return ['actual' => $actual->format('Y-m'), 'anterior' => $anterior->format('Y-m')];
}

// ¿La fecha (dd/mm/aaaa, como la traen las consultas) cae dentro de ese mes ("AAAA-MM")?
function fechaEnMes($fechaTexto, $claveMes)
{
    $fecha = DateTime::createFromFormat('d/m/Y', $fechaTexto);
    return $fecha && $fecha->format('Y-m') === $claveMes;
}

// Texto y color de una tarjeta, a partir de su valor en el mes actual y en el mes anterior.
// Siempre devuelve un porcentaje (nunca un mensaje en palabras):
//   sin datos en el mes anterior -> 0% si tampoco hay ahora, 100% si ahora sí hay.
// Color: positivo = verde, negativo = rojo, cero = gris.
//   $subirEsBueno: true (normal) = subir es verde y bajar es rojo.
//                  false (ej. Retrasadas, Muertos) = al revés: subir es rojo y bajar es verde.
function armarComparativa($actual, $anterior, $subirEsBueno = true)
{
    if ($anterior == 0) {
        $cambio = $actual == 0 ? 0.0 : 100.0;
    } else {
        $cambio = (($actual - $anterior) / $anterior) * 100;
    }

    if (round($cambio, 1) === 0.0) {
        // Sin el signo "+": en 0 no hay ni subida ni bajada que marcar.
        return ['texto' => '0.0% vs el mes anterior', 'clase' => 'neutro'];
    }

    $clase = ($cambio > 0) === $subirEsBueno ? 'positivo' : 'negativo';

    return ['texto' => sprintf('%+.1f%% vs el mes anterior', $cambio), 'clase' => $clase];
}
