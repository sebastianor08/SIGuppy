<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ReporteHelper.php';

/**
 * UT-Reportes-003
 * Verifica que generarEncabezadoReporte() formatee correctamente
 * el título en mayúsculas y asigne autor y fecha.
 */
final class ReporteHelperTest extends TestCase
{
    public function test_generarEncabezadoReporte_formateaCorrectamente(): void
    {
        // Datos de entrada
        $sistema = 'SIGCETV';
        $titulo  = 'Reporte General de Zoocriaderos';
        $autor   = 'Andrea Rivera';

        $helper = new ReporteHelper();
        $resultado = $helper->generarEncabezadoReporte($sistema, $titulo, $autor);

        // El resultado debe ser un array
        $this->assertIsArray($resultado);

        // Campos obligatorios presentes
        $this->assertArrayHasKey('sistema', $resultado);
        $this->assertArrayHasKey('titulo', $resultado);
        $this->assertArrayHasKey('autor', $resultado);
        $this->assertArrayHasKey('fecha', $resultado);

        // Título convertido a mayúsculas
        $this->assertEquals('REPORTE GENERAL DE ZOOCRIADEROS', $resultado['titulo']);

        // Autor asignado correctamente
        $this->assertEquals('Andrea Rivera', $resultado['autor']);

        // Fecha con formato Y-m-d (ej. 2026-09-20)
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $resultado['fecha']);

        // Ningún campo obligatorio debe estar vacío
        foreach ($resultado as $campo => $valor) {
            $this->assertNotEmpty($valor, "El campo '$campo' no debería estar vacío.");
        }
    }
}