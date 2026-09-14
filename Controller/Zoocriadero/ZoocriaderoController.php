<?php

include_once '../Model/Zoocriadero/ZoocriaderoModel.php';

// ============================================================
// Controlador del módulo Zoocriaderos. Responde solo JSON, así
// que se llama siempre por Web/ajax.php:
//   Web/ajax.php?modulo=Zoocriadero&controlador=Zoocriadero&funcion=lista
// ============================================================
class ZoocriaderoController{

    // ---------- Lecturas ----------

    public function lista(){
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->listar()]);
    }

    public function comunas(){
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->usuariosActivos()]);
    public function comunas(){
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->comunas()]);
    }

    public function barrios(){
        $obj = new ZoocriaderoModel();
        $idComuna = filter_var($_GET['id_comuna'] ?? null, FILTER_VALIDATE_INT);

        if(!$idComuna){
            jsonResponse(['ok' => false, 'message' => 'Debe indicar la comuna.'], 422);
        }
        jsonResponse(['ok' => true, 'data' => $obj->barriosDe($idComuna)]);
    }

    public function tiposTanque(){
        $obj = new ZoocriaderoModel();
        jsonResponse(['ok' => true, 'data' => $obj->tiposTanque()]);
    }

    public function tanques(){
        $obj   = new ZoocriaderoModel();
        $idZoo = filter_var($_GET['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);

        if(!$idZoo){
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio.'], 422);
        }
        jsonResponse(['ok' => true, 'data' => $obj->tanquesDe($idZoo)]);
    }

    // ---------- Escrituras ----------

    public function postCreate(){
        $obj    = new ZoocriaderoModel();
        $body   = requestJsonBody();
        $datos  = $this->validarZoocriadero($body, $obj);

        $id = $obj->crear($datos);
        if($id === null){
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => 'Zoocriadero registrado correctamente.',
            'id_zoocriadero' => (int) $id,
        ], 201);
    }

    public function postUpdate(){
        $obj  = new ZoocriaderoModel();
        $body = requestJsonBody();

        $idZoo = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        if(!$idZoo){
            jsonResponse(['ok' => false, 'message' => 'id_zoocriadero es obligatorio.'], 422);
        }
        if(!$obj->buscar($idZoo)){
            jsonResponse(['ok' => false, 'message' => 'El zoocriadero no existe.'], 404);
        }

        $datos = $this->validarZoocriadero($body, $obj);

        if($obj->actualizar($idZoo, $datos) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo actualizar: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Zoocriadero actualizado correctamente.']);
    }

    public function postEstado(){
        $obj  = new ZoocriaderoModel();
        $body = requestJsonBody();

        $idZoo  = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($body['estado'] ?? null, FILTER_VALIDATE_INT);

        if(!$idZoo || ($estado !== 0 && $estado !== 1)){
            jsonResponse(['ok' => false, 'message' => 'Datos incompletos para cambiar el estado.'], 422);
        }

        if($obj->cambiarEstado($idZoo, $estado) === false){
            jsonResponse(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }

        jsonResponse([
            'ok' => true,
            'message' => $estado === 1 ? 'Zoocriadero habilitado.' : 'Zoocriadero inhabilitado.',
        ]);
    }

    public function postTanque(){
        $obj  = new ZoocriaderoModel();
        $body = requestJsonBody();

        $idZoo   = filter_var($body['id_zoocriadero'] ?? null, FILTER_VALIDATE_INT);
        $idTipo  = filter_var($body['id_tipo_tanque'] ?? null, FILTER_VALIDATE_INT);
        $numero  = filter_var($body['numero_tanque'] ?? null, FILTER_VALIDATE_INT);

        if(!$idZoo){
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un zoocriadero.'], 422);
        }
        if(!$idTipo || !$obj->tipoTanqueExiste($idTipo)){
            jsonResponse(['ok' => false, 'message' => 'Debe seleccionar un tipo de tanque válido.'], 422);
        }
        if(!$numero || $numero <= 0){
            jsonResponse(['ok' => false, 'message' => 'El número de tanque debe ser un entero mayor que cero.'], 422);
        }
        if(!$obj->buscar($idZoo)){
            jsonResponse(['ok' => false, 'message' => 'El zoocriadero no existe.'], 404);
        }
        if($obj->existeNumeroTanque($idZoo, $numero)){
            jsonResponse(['ok' => false, 'message' => "Ese zoocriadero ya tiene un tanque número $numero."], 422);
        }

        $id = $obj->crearTanque($idZoo, $idTipo, $numero);
        if($id === null){
            jsonResponse(['ok' => false, 'message' => 'No se pudo registrar el tanque: ' . $obj->ultimoError()], 500);
        }

        jsonResponse(['ok' => true, 'message' => 'Tanque registrado correctamente.'], 201);
    }

    // ---------- Validación compartida por create y update ----------
    private function validarZoocriadero($body, $obj){
        // limpiar() recorta y colapsa espacios: así un campo escrito solo
        // con la barra espaciadora queda como cadena vacía y no pasa.
        $nombre    = limpiar($body['nombre'] ?? '');
        $direccion = limpiar($body['direccion'] ?? '');
        $comuna    = limpiar($body['comuna'] ?? '');
        $barrio    = limpiar($body['barrio'] ?? '');
        $latitud   = $body['latitud'] ?? null;
        $longitud  = $body['longitud'] ?? null;

        foreach([
            validarTexto($nombre, 'Nombre', 3, 100),
            validarTexto($direccion, 'Dirección', 5, 200),
            validarTextoOpcional($comuna, 'Comuna', 60),
            validarTextoOpcional($barrio, 'Barrio', 60),
        ] as $error){
            if($error !== null){
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }
        if($idUsuario && !$obj->usuarioExiste($idUsuario)){
            jsonResponse(['ok' => false, 'message' => 'La persona a cargo seleccionada no es válida.'], 422);
        // limpiar() recorta y colapsa espacios: así un campo escrito solo
        // con la barra espaciadora queda como cadena vacía y no pasa.
        $nombre    = limpiar($body['nombre'] ?? '');
        $direccion = limpiar($body['direccion'] ?? '');
        $comuna    = limpiar($body['comuna'] ?? '');
        $barrio    = limpiar($body['barrio'] ?? '');
        $latitud   = $body['latitud'] ?? null;
        $longitud  = $body['longitud'] ?? null;

        foreach([
            validarTexto($nombre, 'Nombre', 3, 100),
            validarTexto($direccion, 'Dirección', 5, 200),
            validarTextoOpcional($comuna, 'Comuna', 60),
            validarTextoOpcional($barrio, 'Barrio', 60),
        ] as $error){
            if($error !== null){
                jsonResponse(['ok' => false, 'message' => $error], 422);
            }
        }
        // Comuna y barrio ahora salen de un select: se comprueba que el
        // barrio elegido realmente pertenezca a la comuna elegida.
        if($comuna !== '' && $barrio !== '' && !$obj->barrioPerteneceAComuna($barrio, $comuna)){
            jsonResponse(['ok' => false, 'message' => 'El barrio seleccionado no pertenece a esa comuna.'], 422);
        }

        // latitud/longitud son NOT NULL en la tabla: si el formulario
        // las deja vacías se guardan en 0.
        $latitud  = is_numeric($latitud)  ? (float) $latitud  : 0;
        $longitud = is_numeric($longitud) ? (float) $longitud : 0;

        return [
            'nombre'           => $nombre,
            'direccion'        => $direccion,
            'comuna'           => ($comuna !== '' ? $comuna : null),
            'barrio'           => ($barrio !== '' ? $barrio : null),
            'latitud'          => $latitud,
            'longitud'         => $longitud,
        ];
    }
}

?>
