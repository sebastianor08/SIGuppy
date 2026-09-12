<?php

    session_start();
    function redirect($url){
        echo "<script>";
            echo "window.location.href='$url'";
        echo "</script>";
    }

    function dd($date){
        echo "<pre>";
        die(print_r($date));
    }
    
    function getUrl($modulo,$controlador,$funcion,$parametros=false,$pagina=false){

        if($pagina==false){
            $pagina = "index";
        }
        $url = "index.php?modulo=$modulo&controlador=$controlador&funcion=$funcion";

        if($parametros){
            foreach($parametros as $key => $value){
                $url .= "&$key=$value";
            }
        }

        return $url;
    }

    function resolve(){
        $modulo = ucwords($_GET['modulo']); //Carpeta Usuario
        $controlador = ucwords($_GET['controlador']); //Archivo UsuariosController.php
        $funcion = $_GET['funcion']; //Metodo getUsers
        if(is_dir("../Controller/$modulo")){

            if(is_file("../Controller/$modulo/".$controlador."Controller.php")){


                include_once "../Controller/$modulo/$controlador"."Controller.php";
                $nombreClass = $controlador."Controller";

                $object = new $nombreClass();
                //$objeto = new UsuariosController();

                if(method_exists($object,$funcion)){

                    $object->$funcion();

                }else{
                    echo "El metodo $funcion no existe en el controlador $controlador";
                }

            }else{
                echo "El controlador $controlador no existe en el modulo $modulo";
            }

        }else{
            echo "El modulo $modulo no existe";
        }
    }

?>