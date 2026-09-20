<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

// ============================================================
// Vista del módulo Exportar Excel.
//
// Responsabilidad: la PRESENTACIÓN del libro de Excel (hojas, colores,
// bordes, anchos, totales, impresión). Recibe los datos ya listos del
// Modelo y devuelve un Spreadsheet; no consulta la BD ni envía HTTP.
// ============================================================
class ExportarExcelView {

    // Paleta de SIGuppys
    const AZUL    = '2F7DFA';
    const TINTA   = '2A2F5B';
    const CELESTE = 'E5F0FF';
    const ZEBRA   = 'F5F8FF';
    const BORDE   = 'D7DBE3';
    const GRIS    = '6B7280';

    // Formato propio de cada reporte (no son datos, son decisiones de diseño).
    //   totales  : columnas numéricas que llevan fila de total
    //   formatos : formato de número de Excel por columna
    private function presentacion($reporte) {
        $tabla = [
            'rep-peces-tanque'     => ['totales' => ['Nacidos', 'Muertos'], 'formatos' => []],
            'rep-tanques-zoo'      => ['totales' => ['Cantidad'], 'formatos' => []],
            'rep-terreno-tipo'     => [
                'totales'  => ['Completadas', 'En progreso', 'Retrasadas', 'Total'],
                'formatos' => ['% del total' => '0.0"%"'],
            ],
            'rep-terreno-auxiliar' => [
                'totales'  => ['Completadas', 'En progreso', 'Retrasadas', 'Total'],
                'formatos' => ['% Cumplimiento' => '0.0"%"'],
            ],
            'rep-sitios-deposito'  => ['totales' => ['Visitas'], 'formatos' => []],
        ];
        return $tabla[$reporte] ?? ['totales' => [], 'formatos' => []];
    }

    // Números como números; texto siempre como texto (evita que "=algo" se vuelva fórmula).
    private function poner($hoja, $celda, $valor) {
        if (is_int($valor) || is_float($valor)) {
            $hoja->setCellValue($celda, $valor);
        } else {
            $hoja->setCellValueExplicit($celda, (string) $valor, DataType::TYPE_STRING);
        }
    }

