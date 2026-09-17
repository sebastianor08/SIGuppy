<?php

// ============================================================
// Conexión a PostgreSQL SIN PDO.
// Usa la extensión nativa "pgsql" de PHP: pg_connect, pg_query,
// pg_query_params, pg_fetch_assoc...
//
// Requisito: en php.ini debe estar activa la línea
//     extension=pgsql
// (NO hace falta pdo_pgsql)
// ============================================================
class Connection
{

    private $host;
    private $user;
    private $password;
    private $database;
    private $port;

    // Estática: aunque se creen varios modelos en la misma
    // petición, todos comparten UNA sola conexión.
    private static $link = null;

    function __construct()
    {
        if (self::$link === null) {
            $this->setConnect();
            $this->connect();
        }
    }

    private function setConnect()
    {
        require __DIR__ . '/conf.php';

        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    private function connect()
    {
        // Cadena de conexión con el formato que espera pg_connect
        $cadena = "host=" . $this->host
            . " port=" . $this->port
            . " dbname=" . $this->database
            . " user=" . $this->user
            . " password=" . $this->password;



        $conexion = pg_connect($cadena);

        if ($conexion === false) {
            $error = error_get_last();

            die("ERROR REAL DE POSTGRESQL:<br><pre>" .
                print_r($error, true) .
                "</pre>");
        }



        pg_set_client_encoding($conexion, "UTF8");
        self::$link = $conexion;

        $this->avisarUsuarioActual($conexion);
    }

    // ============================================================
    // Auditoría: los triggers de la base (fn_auditoria_general,
    // fn_auditoria_seguimiento_zoocriadero) leen quién hizo el
    // cambio con fn_usuario_actual(), que a su vez lee el parámetro
    // de sesión de Postgres "app.usuario_actual". Aquí es donde se
    // lo informamos, tomando el usuario logueado en PHP, para que
    // cualquier INSERT/UPDATE/DELETE hecho en esta conexión quede
    // asociado a la persona correcta en auditoria_sistema y en
    // auditoria_seguimiento_zoocriadero.
    // ============================================================
    private function avisarUsuarioActual($conexion)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = $_SESSION['id_usuario'] ?? null;

        if ($idUsuario !== null) {
            pg_query_params(
                $conexion,
                "SELECT set_config('app.usuario_actual', $1, false)",
                [ (string) $idUsuario ]
            );
        }
    }

    protected function getConnect()
    {
        return self::$link;
    }

    protected function close()
    {
        if (self::$link !== null) {
            pg_close(self::$link);
            self::$link = null;
        }
    }

}

?>