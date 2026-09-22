<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Model/Actividad/ActividadModel.php';

/**
 * Doble de prueba de ActividadModel.
 *
 * Sobrescribe el constructor para NO conectarse a PostgreSQL, y
 * sobrescribe los 4 métodos heredados de MasterModel (selectAll,
 * selectOne, selectValue, update) para:
 *   1) guardar el SQL y los parámetros que ActividadModel les envía
 *   2) devolver un valor "de mentiras" que el propio test controla
 *
 * Así se puede probar la LÓGICA de ActividadModel (qué SQL arma,
 * en qué orden manda los parámetros, qué hace con el resultado)
 * sin depender de que haya una base de datos real disponible.
 */
class ActividadModelDeDoble extends ActividadModel
{
    public array $llamadas = [];

    public $selectAllRetorno = [];
    public $selectOneRetorno = null;
    public $selectValueRetorno = null;
    public $updateRetorno = true;

    public function __construct()
    {
        // Intencionalmente vacío: NO se llama a parent::__construct(),
        // así se evita el pg_connect() real.
    }

    public function selectAll($sql, $parametros = [])
    {
        $this->llamadas[] = ['metodo' => 'selectAll', 'sql' => $sql, 'parametros' => $parametros];
        return $this->selectAllRetorno;
    }

    public function selectOne($sql, $parametros = [])
    {
        $this->llamadas[] = ['metodo' => 'selectOne', 'sql' => $sql, 'parametros' => $parametros];
        return $this->selectOneRetorno;
    }

    public function selectValue($sql, $parametros = [])
    {
        $this->llamadas[] = ['metodo' => 'selectValue', 'sql' => $sql, 'parametros' => $parametros];
        return $this->selectValueRetorno;
    }

    public function update($sql, $parametros = [])
    {
        $this->llamadas[] = ['metodo' => 'update', 'sql' => $sql, 'parametros' => $parametros];
        return $this->updateRetorno;
    }
}

final class ActividadModelTest extends TestCase
{
    private function normalizarSql(string $sql): string
    {
        return trim(preg_replace('/\s+/', ' ', $sql));
    }

    // ---------------------------------------------------------
    // listar()
    // ---------------------------------------------------------

