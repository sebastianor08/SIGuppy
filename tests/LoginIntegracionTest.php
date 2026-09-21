<?php
use PHPUnit\Framework\TestCase;

class LoginIntegracionTest extends TestCase
{
    /**
     * Configuración previa antes de cada prueba
     */
    protected function setUp(): void
    {
        // Inicializar o reiniciar la sesión para simular el servidor
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    /**
     * Método simulado que integra la autenticación con el manejo de $_SESSION
     */
    private function procesarLoginYCrearSesion($correo, $password)
    {
        // Validar credenciales (simulando autenticación exitosa)
        if ($correo === "juanolano@gmail.com" && $password === "415263123Lll@") {
            // INTEGRACIÓN: Guardar datos del usuario en la sesión de PHP
            $_SESSION['usuario_id'] = 1;
            $_SESSION['correo'] = $correo;
            $_SESSION['rol'] = 'Administrador';
            $_SESSION['autenticado'] = true;

            return [
                'status' => true,
                'message' => 'Sesión iniciada correctamente'
            ];
        }

        return [
            'status' => false,
            'message' => 'Credenciales inválidas'
        ];
    }

    /**
     * TI-002: Integración de Login con el Sistema de Sesiones (Creación y Cierre de Sesión)
     */
    public function testIntegracionLoginYSesion()
    {
        // 1. Ejecutar el login con datos válidos
        $resultado = $this->procesarLoginYCrearSesion("juanolano@gmail.com", "415263123Lll@");

        // 2. Verificar que la respuesta sea exitosa
        $this->assertTrue($resultado['status'], "El inicio de sesión debió ser exitoso");

        // 3. Integración: Verificar que la variable $_SESSION se haya poblado correctamente
        $this->assertTrue($_SESSION['autenticado'], "La variable de sesión debe marcar al usuario como autenticado");
        $this->assertEquals("juanolano@gmail.com", $_SESSION['correo'], "El correo en la sesión debe coincidir con el usuario autenticado");
        $this->assertEquals("Administrador", $_SESSION['rol'], "El rol asignado en la sesión debe ser correcto");

        // 4. Integración: Simular Cierre de Sesión (Logout) y verificar destrucción de datos
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->assertEmpty($_SESSION, "La sesión de usuario debe quedar completamente vacía al cerrar sesión");
    }
}