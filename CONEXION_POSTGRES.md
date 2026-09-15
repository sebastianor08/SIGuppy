# SIGuppys — Conectar PHP con PostgreSQL sin PDO

## 1. Habilitar la extensión pgsql

En tu `php.ini` (Laragon: menú Laragon → PHP → php.ini) quita el `;` de:

```ini
extension=pgsql
```

No necesitas `pdo_pgsql`. Guarda y **reinicia Apache**.

Para comprobar, crea `test.php` en la raíz de www:

```php
<?php var_dump(function_exists('pg_connect')); ?>
```

Debe imprimir `bool(true)`.

## 2. Crear la base y ejecutar el script

```sql
CREATE DATABASE bd_dengue_siguppy;
```

Conéctate a esa base y ejecuta `BD_Dengue_SIGuppy.sql` completo. Además de las
tablas trae dos secciones de datos al final:

- **DATOS INICIALES DE CATÁLOGO** → acciones y módulos (las filas y columnas
  del formulario Registro Roles).
- **DATOS BASE DEL SISTEMA** → tipos de documento, roles, usuarios, tipos de
  tanque, tipos de depósito, actividades, zoocriaderos y tanques.

Si ya habías ejecutado el script antes, borra la base y créala de nuevo para
evitar registros duplicados.

## 3. Configurar la conexión

`lib/conf/conf.php`:

```php
$host     = "127.0.0.1";
$user     = "postgres";
$password = "tu_clave";
$database = "bd_dengue_siguppy";
$port     = "5432";
```

## 4. Cómo funciona

`lib/conf/connection.php` arma la cadena de `pg_connect`:

```php
$cadena = "host=127.0.0.1 port=5432 dbname=bd_dengue_siguppy user=postgres password=...";
$conexion = pg_connect($cadena);
```

`Model/MasterModel.php` ejecuta con `pg_query` (consultas fijas) o
`pg_query_params` (cuando hay datos del usuario). Con `pg_query_params` los
valores van aparte del SQL como `$1, $2, $3`:

```php
$this->selectAll("SELECT * FROM rol WHERE id_rol = $1", [$id]);
```

Métodos: `insert()`, `select()`, `update()`, `delete()`, `selectAll()`,
`selectOne()`, `selectValue()`, `beginTransaction()`, `commit()`, `rollBack()`.

## 5. Pantallas conectadas a la base

| Pantalla | Qué hace |
|---|---|
| `View/Roles/registro-roles.php` | INSERT en `rol` + `rol_permiso` |
| `View/Roles/consultar-roles.php` | Lista roles y permisos, DELETE |
| `View/Zoocriadero/zoocriaderos.php` | Lista, INSERT, UPDATE y cambio de estado de `zoocriadero`; INSERT en `tanque` |
| `View/Seguimiento_Zoocriadero/seguimiento-zoocriadero.php` | INSERT y UPDATE en `seguimiento_zoocriadero` + `actividad_zoocriadero` |

Las pantallas de zoocriaderos y seguimiento hablan con el backend por
`Web/ajax.php`, que enruta a `Controller/<Modulo>/<Modulo>Controller.php`.
