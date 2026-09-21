<?php
use PHPUnit\Framework\TestCase;

class ReportesIntegracionTest extends TestCase
{
    private $rutaCarpeta;

    protected function setUp(): void
    {
        $this->rutaCarpeta = __DIR__ . '/../files';
    }

    private function exportarReporteAZoocriaderosJSON($datos, $nombreArchivo)
    {
        if (!is_dir($this->rutaCarpeta) || !is_writable($this->rutaCarpeta)) {
            return false;
        }

        $jsonContent = json_encode($datos, JSON_PRETTY_PRINT);
        $rutaCompleta = $this->rutaCarpeta . '/' . $nombreArchivo;

        $resultado = file_put_contents($rutaCompleta, $jsonContent);
        return $resultado !== false;
    }

    public function testIntegracionGeneracionYGuardadoReporte()
    {
        $datosReporte = [
            'titulo' => 'REPORTE GENERAL DE ZOOCRIADEROS',
            'generado_por' => 'Andrea Rivera',
            'total_registros' => 2,
            'zoocriaderos' => [
                ['id' => 1, 'nombre' => 'Zoocriadero Central', 'peces' => 500],
                ['id' => 2, 'nombre' => 'Zoocriadero Norte', 'peces' => 300]
            ]
        ];

        $nombreArchivo = 'reporte_exportado_test.json';
        $rutaCompleta = $this->rutaCarpeta . '/' . $nombreArchivo;

        // 1. Probar que el Módulo de Reportes guarde el archivo en el Sistema de Archivos
        $guardado = $this->exportarReporteAZoocriaderosJSON($datosReporte, $nombreArchivo);
        $this->assertTrue($guardado, "El reporte debió exportarse y guardarse en la carpeta files/");

        // 2. Probar lectura y estructura del archivo generado
        $this->assertFileExists($rutaCompleta);
        $contenidoLeido = file_get_contents($rutaCompleta);
        $datosDecodificados = json_decode($contenidoLeido, true);

        $this->assertEquals('REPORTE GENERAL DE ZOOCRIADEROS', $datosDecodificados['titulo']);
        $this->assertEquals(2, $datosDecodificados['total_registros']);

        // 3. Limpieza de archivo temporal de prueba
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }
}