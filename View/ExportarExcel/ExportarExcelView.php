<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalColorScale;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// la PRESENTACIÓN del libro de Excel (hojas, colores, bordes, anchos,
// tarjetas de indicadores, gráficos, totales, impresión). Recibe los datos
// ya listos del Modelo y devuelve un Spreadsheet, no envía HTTP.
//
// Hojas del libro:
//   Resumen : banner, tarjetas de indicadores y 3 gráficos
//   Detalle : tabla completa con formato, escalas de color y totales
//   Datos   : (oculta) tablas auxiliares de las que leen los gráficos

class ExportarExcelView
{

    // Paleta de colores
    const AZUL    = '2F7DFA';
    const TINTA   = '2A2F5B';
    const CELESTE = 'E5F0FF';
    const ZEBRA   = 'F5F8FF';
    const BORDE   = 'D7DBE3';
    const GRIS    = '6B7280';
    const VERDE   = '21A666';
    const AMBAR   = 'F5A623';
    const ROJO    = 'E5484D';

    // Colores de apoyo para gráficos cuando no hay un color "con significado".
    const PALETA = ['2F7DFA', '21A666', 'F5A623', 'E5484D', '8E5CF7', '19B5C9', '2A2F5B', 'F77FBE'];

    // Columnas (A..L) del área de tarjetas y gráficos de la hoja Resumen.
    const COLUMNAS_RESUMEN = 12;

    // Formato propio de cada reporte (no son datos, son decisiones de diseño).
    //   totales  : columnas numéricas que llevan fila de total
    //   formatos : formato de número de Excel por columna
    //   escalas  : columnas con escala de color ('semaforo' 0-100 % o 'azul' relativa)
    //   graficos : 3 gráficos: [tipo, titulo, fuente]
    //     tipo   : pie | columna | apilada | linea
    //     fuente : ['resumen' => [etiquetas de tarjetas]]
    //              ['detalle' => ['cat' => col | [cols], 'series' => [cols], 'limite' => n]]
    //              ['agrupar' => ['por' => col, 'series' => [[nombre, col|null]], 'limite' => n]]
    //              (col = suma de esa columna; null = contar filas)
    private function presentacion($reporte)
    {
        $estados = ['resumen' => ['Completas', 'En progreso', 'Retrasadas']];
        $tabla = [
            'rep-actividades-zoo' => [
                'totales' => [], 'formatos' => [], 'escalas' => [],
                'graficos' => [
                    ['pie',     'Actividades por estado',      $estados],
                    ['columna', 'Actividades por zoocriadero', ['agrupar' => ['por' => 'Zoocriadero', 'series' => [['Actividades', null]], 'limite' => 10]]],
                    ['columna', 'Actividades por responsable', ['agrupar' => ['por' => 'Responsable', 'series' => [['Actividades', null]], 'limite' => 10]]],
                ],
            ],
            'rep-peces-tanque' => [
                'totales' => ['Nacidos', 'Muertos'], 'formatos' => [], 'escalas' => [],
                'graficos' => [
                    ['pie',     'Peces nacidos vs. muertos',         ['resumen' => ['Peces nacidos', 'Peces muertos']]],
                    ['columna', 'Nacidos y muertos por tanque',      ['detalle' => ['cat' => ['Tanque', 'Zoocriadero'], 'series' => ['Nacidos', 'Muertos'], 'limite' => 12]]],
                    ['linea',   'Nacidos y muertos por zoocriadero', ['agrupar' => ['por' => 'Zoocriadero', 'series' => [['Nacidos', 'Nacidos'], ['Muertos', 'Muertos']], 'limite' => 10]]],
                ],
            ],
            'rep-tanques-zoo' => [
                'totales' => ['Cantidad'], 'formatos' => [], 'escalas' => [],
                'graficos' => [
                    ['pie',     'Tanques por estado',      ['agrupar' => ['por' => 'Estado', 'series' => [['Tanques', 'Cantidad']], 'limite' => 8]]],
                    ['columna', 'Tanques por zoocriadero', ['agrupar' => ['por' => 'Zoocriadero', 'series' => [['Tanques', 'Cantidad']], 'limite' => 10]]],
                    ['columna', 'Tanques por tipo',        ['agrupar' => ['por' => 'Tipo de tanque', 'series' => [['Tanques', 'Cantidad']], 'limite' => 10]]],
                ],
            ],
            'rep-terreno-tipo' => [
                'totales'  => ['Completadas', 'En progreso', 'Retrasadas', 'Total'],
                'formatos' => ['% del total' => '0.0"%"'],
                'escalas'  => ['% del total' => 'azul'],
                'graficos' => [
                    ['pie',     'Actividades por estado',              $estados],
                    ['apilada', 'Estado de las actividades por tipo',  ['detalle' => ['cat' => 'Tipo de actividad', 'series' => ['Completadas', 'En progreso', 'Retrasadas'], 'limite' => 12]]],
                    ['linea',   'Completadas vs. retrasadas por tipo', ['detalle' => ['cat' => 'Tipo de actividad', 'series' => ['Completadas', 'Retrasadas'], 'limite' => 12]]],
                ],
            ],
            'rep-terreno-auxiliar' => [
                'totales'  => ['Completadas', 'En progreso', 'Retrasadas', 'Total'],
                'formatos' => ['% Cumplimiento' => '0.0"%"'],
                'escalas'  => ['% Cumplimiento' => 'semaforo'],
                'graficos' => [
                    ['pie',     'Actividades por estado',                 $estados],
                    ['apilada', 'Estado de las actividades por auxiliar', ['detalle' => ['cat' => 'Auxiliar', 'series' => ['Completadas', 'En progreso', 'Retrasadas'], 'limite' => 12]]],
                    ['linea',   'Cumplimiento (%) por auxiliar',          ['detalle' => ['cat' => 'Auxiliar', 'series' => ['% Cumplimiento'], 'limite' => 15]]],
                ],
            ],
            'rep-sitios-deposito' => [
                'totales' => ['Visitas'], 'formatos' => [], 'escalas' => [],
                'graficos' => [
                    ['pie',     'Sitios por estado',           ['agrupar' => ['por' => 'Estado', 'series' => [['Sitios', null]], 'limite' => 8]]],
                    ['columna', 'Sitios por tipo de depósito', ['agrupar' => ['por' => 'Tipo de depósito', 'series' => [['Sitios', null]], 'limite' => 10]]],
                    ['columna', 'Visitas por barrio',          ['agrupar' => ['por' => 'Barrio', 'series' => [['Visitas', 'Visitas']], 'limite' => 10]]],
                ],
            ],
        ];
        return $tabla[$reporte] ?? ['totales' => [], 'formatos' => [], 'escalas' => [], 'graficos' => []];
    }

