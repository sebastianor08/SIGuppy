<?php
use PHPUnit\Framework\TestCase;

class ZocriaderoTest extends TestCase
{
    /**
     * Simulación de validación y registro de un Zoocriadero en SIGuppy
     */
    private function registrarZoocriadero($nombre, $capacidad, $ubicacion)
    {
        $nombre = trim($nombre);
        $ubicacion = trim($ubicacion);

        // Validar campos obligatorios
        if (empty($nombre) || empty($ubicacion)) {
            return [
                'status' => false,
                'message' => 'El nombre y la ubicación del zoocriadero son obligatorios'
            ];
        }

        // Validar que la capacidad sea un número válido y mayor a 0
        if (!is_numeric($capacidad) || $capacidad <= 0) {
            return [
                'status' => false,
                'message' => 'La capacidad debe ser un número entero mayor a cero'
            ];
        }

        // Registro exitoso simulado
        return [
            'status' => true,
            'message' => 'Zoocriadero registrado exitosamente',
            'data' => [
                'nombre' => $nombre,
                'capacidad' => (int)$capacidad,
                'ubicacion' => $ubicacion
            ]
        ];
    }

    
    public function testRegistroZoocriaderoExitoso()
    {
        $resultado = $this->registrarZoocriadero("Zoocriadero Central Guppy", 500, "Sede Principal");

        $this->assertTrue($resultado['status'], "El registro debería ser exitoso");
        $this->assertEquals("Zoocriadero registrado exitosamente", $resultado['message']);
        $this->assertEquals(500, $resultado['data']['capacidad']);
    }

    /**
     * TC-004: Validar error cuando la capacidad es inválida (negativa o cero)
     */
    public function testRegistroZoocriaderoCapacidadInvalida()
    {
        $resultado = $this->registrarZoocriadero("Zoocriadero Norte", -10, "Sede Norte");

        $this->assertFalse($resultado['status'], "No debe permitir capacidad negativa");
        $this->assertEquals("La capacidad debe ser un número entero mayor a cero", $resultado['message']);
    }

    /**
     * TC-005: Validar error cuando faltan campos obligatorios
     */
    public function testRegistroZoocriaderoCamposVacios()
    {
        $resultado = $this->registrarZoocriadero("", 100, "");

        $this->assertFalse($resultado['status'], "No debe registrar si faltan datos");
        $this->assertEquals("El nombre y la ubicación del zoocriadero son obligatorios", $resultado['message']);
    }
}