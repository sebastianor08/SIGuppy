<?php
class Connection
{

    private $host;
    private $user;
    private $password;
    private $database;
    private $port;
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



        $conexion = @pg_connect($cadena);
        if ($conexion === false) {
            $error = error_get_last();
            error_log("Error de conexión a PostgreSQL: " . print_r($error, true));
            throw new Exception("No fue posible conectar con la base de datos.");
        }



        pg_set_client_encoding($conexion, "UTF8");

        $idUsuarioSesion = $_SESSION['id_usuario'] ?? null;
        @pg_query_params(
            $conexion,
            "SELECT set_config('app.id_usuario', $1, false)",
            [$idUsuarioSesion !== null ? (string) $idUsuarioSesion : '']
        );

        self::$link = $conexion;
    }

    protected function getConnect()
    {
        return self::$link;
    }
    public function actualizarUsuarioAuditoria()
    {
        if (self::$link === null) {
            return;
        }
        $idUsuarioSesion = $_SESSION['id_usuario'] ?? null;
        @pg_query_params(
            self::$link,
            "SELECT set_config('app.id_usuario', $1, false)",
            [$idUsuarioSesion !== null ? (string) $idUsuarioSesion : '']
        );
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