    // Franja de título (fondo tinta, texto blanco) sobre un rango combinado.
    private function banner($hoja, $rango, $texto, $tam, $altura) {
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
    private function nota($hoja, $rango, $texto, $altura = 18) {
        $hoja->mergeCells($rango);
        $celda = explode(':', $rango)[0];
        $this->poner($hoja, $celda, $texto);
        $hoja->getStyle($rango)->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => self::GRIS]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1, 'wrapText' => true],
        ]);
        $hoja->getRowDimension((int) preg_replace('/\D/', '', $celda))->setRowHeight($altura);
    }

    private function bordes($hoja, $rango) {
        $hoja->getStyle($rango)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDE);
    }

    private function configurarImpresion($hoja, $filaEncabezado) {
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
    public function construir($reporte) {
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

        $this->hojaResumen($libro, $def, $datos, $filtros, $generado);
        $this->hojaDetalle($libro, $def, $filas, $filtros, $generado, $formato);

        $libro->setActiveSheetIndex(0);
        return $libro;
    }

    // =====================================================
    // Hoja 1: Resumen
    // =====================================================
    private function hojaResumen($libro, $def, $datos, $filtros, $generado) {
        $resumen = $libro->getActiveSheet();
        $resumen->setTitle('Resumen');
        $resumen->setShowGridlines(false);
        $resumen->getTabColor()->setRGB(self::AZUL);
        $resumen->getColumnDimension('A')->setWidth(38);
        $resumen->getColumnDimension('B')->setWidth(18);

        $this->banner($resumen, 'A1:B1', $def['titulo'], 16, 36);
        $this->nota($resumen, 'A2:B2', 'SIGuppys · Control Biológico contra el Dengue', 20);
        $this->nota($resumen, 'A3:B3', $generado, 18);
        $this->nota($resumen, 'A4:B4', 'Filtros aplicados: ' . $filtros, 34);

        // Encabezado de la tabla de indicadores
        $resumen->setCellValue('A6', 'Indicador');
        $resumen->setCellValue('B6', 'Valor');
        $resumen->getStyle('A6:B6')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $resumen->getStyle('B6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $resumen->getRowDimension(6)->setRowHeight(22);

        $n = 7;
        foreach ($def['resumen'] as $etiqueta => $indice) {
            $valor = $datos[$indice] ?? '';
            if (is_float($valor)) {
                $valor = round($valor, 2);
            }
            $this->poner($resumen, 'A' . $n, $etiqueta);
            $this->poner($resumen, 'B' . $n, $valor);
            $resumen->getRowDimension($n)->setRowHeight(22);
            $n++;
        }
        $ultimaResumen = $n - 1;
        if ($ultimaResumen >= 7) {
            $resumen->getStyle('A7:A' . $ultimaResumen)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => self::TINTA]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::CELESTE]],
            ]);
            $resumen->getStyle('B7:B' . $ultimaResumen)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => self::AZUL]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
            $resumen->getStyle('A7:B' . $ultimaResumen)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $this->bordes($resumen, 'A6:B' . $ultimaResumen);
        }
        $resumen->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $resumen->getPageSetup()->setFitToWidth(1);
        $resumen->getPageSetup()->setFitToHeight(0);
    }

    // =====================================================
    // Hoja 2: Detalle
    // =====================================================
    private function hojaDetalle($libro, $def, $filas, $filtros, $generado, $formato) {
        $detalle = $libro->createSheet();
        $detalle->setTitle('Detalle');
        $detalle->setShowGridlines(false);
        $detalle->getTabColor()->setRGB('21A666');

        $columnas = array_keys($def['columnas']);
        $campos = array_values($def['columnas']);
        $cantidadColumnas = count($columnas);
        $ultimaLetra = Coordinate::stringFromColumnIndex($cantidadColumnas);
        $filaEncabezado = 5;
        $primeraFila = $filaEncabezado + 1;

        $this->banner($detalle, "A1:{$ultimaLetra}1", $def['titulo'] . ' — Detalle', 15, 34);
        $this->nota($detalle, "A2:{$ultimaLetra}2", $generado . '   ·   ' . count($filas) . ' registro(s)', 18);
        $this->nota($detalle, "A3:{$ultimaLetra}3", 'Filtros aplicados: ' . $filtros, 30);

        // Encabezados
        foreach ($columnas as $i => $encabezado) {
            $this->poner($detalle, Coordinate::stringFromColumnIndex($i + 1) . $filaEncabezado, $encabezado);
        }
        $detalle->getStyle("A{$filaEncabezado}:{$ultimaLetra}{$filaEncabezado}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $detalle->getRowDimension($filaEncabezado)->setRowHeight(26);

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
                }
                if ($columnas[$i] === 'Estado') {
                    $detalle->getStyle($letra . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    if ($valor === 'Activo') {
                        $detalle->getStyle($letra . $fila)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '1B7F4C']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F9EC']],
                        ]);
                    } elseif ($valor === 'Inactivo') {
                        $detalle->getStyle($letra . $fila)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'C0392B']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE6E6']],
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

        // Fila de totales (SUM de Excel, se recalcula si el usuario filtra)
        $ultimaTabla = $ultimaDato;
        if ($formato['totales'] && count($filas) > 0) {
            $filaTotal = $ultimaDato + 1;
            $this->poner($detalle, "A{$filaTotal}", 'Total');
            foreach ($formato['totales'] as $encabezado) {
                $i = array_search($encabezado, $columnas, true);
                if ($i === false) {
                    continue;
                }
                $letra = Coordinate::stringFromColumnIndex($i + 1);
                $detalle->setCellValue($letra . $filaTotal, "=SUM({$letra}{$primeraFila}:{$letra}{$ultimaDato})");
            }
            $detalle->getStyle("A{$filaTotal}:{$ultimaLetra}{$filaTotal}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => self::TINTA]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::CELESTE]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $detalle->getStyle("A{$filaTotal}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $detalle->getRowDimension($filaTotal)->setRowHeight(22);
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
