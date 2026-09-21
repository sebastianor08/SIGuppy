<?php
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
   
    private function autenticarUsuario($email, $password)
    {
        // Limpiar datos de entrada (similar a la sanetización en login)
        $email = trim($email);
        $password = trim($password);

        // Validación de campos obligatorios
        if (empty($email) || empty($password)) {
            return [
                'status' => false,
                'message' => 'El correo y la contraseña son obligatorios'
            ];
        }

        // Simulación de credenciales válidas en SIGuppy
        if ($email === "juanolano@gmail.com" && $password === "415263123Lll@") {
            return [
                'status' => true,
                'message' => 'Inicio de sesión exitoso'
            ];
        }

        // Credenciales incorrectas
        return [
            'status' => false,
            'message' => 'Usuario o contraseña incorrectos, inténtalo de nuevo'
        ];
    }

    /**
     * TC-001: Validar inicio de sesión exitoso con credenciales válidas
     */
    public function testInicioDeSesionExitoso()
    {
        $correoValido = "juanolano@gmail.com";
        $passwordValida = "415263123Lll@";

        $resultado = $this->autenticarUsuario($correoValido, $passwordValida);

        // Aserciones
        $this->assertTrue($resultado['status'], "El inicio de sesión debería ser exitoso");
        $this->assertEquals("Inicio de sesión exitoso", $resultado['message']);
    }

    /**
     * TC-002: Validar que el sistema rechace credenciales inválidas
     */
    public function testInicioDeSesionFallidoCredencialesIncorrectas()
    {
        $correoInvalido = "fakeUser@gmail.com";
        $passwordInvalida = "00000";

        $resultado = $this->autenticarUsuario($correoInvalido, $passwordInvalida);

        // Aserciones
        $this->assertFalse($resultado['status'], "El inicio de sesión no debe permitirse con datos falsos");
        $this->assertEquals("Usuario o contraseña incorrectos, inténtalo de nuevo", $resultado['message']);
    }

    /**
     * Validar rechazo cuando los campos se envían vacíos
     */
    public function testInicioDeSesionCamposVacios()
    {
        $resultado = $this->autenticarUsuario("", "");

        // Aserciones
        $this->assertFalse($resultado['status'], "No debe permitir el ingreso con campos vacíos");
        $this->assertEquals("El correo y la contraseña son obligatorios", $resultado['message']);
    }
}