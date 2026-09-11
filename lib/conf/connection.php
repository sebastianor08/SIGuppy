<?php

    class Connection{

        private $host;
        private $user;
        private $password;
        private $database;
        private $port;
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