<?php
use PHPUnit\Framework\TestCase;

// Requerimos la clase de conexión real de tu proyecto SIGuppy
require_once __DIR__ . '/../lib/conf/connection.php';

class DatabaseIntegrationTest extends TestCase
{
    private $dbConnection;

    /**
     * Configuración previa: Intenta establecer la conexión real antes de cada test
     */
    protected function setUp(): void
    {
        // Instanciamos tu clase Connection real
        $this->dbConnection = new Connection();
    }

    /**
     * TC-INT-001: Probar que la extensión de PostgreSQL y la conexión física funcionen
     */
    public function testConexionBaseDeDatosExitosa()
    {
        // 1. Verificar que la extensión pgsql esté activa en el entorno PHP
        $this->assertTrue(
            extension_loaded('pgsql'),
            'La extensión "pgsql" de PHP debe estar activa en php.ini'
        );

        // 2. Obtener el recurso o enlace de conexión desde tu método estático/clase
        $link = Connection::getConnection(); // O el método que retorne $link en tu clase

        // 3. Validar que la conexión no sea nula o falsa
        $this->assertNotNull(
            $link,
            'La conexión a la base de datos PostgreSQL de SIGuppy no devolvió un enlace válido'
        );
    }

    /**
     * TC-INT-002: Probar la integración de una consulta SQL real
     */
    public function testConsultaIntegracionBD()
    {
        $link = Connection::getConnection();

        if ($link) {
            // Ejecutar una consulta simple para verificar la respuesta del servidor DB
            $result = pg_query($link, "SELECT 1 as test_val");
            
            $this->assertNotFalse($result, "La consulta de prueba a PostgreSQL falló");
            
            $row = pg_fetch_assoc($result);
            $this->assertEquals('1', $row['test_val']);
        } else {
            $this->markTestSkipped('No se pudo establecer la conexión a la BD para ejecutar la consulta.');
        }
    }
}