<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Model/Tanque/TanqueModel.php';

class TanqueModelTest extends TestCase
{
    public function testZoocriaderoExisteConIdInvalido()
    {
        $modelo = new TanqueModel();

        $idInvalido = 0;

        $resultado = $modelo->zoocriaderoExiste($idInvalido);

        $this->assertFalse($resultado, "Se esperaba que el id de zoocriadero 0 no existiera");

        echo "\nzoocriaderoExiste($idInvalido) devolvió: " . var_export($resultado, true) . "\n";
    }
}