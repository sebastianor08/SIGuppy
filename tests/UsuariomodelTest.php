<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Model/Usuarios/UsuarioModel.php';

class UsuarioModelTest extends TestCase
{
    public function testExisteCorreoConCorreoInexistente()
    {
        $modelo = new UsuarioModel();

        $correoInexistente = "usuario_no_registrado_qa_" . time() . "@siguppy.test";

        $resultado = $modelo->existeCorreo($correoInexistente);

        $this->assertFalse($resultado, "Se esperaba que el correo de prueba no existiera en la base de datos");

        echo "\nexisteCorreo('$correoInexistente') devolvió: " . var_export($resultado, true) . "\n";
    }
}