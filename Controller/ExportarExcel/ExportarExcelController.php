<?php

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../../Model/ExportarExcel/ExportarExcelModel.php';
require_once __DIR__ . '/../../View/ExportarExcel/ExportarExcelView.php';

// Se llama por:
//   Web/ajax.php?modulo=ExportarExcel&controlador=ExportarExcel&funcion=descargar&reporte=<data-page>&<filtros>

class ExportarExcelController
{

    public function descargar()
    {
        $modelo  = new ExportarExcelModel();
        $reporte = $_GET['reporte'] ?? '';

        if (!$modelo->existe($reporte)) {
            http_response_code(400);
            echo 'Reporte no válido.';
            return;
        }

        $autoload = __DIR__ . '/../../lib/phpspreadsheet/vendor/autoload.php';
        if (!is_file($autoload)) {
            http_response_code(500);
            echo 'Falta instalar PhpSpreadsheet: ejecute "composer install" dentro de la carpeta lib/phpspreadsheet.';
            return;
        }
        require_once $autoload;

        // Captura cualquier salida  de los controladores
        ob_start();
        $datos = $modelo->obtenerReporte($reporte);
        $libro = (new ExportarExcelView())->construir($datos);
        ob_end_clean();

        $nombre = 'reporte_' . $reporte . '_completo_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Cache-Control: max-age=0');

        $escritor = new Xlsx($libro);
        $escritor->setIncludeCharts(true); // sin esto los gráficos de la hoja Resumen no se guardan
        $escritor->save('php://output');
        exit;
    }
}
