<?php

    date_default_timezone_set('America/Bogota');

    session_start();

    include_once __DIR__ . '/validaciones.php';
    include_once __DIR__ . '/sesion_config.php';
    include_once __DIR__ . '/permisos.php';

    // Las peticiones AJAX (Web/ajax.php) no pasan por requiere_sesion.php,
    // así que la inactividad se revisa también aquí: si la sesión ya
    // estaba vencida, se cierra y se avisa al JS para que redirija a
    // login en vez de mostrar datos de una sesión que ya no debería
    // estar activa. Si sigue vigente, esta misma petición cuenta como
    // actividad y renueva el conteo.
    if (isset($_GET['modulo'])) {
        if (sigSesionInactivaVencida()) {
            sigCerrarPorInactividad();
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'sesionExpirada' => true, 'message' => 'Tu sesión expiró por inactividad.'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        if (!empty($_SESSION['id_usuario']) && !empty($_SESSION['debe_cambiar_contrasena'])) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Debes actualizar tu contraseña antes de continuar.'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        sigRegistrarActividad();
    }
    function redirect($url){
        echo "<script>";
            echo "window.location.href='$url'";
        echo "</script>";
        exit; // sin esto, el código que sigue después de llamar a redirect()
              // se sigue ejecutando y puede disparar una segunda redirección
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

    // Respuesta JSON para las peticiones que entran por Web/ajax.php.
    // (El controlador de Seguimiento ya la llamaba, pero no estaba definida.)
    function jsonResponse($data, $codigo = 200){
        if(!headers_sent()){
            http_response_code($codigo);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Lee el cuerpo de la peticion cuando viene como JSON (fetch).
    // Si viene como formulario normal, devuelve $_POST.
    function requestJsonBody(){
        $crudo = file_get_contents('php://input');
        if($crudo === false || trim($crudo) === ''){
            return $_POST;
        }
        $datos = json_decode($crudo, true);
        return is_array($datos) ? $datos : $_POST;
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
