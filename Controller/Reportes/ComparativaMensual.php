<?php

// ============================================================
// Comparativa "vs mes anterior" de las tarjetas de resumen de los reportes.
//
// Solo depende del filtro "Fecha Inicio": si esa fecha cae en octubre, el
// periodo anterior es todo septiembre. Si no hay Fecha Inicio no hay contra
// qué comparar y la tarjeta lo dice. Los demás filtros (auxiliar, zoocriadero,
// etc.) los aplica cada controlador al calcular el mes anterior.
// ============================================================

const MESES_COMPARATIVA = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
    7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
];

// A partir de la Fecha Inicio (Y-m-d) devuelve el mes anterior a ese mes:
//   ['clave' => 'AAAA-MM', 'nombre' => 'septiembre']  o null si no hay fecha válida.
function obtenerMesAnterior($filtroFechaInicio)
{
    if ($filtroFechaInicio === '') {
        return null;
    }
    $inicio = DateTime::createFromFormat('Y-m-d', $filtroFechaInicio);
    if (!$inicio) {
        return null;
    }
    $anterior = new DateTime($inicio->format('Y-m-01'));
    $anterior->modify('-1 month');

    return [
        'clave'  => $anterior->format('Y-m'),
        'nombre' => MESES_COMPARATIVA[(int) $anterior->format('n')],
    ];
}

// ¿La fecha (dd/mm/aaaa, como la traen las consultas) cae dentro del mes anterior?
function fechaEnMesAnterior($fechaTexto, $mesAnterior)
{
    $fecha = DateTime::createFromFormat('d/m/Y', $fechaTexto);
    return $fecha && $mesAnterior && $fecha->format('Y-m') === $mesAnterior['clave'];
}

// Texto y color de una tarjeta de conteo.
//   $subirEsBueno: true = subir es bueno (verde), false = subir es malo (rojo),
//                  null = sin juicio de valor (gris).
function armarComparativa($actual, $anterior, $mesAnterior, $subirEsBueno = true)
{
    if ($mesAnterior === null) {
        return ['texto' => 'Elige una Fecha Inicio para comparar con el mes anterior', 'clase' => 'neutro'];
    }

    $mes = $mesAnterior['nombre'];

    if ($anterior == 0) {
        $texto = $actual == 0
            ? "Sin registros en $mes ni ahora"
            : "Sin registros en $mes para comparar";
        return ['texto' => $texto, 'clase' => 'neutro'];
    }

    $cambio = (($actual - $anterior) / $anterior) * 100;
    if (round($cambio, 1) == 0) {
        return ['texto' => "Sin cambios vs $mes ($anterior)", 'clase' => 'neutro'];
    }

    return [
        'texto' => sprintf('%+.1f%% vs %s (%s)', $cambio, $mes, $anterior),
        'clase' => claseComparativa($cambio, $subirEsBueno),
    ];
}

// Igual que armarComparativa pero para valores que ya son un porcentaje
// (ej. tasa de mortalidad): la diferencia se muestra en puntos porcentuales.
function armarComparativaPuntos($actual, $anterior, $mesAnterior, $subirEsBueno = true, $hayDatosAnteriores = true)
{
    if ($mesAnterior === null) {
        return ['texto' => 'Elige una Fecha Inicio para comparar con el mes anterior', 'clase' => 'neutro'];
    }

    $mes = $mesAnterior['nombre'];

    if (!$hayDatosAnteriores) {
        return ['texto' => "Sin registros en $mes para comparar", 'clase' => 'neutro'];
    }

    $diferencia = $actual - $anterior;
    if (round($diferencia, 2) == 0) {
        return ['texto' => sprintf('Sin cambios vs %s (%.2f%%)', $mes, $anterior), 'clase' => 'neutro'];
    }

    return [
        'texto' => sprintf('%+.2f pts vs %s (%.2f%%)', $diferencia, $mes, $anterior),
        'clase' => claseComparativa($diferencia, $subirEsBueno),
    ];
}

function claseComparativa($cambio, $subirEsBueno)
{
    if ($subirEsBueno === null) {
        return 'neutro';
    }
    return ($cambio > 0) === $subirEsBueno ? 'positivo' : 'negativo';
}
