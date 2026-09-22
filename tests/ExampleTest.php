<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testSum()
    {
        $a = 2;
        $b = 3;

        $resultado = $a + $b;

        // Comprueba que el resultado esperado sea 5
        $this->assertEquals(
            5,
            $resultado,
            "Se esperaba que 2 + 3 sea igual a 5"
        );

        // Muestra el resultado en consola
        echo "\nEl resultado de $a + $b es: $resultado\n";
    }
}