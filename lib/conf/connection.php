<?php

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