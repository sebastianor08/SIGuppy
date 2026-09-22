<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Model/Roles/RolesModel.php';

class RolesModelTest extends TestCase
{
    public function testExisteNombreRolConNombreInexistente()
    {
        $modelo = new RolesModel();

        $rolInexistente = "RolDePruebaQA_" . time();

        $resultado = $modelo->existeNombreRol($rolInexistente);

        $this->assertFalse($resultado, "Se esperaba que el nombre de rol de prueba no existiera en la base de datos");

        echo "\nexisteNombreRol('$rolInexistente') devolvió: " . var_export($resultado, true) . "\n";
    }
}