    public function testListarFiltraPorAmbitoTerrenoYDevuelveLosDatos(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectAllRetorno = [
            ['id_actividad' => 1, 'nombre' => 'Fumigación', 'descripcion' => 'Control de larvas', 'estado' => 1],
            ['id_actividad' => 2, 'nombre' => 'Recolección de inservibles', 'descripcion' => 'Retiro de criaderos', 'estado' => 0],
        ];

        $resultado = $modelo->listar();

        $this->assertSame($modelo->selectAllRetorno, $resultado, 'listar() debe devolver tal cual lo que retorna selectAll()');

        $llamada = $modelo->llamadas[0];
        $this->assertSame('selectAll', $llamada['metodo']);
        $this->assertSame(
            'SELECT id_actividad, nombre, descripcion, estado FROM actividad WHERE ambito = $1 ORDER BY id_actividad',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame(['terreno'], $llamada['parametros'], 'Debe filtrar siempre por ambito = terreno');
    }

    // ---------------------------------------------------------
    // buscar()
    // ---------------------------------------------------------

    public function testBuscarEnviaElIdYElAmbitoComoParametros(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectOneRetorno = ['id_actividad' => 5, 'nombre' => 'Fumigación', 'descripcion' => 'x', 'estado' => 1];

        $resultado = $modelo->buscar(5);

        $this->assertSame($modelo->selectOneRetorno, $resultado);

        $llamada = $modelo->llamadas[0];
        $this->assertSame(
            'SELECT id_actividad, nombre, descripcion, estado FROM actividad WHERE id_actividad = $1 AND ambito = $2',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame([5, 'terreno'], $llamada['parametros']);
    }

    public function testBuscarDevuelveNullCuandoNoExiste(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectOneRetorno = null;

        $this->assertNull($modelo->buscar(999));
    }

    // ---------------------------------------------------------
    // existeNombre()
    // ---------------------------------------------------------

    public function testExisteNombreSinExcluirDevuelveTrueCuandoHayCoincidencia(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectValueRetorno = 1; // simula que la BD encontró una fila

        $resultado = $modelo->existeNombre('Fumigación');

        $this->assertTrue($resultado);

        $llamada = $modelo->llamadas[0];
        $this->assertSame(
            'SELECT 1 FROM actividad WHERE ambito = $1 AND LOWER(nombre) = LOWER($2)',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame(['terreno', 'Fumigación'], $llamada['parametros']);
    }

    public function testExisteNombreSinExcluirDevuelveFalseCuandoNoHayCoincidencia(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectValueRetorno = null;

        $this->assertFalse($modelo->existeNombre('Actividad nueva'));
    }

    public function testExisteNombreConIdExcluirAgregaLaCondicionDeExclusion(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectValueRetorno = null; // no hay OTRA actividad con ese nombre

        $resultado = $modelo->existeNombre('Fumigación', 7);

        $this->assertFalse($resultado);

        $llamada = $modelo->llamadas[0];
        $this->assertSame(
            'SELECT 1 FROM actividad WHERE ambito = $1 AND LOWER(nombre) = LOWER($2) AND id_actividad <> $3',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame(['terreno', 'Fumigación', 7], $llamada['parametros']);
    }

    // ---------------------------------------------------------
    // crear()
    // ---------------------------------------------------------

    public function testCrearEnviaAmbitoNombreYDescripcionEnEseOrden(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectValueRetorno = '42'; // RETURNING id_actividad

        $id = $modelo->crear(['nombre' => 'Riego de plantas', 'descripcion' => 'Actividad de mantenimiento']);

        $this->assertSame('42', $id);

        $llamada = $modelo->llamadas[0];
        $this->assertSame(
            'INSERT INTO actividad (ambito, nombre, descripcion) VALUES ($1, $2, $3) RETURNING id_actividad',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame(['terreno', 'Riego de plantas', 'Actividad de mantenimiento'], $llamada['parametros']);
    }

    public function testCrearDevuelveNullSiLaInsercionFalla(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->selectValueRetorno = null;

        $id = $modelo->crear(['nombre' => 'X', 'descripcion' => 'Y']);

        $this->assertNull($id);
    }

    // ---------------------------------------------------------
    // actualizar()
    // ---------------------------------------------------------

    public function testActualizarDevuelveTrueCuandoLaActualizacionEsExitosa(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->updateRetorno = true;

        $resultado = $modelo->actualizar(3, ['nombre' => 'Nuevo nombre', 'descripcion' => 'Nueva descripción']);

        $this->assertTrue($resultado);

        $llamada = $modelo->llamadas[0];
        $this->assertSame(
            'UPDATE actividad SET nombre = $1, descripcion = $2 WHERE id_actividad = $3 AND ambito = $4',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame(['Nuevo nombre', 'Nueva descripción', 3, 'terreno'], $llamada['parametros']);
    }

    public function testActualizarDevuelveFalseCuandoUpdateFalla(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->updateRetorno = false; // ejecutar() devuelve false ante un error SQL

        $resultado = $modelo->actualizar(3, ['nombre' => 'X', 'descripcion' => 'Y']);

        $this->assertFalse($resultado);
    }

    // ---------------------------------------------------------
    // cambiarEstado()
    // ---------------------------------------------------------

    public function testCambiarEstadoAHabilitadaEnviaLosParametrosCorrectos(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->updateRetorno = true;

        $resultado = $modelo->cambiarEstado(10, 1);

        $this->assertTrue($resultado);

        $llamada = $modelo->llamadas[0];
        $this->assertSame(
            'UPDATE actividad SET estado = $1 WHERE id_actividad = $2 AND ambito = $3',
            $this->normalizarSql($llamada['sql'])
        );
        $this->assertSame([1, 10, 'terreno'], $llamada['parametros']);
    }

    public function testCambiarEstadoAInhabilitadaDevuelveFalseCuandoUpdateFalla(): void
    {
        $modelo = new ActividadModelDeDoble();
        $modelo->updateRetorno = false;

        $this->assertFalse($modelo->cambiarEstado(10, 0));
    }
}