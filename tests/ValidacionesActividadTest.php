<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../lib/validaciones.php';

/**
 * Pruebas de las funciones de lib/validaciones.php que usa
 * ActividadController::validar() para el nombre (3-60 caracteres)
 * y la descripción (1-200 caracteres) de una actividad.
 */
final class ValidacionesActividadTest extends TestCase
{
    // ---------------------------------------------------------
    // limpiar()
    // ---------------------------------------------------------

    public function testLimpiarQuitaEspaciosAlInicioYAlFinal(): void
    {
        $this->assertSame('Fumigación', limpiar('   Fumigación   '));
    }

    public function testLimpiarColapsaEspaciosMultiplesEnUno(): void
    {
        $this->assertSame('Control de larvas', limpiar('Control    de     larvas'));
    }

    public function testLimpiarConvierteTabsYSaltosDeLineaEnEspacios(): void
    {
        $this->assertSame('Recolección de inservibles', limpiar("Recolección\tde\ninservibles"));
    }

    // ---------------------------------------------------------
    // validarTexto() aplicado a "nombre" de actividad (min 3, max 60)
    // ---------------------------------------------------------

    public function testNombreValidoNoDevuelveError(): void
    {
        $this->assertNull(validarTexto('Fumigación', 'Nombre', 3, 60));
    }

    public function testNombreVacioEsObligatorio(): void
    {
        $error = validarTexto('', 'Nombre', 3, 60);
        $this->assertNotNull($error);
        $this->assertStringContainsString('obligatorio', $error);
    }

    public function testNombreSoloConEspaciosSeConsideraVacio(): void
    {
        $error = validarTexto('     ', 'Nombre', 3, 60);
        $this->assertNotNull($error);
        $this->assertStringContainsString('obligatorio', $error);
    }

    public function testNombreMasCortoQueElMinimoDaError(): void
    {
        $error = validarTexto('Fu', 'Nombre', 3, 60); // 2 caracteres, mínimo 3
        $this->assertNotNull($error);
        $this->assertStringContainsString('al menos 3 caracteres', $error);
    }

    public function testNombreEnElLimiteMinimoEsValido(): void
    {
        // Exactamente 3 caracteres: debe pasar (el mínimo es inclusivo)
        $this->assertNull(validarTexto('Fum', 'Nombre', 3, 60));
    }

    public function testNombreMasLargoQueElMaximoDaError(): void
    {
        $nombreDe61Caracteres = str_repeat('a', 61);
        $error = validarTexto($nombreDe61Caracteres, 'Nombre', 3, 60);
        $this->assertNotNull($error);
        $this->assertStringContainsString('no puede superar 60 caracteres', $error);
    }

    public function testNombreEnElLimiteMaximoEsValido(): void
    {
        $nombreDe60Caracteres = str_repeat('a', 60);
        $this->assertNull(validarTexto($nombreDe60Caracteres, 'Nombre', 3, 60));
    }

    public function testNombreSoloConSimbolosDaError(): void
    {
        // Cumple el largo mínimo pero no tiene letras ni números
        $error = validarTexto('!!!!!', 'Nombre', 3, 60);
        $this->assertNotNull($error);
        $this->assertStringContainsString('letras o números', $error);
    }

   

    public function testDescripcionDeUnSoloCaracterEsValida(): void
    {
        $this->assertNull(validarTexto('x', 'Descripción', 1, 200));
    }

    public function testDescripcionVaciaDaError(): void
    {
        $error = validarTexto('', 'Descripción', 1, 200);
        $this->assertNotNull($error);
        $this->assertStringContainsString('obligatorio', $error);
    }

    public function testDescripcionDe201CaracteresDaError(): void
    {
        $descripcionMuyLarga = str_repeat('a', 201);
        $error = validarTexto($descripcionMuyLarga, 'Descripción', 1, 200);
        $this->assertNotNull($error);
        $this->assertStringContainsString('no puede superar 200 caracteres', $error);
    }

    public function testDescripcionDeExactamente200CaracteresEsValida(): void
    {
        $descripcionLimite = str_repeat('a', 200);
        $this->assertNull(validarTexto($descripcionLimite, 'Descripción', 1, 200));
    }
}