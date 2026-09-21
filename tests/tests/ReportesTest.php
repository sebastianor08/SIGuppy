<?php
use PHPUnit\Framework\TestCase;

class ReportesTest extends TestCase
{
    /**
     * Métodos simulados del Módulo de Reportes de SIGuppy
     */

    // 1. Calcula el total de peces guppy registrados en una lista de tanques
    private function calcularTotalPeces(array $tanques)
    {
        $total = 0;
        foreach ($tanques as $tanque) {
            if (isset($tanque['cantidad']) && is_numeric($tanque['cantidad'])) {
                $total += $tanque['cantidad'];
            }
        }
        return $total;
    }

    // 2. Filtra los zoocriaderos por estado activo/inactivo para el reporte
    private function filtrarZoocriaderosPorEstado(array $zoocriaderos, $estadoRequerido)
    {
        return array_filter($zoocriaderos, function ($zoocriadero) use ($estadoRequerido) {
            return isset($zoocriadero['estado']) && $zoocriadero['estado'] === $estadoRequerido;
        });
    }

    // 3. Genera la estructura de encabezado para la exportación de un reporte
    private function generarEncabezadoReporte($tituloReporte, $autor)
    {
        $tituloReporte = trim($tituloReporte);
        $autor = trim($autor);

        if (empty($tituloReporte) || empty($autor)) {
            return false;
        }

        return [
            'sistema' => 'SIGuppy',
            'titulo' => mb_strtoupper($tituloReporte),
            'generado_por' => $autor,
            'fecha' => date('Y-m-d')
        ];
    }

    /**
     * UT-Reportes-001: Validar el cálculo del total de peces para un reporte de inventario
     */
    public function testCalcularTotalPecesEnReporte()
    {
        $tanquesDemo = [
            ['id' => 1, 'nombre' => 'Tanque A', 'cantidad' => 150],
            ['id' => 2, 'nombre' => 'Tanque B', 'cantidad' => 300],
            ['id' => 3, 'nombre' => 'Tanque C', 'cantidad' => 50]
        ];

        $totalPeces = $this->calcularTotalPeces($tanquesDemo);

        $this->assertEquals(500, $totalPeces, "El total de peces calculado en el reporte debe ser exactamente 500");
    }

    /**
     * UT-Reportes-002: Validar el filtrado de zoocriaderos activos para reporte de estado
     */
    public function testFiltrarZoocriaderosActivos()
    {
        $zoocriaderosDemo = [
            ['id' => 1, 'nombre' => 'Zoocriadero Norte', 'estado' => 'Activo'],
            ['id' => 2, 'nombre' => 'Zoocriadero Sur', 'estado' => 'Inactivo'],
            ['id' => 3, 'nombre' => 'Zoocriadero Central', 'estado' => 'Activo']
        ];

        $activos = $this->filtrarZoocriaderosPorEstado($zoocriaderosDemo, 'Activo');

        $this->assertCount(2, $activos, "El reporte filtrado debe contener exactamente 2 zoocriaderos activos");
    }

    /**
     * UT-Reportes-003: Validar la generación del encabezado estructurado del reporte
     */
    public function testGenerarEncabezadoReporteExitoso()
    {
        $encabezado = $this->generarEncabezadoReporte("Reporte General de Zoocriaderos", "Andrea Rivera");

        $this->assertIsArray($encabezado, "El encabezado del reporte debe retornar una estructura array");
        $this->assertEquals("SIGuppy", $encabezado['sistema']);
        $this->assertEquals("REPORTE GENERAL DE ZOOCRIADEROS", $encabezado['titulo']);
        $this->assertEquals("Andrea Rivera", $encabezado['generado_por']);
    }
}