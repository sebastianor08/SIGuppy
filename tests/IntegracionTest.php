<?php
use PHPUnit\Framework\TestCase;

class IntegracionTest extends TestCase
{
    private $rutaDirectorio;

    protected function setUp(): void
    {
        $this->rutaDirectorio = __DIR__ . '/../files';
    }

    /**
     * TI-001: Pruebas de integración entre el módulo de archivos y el almacenamiento local
     */
    public function testIntegracionAlmacenamientoArchivos()
    {
        // 1. Verificar que la carpeta exista
        $this->assertDirectoryExists($this->rutaDirectorio, "La carpeta files/ debe existir en la raíz.");

        // 2. Verificar permisos de escritura
        $this->assertIsWritable($this->rutaDirectorio, "La carpeta files/ debe tener permisos de escritura.");

        // 3. Crear archivo temporal
        $rutaArchivo = $this->rutaDirectorio . '/reporte_zoocriadero_temp.txt';
        $contenido = "Reporte de Integración SIGuppy - Fecha: " . date('Y-m-d H:i:s');

        $escrito = file_put_contents($rutaArchivo, $contenido);
        $this->assertNotFalse($escrito, "El sistema debe permitir escribir el archivo en disco.");

        // 4. Leer archivo y validar contenido
        $contenidoLeido = file_get_contents($rutaArchivo);
        $this->assertEquals($contenido, $contenidoLeido, "El contenido leído debe ser idéntico al escrito.");

        // 5. Eliminar archivo de prueba
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }

        $this->assertFileDoesNotExist($rutaArchivo, "El archivo temporal debe eliminarse correctamente.");
    }
}