<?php

<<<<<<< HEAD
    // ============================================================
    // Conexión a PostgreSQL SIN PDO.
    // Usa la extensión nativa "pgsql" de PHP: pg_connect, pg_query,
    // pg_query_params, pg_fetch_assoc...
    //
    // Requisito: en php.ini debe estar activa la línea
    //     extension=pgsql
    // (NO hace falta pdo_pgsql)
    // ============================================================
=======
>>>>>>> 298a2c415ff3c731d1de0ff9cc681fc5c0507046
    class Connection{

        private $host;
        private $user;
        private $password;
        private $database;
        private $port;
<<<<<<< HEAD

        // Estática: aunque se creen varios modelos en la misma
        // petición, todos comparten UNA sola conexión.
        private static $link = null;

        function __construct(){
            if(self::$link === null){
                $this->setConnect();
                $this->connect();
            }
        }

        private function setConnect(){
            require __DIR__ . '/conf.php';

            $this->host     = $host;
            $this->user     = $user;
            $this->password = $password;
            $this->database = $database;
            $this->port     = $port;
        }

        private function connect(){
            // Cadena de conexión con el formato que espera pg_connect
            $cadena = "host="      . $this->host
                    . " port="     . $this->port
                    . " dbname="   . $this->database
                    . " user="     . $this->user
                    . " password=" . $this->password;

            $conexion = @pg_connect($cadena);

            if($conexion === false){
                die("Error de conexión a PostgreSQL. Revise lib/conf/conf.php y "
                  . "que 'extension=pgsql' esté habilitada en php.ini.");
            }

            pg_set_client_encoding($conexion, "UTF8");
            self::$link = $conexion;
        }

        protected function getConnect(){
            return self::$link;
        }

        protected function close(){
            if(self::$link !== null){
                pg_close(self::$link);
                self::$link = null;
            }
        }

    }

?>
=======
        private $link;

        function __construct(){
            $this->setConnect();
            $this->connect();

        }

        private  function setConnect(){
            require_once 'conf.php';

            $this->host = $host;
            $this->user = $user;
            $this->password = $password;
            $this->database = $database;
            $this->port = $port;

        }

        private function connect(){
            $this->link = mysqli_connect(
                $this->host,
                $this->user,
                $this->password,
                $this->database,
                $this->port
            );

            if(!$this->link ){
                die(mysqli_error($this->link));
            }else{
                //echo "Conexio exitosa";
            }

        }
        
        protected function getConnect(){
            return $this->link;
        }

        protected function close(){
            mysqli_close($this->link);
        }


    }

?>
>>>>>>> 298a2c415ff3c731d1de0ff9cc681fc5c0507046