    // Color "con significado" para estados y métricas conocidas; si no, null.
    private function colorSemantico($nombre)
    {
        $n = mb_strtolower(trim((string) $nombre));
        if (in_array($n, ['activo', 'completa', 'completas', 'completada', 'completadas', 'nacidos', 'peces nacidos'], true)) {
            return self::VERDE;
        }
        if (in_array($n, ['inactivo', 'retrasada', 'retrasadas', 'muertos', 'peces muertos', 'sin depósito'], true)) {
            return self::ROJO;
        }
        if ($n === 'en progreso') {
            return self::AMBAR;
        }
        return null;
    }

    private function colorPaleta($i)
    {
        return self::PALETA[$i % count(self::PALETA)];
    }

    // Números como números; texto siempre como texto (evita que "=algo" se vuelva fórmula).
    private function poner($hoja, $celda, $valor)
    {
        if (is_int($valor) || is_float($valor)) {
            $hoja->setCellValue($celda, $valor);
        } else {
            $hoja->setCellValueExplicit($celda, (string) $valor, DataType::TYPE_STRING);
        }
    }

    // Franja de título (fondo tinta, texto blanco) sobre un rango combinado.
    private function banner($hoja, $rango, $texto, $tam, $altura)
    {
        $hoja->mergeCells($rango);
        $celda = explode(':', $rango)[0];
        $this->poner($hoja, $celda, $texto);
        $hoja->getStyle($rango)->applyFromArray([
            'font'      => ['bold' => true, 'size' => $tam, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::TINTA]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);
        $hoja->getRowDimension((int) preg_replace('/\D/', '', $celda))->setRowHeight($altura);
    }

    // Línea de texto secundaria (gris, cursiva) en un rango combinado.
    private function nota($hoja, $rango, $texto, $altura = 18)
    {
        $hoja->mergeCells($rango);
        $celda = explode(':', $rango)[0];
        $this->poner($hoja, $celda, $texto);
        $hoja->getStyle($rango)->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => self::GRIS]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true],
        ]);
        $hoja->getRowDimension((int) preg_replace('/\D/', '', $celda))->setRowHeight($altura);
    }

    private function bordes($hoja, $rango)
    {
        $hoja->getStyle($rango)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDE);
    }

    private function configurarImpresion($hoja, $filaEncabezado)
    {
        $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $hoja->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $hoja->getPageSetup()->setFitToWidth(1);
        $hoja->getPageSetup()->setFitToHeight(0);
        $hoja->getSheetView()->setZoomScale(100);
        $hoja->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($filaEncabezado, $filaEncabezado);
        $hoja->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.6)->setBottom(0.6);
        $hoja->getHeaderFooter()->setOddFooter('&L&8SIGuppys - Control Biológico contra el Dengue&R&8Página &P de &N');
    }

    // Recibe lo que devuelve ExportarExcelModel::obtenerReporte() y arma el libro.
    public function construir($reporte)
    {
        $def      = $reporte['definicion'];
        $datos    = $reporte['datos'];
        $filas    = $reporte['filas'];
        $filtros  = $reporte['filtros'];
        $formato  = $this->presentacion($reporte['reporte']);
        $generado = 'Generado el ' . date('d/m/Y') . ' a las ' . date('H:i');

        $libro = new Spreadsheet();
        $libro->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $libro->getProperties()
            ->setCreator('SIGuppys')
            ->setTitle($def['titulo'])
            ->setSubject('Reporte exportado desde SIGuppys');

        // Detalle primero para que "Datos" (creada dentro de hojaResumen) quede al final.
        $this->hojaDetalle($libro, $def, $filas, $filtros, $generado, $formato);
        $this->hojaResumen($libro, $def, $datos, $filas, $filtros, $generado, $formato);

        $libro->setActiveSheetIndex(0);
        return $libro;
    }

    // RESUMEN (banner + tarjetas + gráficos)
    // =====================================================
    private function hojaResumen($libro, $def, $datos, $filas, $filtros, $generado, $formato)
    {
        $resumen = $libro->getSheet(0);
        $resumen->setTitle('Resumen');
        $resumen->setShowGridlines(false);
        $resumen->getTabColor()->setRGB(self::AZUL);

        $ultimaColumna = Coordinate::stringFromColumnIndex(self::COLUMNAS_RESUMEN);
        for ($c = 1; $c <= self::COLUMNAS_RESUMEN; $c++) {
            $resumen->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(11.5);
        }

        $this->banner($resumen, "A1:{$ultimaColumna}1", $def['titulo'], 18, 40);
        $this->nota($resumen, "A2:{$ultimaColumna}2", 'SIGuppys · Control Biológico contra el Dengue', 20);
        $this->nota($resumen, "A3:{$ultimaColumna}3", $generado, 18);
        $this->nota($resumen, "A4:{$ultimaColumna}4", 'Filtros aplicados: ' . $filtros, 30);

        $this->tarjetas($resumen, $def, $datos);

        // Título de sección
        $resumen->mergeCells("A9:{$ultimaColumna}9");
        $resumen->setCellValue('A9', 'Análisis gráfico');
        $resumen->getStyle("A9:{$ultimaColumna}9")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::TINTA]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::AZUL]]],
        ]);
        $resumen->getRowDimension(9)->setRowHeight(26);

        // Hoja oculta con las tablas de las que leen los gráficos
        $auxiliar = $libro->createSheet();
        $auxiliar->setTitle('Datos');

        // Solo se dibujan los gráficos que tienen algo que mostrar (no vacíos).
        $utiles = [];
        foreach ($formato['graficos'] as $g) {
            $reunido = $this->datosGrafico($g[2], $def, $datos, $filas);
            if ($this->tieneDatos($reunido)) {
                $utiles[] = [$g, $reunido];
            }
        }

        // Distribución según cuántos quedan: 3 = dos arriba y uno ancho; 2 = lado a lado; 1 = ancho.
        // (la esquina inferior derecha es la esquina superior izquierda de la celda indicada)
        $distribucion = [
            1 => [['A11', 'M31']],
            2 => [['A11', 'G27'], ['G11', 'M27']],
            3 => [['A11', 'G27'], ['G11', 'M27'], ['A28', 'M47']],
        ];
        $filaAuxiliar = 1;
        foreach ($utiles as $i => $u) {
            $this->grafico($resumen, $auxiliar, $filaAuxiliar, $i, $u[0], $u[1], $distribucion[count($utiles)][$i]);
        }
        $auxiliar->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $resumen->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $resumen->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $resumen->getPageSetup()->setFitToWidth(1);
        $resumen->getPageSetup()->setFitToHeight(0);
    }

    // Una tarjeta por indicador (etiqueta arriba, valor grande abajo).
    private function tarjetas($resumen, $def, $datos)
    {
        $cantidad = count($def['resumen']);
        if ($cantidad === 0) {
            return;
        }
        $ancho = max(1, intdiv(self::COLUMNAS_RESUMEN, $cantidad));
        $col   = 1;
        $n     = 0;
        foreach ($def['resumen'] as $etiqueta => $indice) {
            $c1 = Coordinate::stringFromColumnIndex($col);
            $c2 = Coordinate::stringFromColumnIndex($col + $ancho - 1);
            $color = $this->colorSemantico($etiqueta) ?? ($n % 2 === 0 ? self::AZUL : self::TINTA);

            $valor = $datos[$indice] ?? '';
            if (is_float($valor)) {
                $valor = round($valor, 2);
            }

            $resumen->mergeCells("{$c1}6:{$c2}6");
            $resumen->mergeCells("{$c1}7:{$c2}7");
            $this->poner($resumen, "{$c1}6", mb_strtoupper($etiqueta));
            $this->poner($resumen, "{$c1}7", $valor);

            $resumen->getStyle("{$c1}6:{$c2}7")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'font'      => ['color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $resumen->getStyle("{$c1}6")->getFont()->setBold(true)->setSize(9);
            $resumen->getStyle("{$c1}7")->getFont()->setBold(true)->setSize(26);

            $col += $ancho;
            $n++;
        }
        $resumen->getRowDimension(6)->setRowHeight(28);
        $resumen->getRowDimension(7)->setRowHeight(50);
    }

    // Etiqueta de una categoría; si son varias columnas las une ("Tanque 1 · Aguablanca")
    // y quita el prefijo "Zoocriadero " para que quepa en el eje.
    private function etiquetaCategoria($fila, $def, $cat)
    {
        $partes = [];
        foreach ((array) $cat as $columna) {
            $valor = trim((string) ($fila[$def['columnas'][$columna]] ?? ''));
            $partes[] = preg_replace('/^Zoocriadero\s+/u', '', $valor);
        }
        return implode(' · ', $partes);
    }

    // Reúne categorías y series de un gráfico según su fuente.
    //   devuelve [categorías, [nombre de serie => valores]] o null si no hay datos
    private function datosGrafico($fuente, $def, $datos, $filas)
    {
        if (isset($fuente['resumen'])) {
            $cats = [];
            $vals = [];
            foreach ($fuente['resumen'] as $etiqueta) {
                $cats[] = $etiqueta;
                $vals[] = (float) ($datos[$def['resumen'][$etiqueta]] ?? 0);
            }
            return [$cats, ['Cantidad' => $vals]];
        }

        if (isset($fuente['detalle'])) {
            $f = $fuente['detalle'];
            $filas = array_slice($filas, 0, $f['limite'] ?? 12);
            $cats = [];
            $series = [];
            foreach ($f['series'] as $nombre) {
                $series[$nombre] = [];
            }
            foreach ($filas as $fila) {
                $cats[] = $this->etiquetaCategoria($fila, $def, $f['cat']);
                foreach ($f['series'] as $nombre) {
                    $series[$nombre][] = (float) ($fila[$def['columnas'][$nombre]] ?? 0);
                }
            }
            return [$cats, $series];
        }

        if (isset($fuente['agrupar'])) {
            $f = $fuente['agrupar'];
            $campoPor = $def['columnas'][$f['por']];
            $grupos = [];
            foreach ($filas as $fila) {
                $clave = trim((string) ($fila[$campoPor] ?? ''));
                if ($clave === '') {
                    $clave = '(sin dato)';
                }
                if (!isset($grupos[$clave])) {
                    $grupos[$clave] = array_fill(0, count($f['series']), 0.0);
                }
                foreach ($f['series'] as $j => $serie) {
                    $grupos[$clave][$j] += $serie[1] === null ? 1 : (float) ($fila[$def['columnas'][$serie[1]]] ?? 0);
                }
            }
            uasort($grupos, function ($a, $b) {
                return $b[0] <=> $a[0];
            });
            $grupos = array_slice($grupos, 0, $f['limite'] ?? 10, true);

            $cats = array_map('strval', array_keys($grupos));
            $series = [];
            foreach ($f['series'] as $j => $serie) {
                $series[$serie[0]] = array_column($grupos, $j);
            }
            return [$cats, $series];
        }

        return null;
    }

    // Un gráfico solo vale la pena si hay categorías y algún valor distinto de cero.
    private function tieneDatos($reunido)
    {
        if ($reunido === null || count($reunido[0]) === 0) {
            return false;
        }
        foreach ($reunido[1] as $valores) {
            if (array_sum($valores) != 0) {
                return true;
            }
        }
        return false;
    }

    // Escribe la tabla auxiliar en la hoja "Datos" y crea el gráfico en Resumen.
    private function grafico($resumen, $auxiliar, &$fila, $indice, $g, $reunido, $posicion)
    {
        list($tipo, $titulo) = $g;
        list($cats, $series) = $reunido;

        // --- Tabla auxiliar: categoría | serie 1 | serie 2 ...
        $filaTitulo = $fila;
        $filaEnc    = $fila + 1;
        $primera    = $fila + 2;
        $ultima     = $primera + count($cats) - 1;

        $auxiliar->setCellValueExplicit('A' . $filaTitulo, (string) $titulo, DataType::TYPE_STRING);
        $auxiliar->setCellValueExplicit('A' . $filaEnc, 'Categoría', DataType::TYPE_STRING);
        $nombres = array_keys($series);
        foreach ($nombres as $j => $nombre) {
            $auxiliar->setCellValueExplicit(Coordinate::stringFromColumnIndex($j + 2) . $filaEnc, (string) $nombre, DataType::TYPE_STRING);
        }
        foreach ($cats as $k => $cat) {
            $auxiliar->setCellValueExplicit('A' . ($primera + $k), (string) $cat, DataType::TYPE_STRING);
            foreach ($nombres as $j => $nombre) {
                $auxiliar->setCellValue(Coordinate::stringFromColumnIndex($j + 2) . ($primera + $k), $series[$nombre][$k]);
            }
        }
        $fila = $ultima + 3;

        // --- Series del gráfico
        $total     = count($cats);
        $refCats   = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Datos'!\$A\${$primera}:\$A\${$ultima}", null, $total, $cats);
        $etiquetas = [];
        $valores   = [];
        foreach ($nombres as $j => $nombre) {
            $letra = Coordinate::stringFromColumnIndex($j + 2);
            $etiquetas[] = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Datos'!\${$letra}\${$filaEnc}", null, 1, [$nombre]);
            $v = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Datos'!\${$letra}\${$primera}:\${$letra}\${$ultima}", null, $total, $series[$nombre]);

            if ($tipo === 'pie') {
                $colores = [];
                foreach ($cats as $k => $cat) {
                    $colores[] = $this->colorSemantico($cat) ?? $this->colorPaleta($k);
                }
                $v->setFillColor($colores);
            } else {
                $color = $this->colorSemantico($nombre) ?? $this->colorPaleta($j);
                $v->setFillColor($color);
                if ($tipo === 'linea') {
                    $v->setLineWidth(2.5);
                    $v->setPointMarker('circle');
                    $v->setPointSize(7);
                    $v->getMarkerFillColor()->setColorProperties($color, null, 'srgbClr');
                    $v->getMarkerBorderColor()->setColorProperties($color, null, 'srgbClr');
                    $v->getLineColor()->setColorProperties($color, null, 'srgbClr');
                }
            }
            $valores[] = $v;
        }

        // --- Tipo de gráfico
        $orden  = range(0, count($valores) - 1);
        $layout = new Layout();
        if ($tipo === 'pie') {
            $serie = new DataSeries(DataSeries::TYPE_PIECHART, null, $orden, $etiquetas, [$refCats], $valores);
            $layout->setShowPercent(true)->setShowVal(false)->setShowCatName(false);
        } elseif ($tipo === 'linea') {
            $serie = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, $orden, $etiquetas, [$refCats], $valores);
            $layout->setShowVal(count($valores) === 1);
        } else {
            $agrupacion = $tipo === 'apilada' ? DataSeries::GROUPING_STACKED : DataSeries::GROUPING_CLUSTERED;
            $serie = new DataSeries(DataSeries::TYPE_BARCHART, $agrupacion, $orden, $etiquetas, [$refCats], $valores);
            $serie->setPlotDirection(DataSeries::DIRECTION_COL);
            $layout->setShowVal($tipo !== 'apilada');
        }

        $area    = new PlotArea($layout, [$serie]);
        $leyenda = ($tipo === 'pie' || count($valores) > 1) ? new Legend(Legend::POSITION_BOTTOM, null, false) : null;

        $grafico = new Chart('grafico' . $indice, new Title($titulo), $leyenda, $area, true, DataSeries::EMPTY_AS_GAP);
        $grafico->setTopLeftPosition($posicion[0], 4, 4);
        $grafico->setBottomRightPosition($posicion[1], -4, -4);
        $resumen->addChart($grafico);
    }

    // Estilo (fuente, fondo) de un estado conocido; null si no es un estado.
    private function estiloEstado($valor)
    {
        switch ($valor) {
            case 'Activo':
            case 'Completada':
                return ['1B7F4C', 'E3F9EC'];
            case 'Inactivo':
            case 'Retrasada':
                return ['C0392B', 'FDE6E6'];
            case 'En progreso':
                return ['9A6700', 'FFF4D6'];
        }
        return null;
    }

    // Escala de color de 3 puntos para una columna de porcentajes.
    private function escalaColor($hoja, $rango, $tipo)
    {
        $escala = new ConditionalColorScale();
        if ($tipo === 'semaforo') {
            $escala->setMinimumConditionalFormatValueObject(new ConditionalFormatValueObject('num', 0))
                ->setMidpointConditionalFormatValueObject(new ConditionalFormatValueObject('num', 50))
                ->setMaximumConditionalFormatValueObject(new ConditionalFormatValueObject('num', 100))
                ->setMinimumColor(new Color('FFF8696B'))
                ->setMidpointColor(new Color('FFFFEB84'))
                ->setMaximumColor(new Color('FF63BE7B'));
        } else {
            $escala->setMinimumConditionalFormatValueObject(new ConditionalFormatValueObject('min'))
                ->setMidpointConditionalFormatValueObject(new ConditionalFormatValueObject('percentile', 50))
                ->setMaximumConditionalFormatValueObject(new ConditionalFormatValueObject('max'))
                ->setMinimumColor(new Color('FFFFFFFF'))
                ->setMidpointColor(new Color('FFD6E6FF'))
                ->setMaximumColor(new Color('FF7FAEFA'));
        }
        $condicion = new Conditional();
        $condicion->setConditionType(Conditional::CONDITION_COLORSCALE);
        $condicion->setColorScale($escala);
        $hoja->getStyle($rango)->setConditionalStyles([$condicion]);
    }

    // DETALLES
    // =====================================================
    private function hojaDetalle($libro, $def, $filas, $filtros, $generado, $formato)
    {
        $detalle = $libro->createSheet();
        $detalle->setTitle('Detalle');
        $detalle->setShowGridlines(false);
        $detalle->getTabColor()->setRGB(self::VERDE);

        $columnas = array_keys($def['columnas']);
        $campos = array_values($def['columnas']);
        $cantidadColumnas = count($columnas);
        $ultimaLetra = Coordinate::stringFromColumnIndex($cantidadColumnas);
        $filaEncabezado = 5;
        $primeraFila = $filaEncabezado + 1;

        $this->banner($detalle, "A1:{$ultimaLetra}1", $def['titulo'] . ' — Detalle', 16, 36);
        $this->nota($detalle, "A2:{$ultimaLetra}2", $generado . '   ·   ' . count($filas) . ' registro(s)', 18);
        $this->nota($detalle, "A3:{$ultimaLetra}3", 'Filtros aplicados: ' . $filtros, 30);

        // Encabezados
        foreach ($columnas as $i => $encabezado) {
            $this->poner($detalle, Coordinate::stringFromColumnIndex($i + 1) . $filaEncabezado, $encabezado);
        }
        $detalle->getStyle("A{$filaEncabezado}:{$ultimaLetra}{$filaEncabezado}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $detalle->getRowDimension($filaEncabezado)->setRowHeight(28);

        // Ancho de cada columna según su contenido (mín. 12, máx. 45).
        $anchos = [];
        foreach ($columnas as $i => $encabezado) {
            $anchos[$i] = mb_strlen($encabezado);
        }

        // Filas de datos
        $fila = $primeraFila;
        foreach ($filas as $registro) {
            foreach ($campos as $i => $campo) {
                $valor = $registro[$campo] ?? '';
                $letra = Coordinate::stringFromColumnIndex($i + 1);
                $this->poner($detalle, $letra . $fila, $valor);
                $anchos[$i] = max($anchos[$i], mb_strlen((string) $valor));

                $codigoFormato = $formato['formatos'][$columnas[$i]] ?? null;
                if ($codigoFormato) {
                    $detalle->getStyle($letra . $fila)->getNumberFormat()->setFormatCode($codigoFormato);
                }
                if (is_int($valor) || is_float($valor)) {
                    $detalle->getStyle($letra . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Métricas con significado (completadas en verde, retrasadas en rojo...)
                    $colorNumero = $this->colorSemantico($columnas[$i]);
                    if ($colorNumero !== null && $valor > 0) {
                        $detalle->getStyle($letra . $fila)->getFont()->setBold(true)->getColor()->setRGB($colorNumero);
                    }
                }
                if ($columnas[$i] === 'Estado') {
                    $detalle->getStyle($letra . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $estilo = $this->estiloEstado($valor);
                    if ($estilo !== null) {
                        $detalle->getStyle($letra . $fila)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $estilo[0]]],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $estilo[1]]],
                        ]);
                    }
                }
            }
            // Filas alternadas (las de Estado coloreado conservan su color)
            if (($fila - $primeraFila) % 2 === 1) {
                for ($i = 0; $i < $cantidadColumnas; $i++) {
                    $celda = $detalle->getStyle(Coordinate::stringFromColumnIndex($i + 1) . $fila);
                    if ($celda->getFill()->getFillType() === Fill::FILL_NONE) {
                        $celda->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::ZEBRA);
                    }
                }
            }
            $detalle->getRowDimension($fila)->setRowHeight(20);
            $fila++;
        }
        $ultimaDato = $fila - 1;

        if (count($filas) === 0) {
            $detalle->mergeCells("A{$primeraFila}:{$ultimaLetra}{$primeraFila}");
            $detalle->setCellValue("A{$primeraFila}", 'No hay registros con los filtros aplicados.');
            $detalle->getStyle("A{$primeraFila}")->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['rgb' => self::GRIS]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $detalle->getRowDimension($primeraFila)->setRowHeight(28);
            $ultimaDato = $primeraFila;
        }

        $detalle->getStyle("A{$primeraFila}:{$ultimaLetra}{$ultimaDato}")
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Escalas de color en columnas de porcentaje
        if (count($filas) > 0) {
            foreach ($formato['escalas'] as $encabezado => $tipoEscala) {
                $i = array_search($encabezado, $columnas, true);
                if ($i !== false) {
                    $letra = Coordinate::stringFromColumnIndex($i + 1);
                    $this->escalaColor($detalle, "{$letra}{$primeraFila}:{$letra}{$ultimaDato}", $tipoEscala);
                }
            }
        }

        // Fila de totales (SUM de Excel, se recalcula si el usuario filtra)
        $ultimaTabla = $ultimaDato;
        if ($formato['totales'] && count($filas) > 0) {
            $filaTotal = $ultimaDato + 1;
            $this->poner($detalle, "A{$filaTotal}", 'TOTAL');
            foreach ($formato['totales'] as $encabezado) {
                $i = array_search($encabezado, $columnas, true);
                if ($i === false) {
                    continue;
                }
                $letra = Coordinate::stringFromColumnIndex($i + 1);
                $detalle->setCellValue($letra . $filaTotal, "=SUM({$letra}{$primeraFila}:{$letra}{$ultimaDato})");
            }
            $detalle->getStyle("A{$filaTotal}:{$ultimaLetra}{$filaTotal}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::TINTA]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $detalle->getStyle("A{$filaTotal}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
            $detalle->getRowDimension($filaTotal)->setRowHeight(26);
            $ultimaTabla = $filaTotal;
        }

        $this->bordes($detalle, "A{$filaEncabezado}:{$ultimaLetra}{$ultimaTabla}");

        // Anchos y filtros de Excel
        foreach ($anchos as $i => $largo) {
            $detalle->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))
                ->setWidth(min(45, max(12, $largo + 4)));
        }
        if (count($filas) > 0) {
            $detalle->setAutoFilter("A{$filaEncabezado}:{$ultimaLetra}{$ultimaDato}");
        }
        $detalle->freezePane('A' . $primeraFila);
        $this->configurarImpresion($detalle, $filaEncabezado);
    }
}
