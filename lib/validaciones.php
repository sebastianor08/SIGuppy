<?php

    function limpiar($valor){
        $texto = (string) $valor;
        $texto = str_replace("\xC2\xA0", ' ', $texto); // NBSP en UTF-8
        $texto = preg_replace('/[\t\n\r\x0B\x0C]/u', ' ', $texto);
        return trim(preg_replace('/\s+/u', ' ', $texto));
    }

    function esVacio($valor){
        return limpiar($valor) === '';
    }


    function tieneContenido($valor){
        return preg_match('/[\p{L}\p{N}]/u', (string) $valor) === 1;
    }


    function validarTexto($valor, $etiqueta, $min = 3, $max = 200){
        $limpio = limpiar($valor);

        if($limpio === ''){
            return "El campo \"$etiqueta\" es obligatorio y no puede quedar en blanco.";
        }
        if(!tieneContenido($limpio)){
            return "El campo \"$etiqueta\" debe contener letras o números.";
        }
        if(mb_strlen($limpio) < $min){
            return "El campo \"$etiqueta\" debe tener al menos $min caracteres.";
        }
        if(mb_strlen($limpio) > $max){
            return "El campo \"$etiqueta\" no puede superar $max caracteres.";
        }
        return null;
    }


    function validarTextoOpcional($valor, $etiqueta, $max = 200){
        if(esVacio($valor)){
            return null;
        }
        return validarTexto($valor, $etiqueta, 2, $max);
    }

    function validarNombrePropio($valor, $etiqueta, $min = 2, $max = 50){
        $limpio = limpiar($valor);

        if($limpio === ''){
            return "El campo \"$etiqueta\" es obligatorio y no puede quedar en blanco.";
        }
        if(preg_match('/[0-9]/u', $limpio)){
            return "El campo \"$etiqueta\" no puede contener números.";
        }
        if(!preg_match('/^[\p{L}\p{M}\'-]+(?: [\p{L}\p{M}\'-]+)*$/u', $limpio)){
            return "El campo \"$etiqueta\" solo puede contener letras (sin números ni símbolos, aparte de apóstrofe o guion).";
        }
        if(mb_strlen($limpio) < $min){
            return "El campo \"$etiqueta\" debe tener al menos $min caracteres.";
        }
        if(mb_strlen($limpio) > $max){
            return "El campo \"$etiqueta\" no puede superar $max caracteres.";
        }
        return null;
    }

    // Entero obligatorio mayor o igual que $min
    function validarEntero($valor, $etiqueta, $min = 0){
        if($valor === '' || $valor === null){
            return "El campo \"$etiqueta\" es obligatorio.";
        }
        $numero = filter_var($valor, FILTER_VALIDATE_INT);
        if($numero === false){
            return "El campo \"$etiqueta\" debe ser un número entero.";
        }
        if($numero < $min){
            return "El campo \"$etiqueta\" no puede ser menor que $min.";
        }
        return null;
    }

    function validarDocumento($valor, $etiqueta = 'Número de documento', $min = 5, $max = 20){
        $limpio = str_replace(' ', '', trim((string) $valor));

        if($limpio === ''){
            return "El campo \"$etiqueta\" es obligatorio.";
        }
        if(!preg_match('/^[A-Za-z0-9]+$/', $limpio)){
            return "El campo \"$etiqueta\" solo puede contener letras y números, sin espacios ni signos.";
        }
        if(mb_strlen($limpio) < $min){
            return "El campo \"$etiqueta\" debe tener al menos $min caracteres.";
        }
        if(mb_strlen($limpio) > $max){
            return "El campo \"$etiqueta\" no puede superar $max caracteres.";
        }
        return null;
    }

    function normalizarDocumento($valor){
        return mb_strtoupper(str_replace(' ', '', trim((string) $valor)));
    }

    define('REGLAS_DOCUMENTO_POR_TIPO', [
        'Cédula de Ciudadanía'            => ['min' => 6,  'max' => 10, 'solo_numeros' => true],
        'Tarjeta de Identidad'            => ['min' => 10, 'max' => 11, 'solo_numeros' => false],
        'Cédula de Extranjería'           => ['min' => 6,  'max' => 15, 'solo_numeros' => false],
        'Pasaporte'                       => ['min' => 6,  'max' => 15, 'solo_numeros' => false],
        'Permiso por Protección Temporal' => ['min' => 5,  'max' => 10, 'solo_numeros' => false],
    ]);


    function reglasDocumentoPorTipo($nombreTipoDocumento){
        foreach (REGLAS_DOCUMENTO_POR_TIPO as $nombre => $regla) {
            if (mb_strtolower($nombre) === mb_strtolower(trim((string) $nombreTipoDocumento))) {
                return $regla;
            }
        }
        return ['min' => 5, 'max' => 20, 'solo_numeros' => false];
    }

    // Validación de documento que depende del tipo seleccionado (a
    // diferencia de validarDocumento(), que usa siempre el mismo min/max
    // para cualquier tipo).
    function validarDocumentoPorTipo($valor, $nombreTipoDocumento, $etiqueta = 'Número de documento'){
        $limpio = normalizarDocumento($valor);
        $regla  = reglasDocumentoPorTipo($nombreTipoDocumento);

        if($limpio === ''){
            return "El campo \"$etiqueta\" es obligatorio.";
        }

        $patron = $regla['solo_numeros'] ? '/^[0-9]+$/' : '/^[A-Z0-9]+$/';
        if(!preg_match($patron, $limpio)){
            return $regla['solo_numeros']
                ? "Para $nombreTipoDocumento, el campo \"$etiqueta\" solo puede contener números."
                : "El campo \"$etiqueta\" solo puede contener letras y números, sin espacios ni signos.";
        }
        if(mb_strlen($limpio) < $regla['min']){
            return "Para $nombreTipoDocumento, el campo \"$etiqueta\" debe tener al menos {$regla['min']} caracteres.";
        }
        if(mb_strlen($limpio) > $regla['max']){
            return "Para $nombreTipoDocumento, el campo \"$etiqueta\" no puede superar {$regla['max']} caracteres.";
        }
        return null;
    }

    // Texto de ayuda para mostrar bajo el campo (se actualiza en el
    // formulario cada vez que cambia el tipo de documento seleccionado).
    function reglasDocumentoTexto($nombreTipoDocumento){
        $regla = reglasDocumentoPorTipo($nombreTipoDocumento);
        $tipoCaracteres = $regla['solo_numeros'] ? 'solo números' : 'letras y/o números';
        if ($regla['min'] === $regla['max']) {
            return "Debe tener exactamente {$regla['min']} caracteres ($tipoCaracteres).";
        }
        return "Debe tener entre {$regla['min']} y {$regla['max']} caracteres ($tipoCaracteres).";
    }

    define('PASSWORD_MIN_LONGITUD', 8);

    function validarContrasena($valor, $etiqueta = 'Contraseña'){
        $clave = (string) $valor;

        if($clave === ''){
            return "El campo \"$etiqueta\" es obligatorio.";
        }
        if(mb_strlen($clave) < PASSWORD_MIN_LONGITUD){
            return "La contraseña debe tener al menos " . PASSWORD_MIN_LONGITUD . " caracteres.";
        }
        if(!preg_match('/[a-záéíóúñü]/u', $clave)){
            return "La contraseña debe incluir al menos una letra minúscula.";
        }
        if(!preg_match('/[A-ZÁÉÍÓÚÑÜ]/u', $clave)){
            return "La contraseña debe incluir al menos una letra mayúscula.";
        }
        if(!preg_match('/[^\p{L}\p{N}\s]/u', $clave)){
            return "La contraseña debe incluir al menos un carácter especial (por ejemplo: ! @ # $ % & * ?).";
        }
        if(preg_match('/\s/u', $clave)){
            return "La contraseña no puede contener espacios.";
        }

        return null;
    }


    function reglasContrasenaTexto(){
        return 'Mínimo ' . PASSWORD_MIN_LONGITUD . ' caracteres, con al menos una minúscula, una mayúscula y un carácter especial.';
    }

    define('DOMINIOS_CORREO_PERMITIDOS', ['cali.gov.co', 'gmail.com']);

    function normalizarCorreo($valor){
        $texto = str_replace("\xC2\xA0", ' ', (string) $valor);
        return mb_strtolower(trim($texto));
    }

    function validarCorreo($valor, $etiqueta = 'Correo electrónico', $max = 120){
        $correo = normalizarCorreo($valor);

        if($correo === ''){
            return "El campo \"$etiqueta\" es obligatorio.";
        }
        if(preg_match('/\s/u', $correo)){
            return "El campo \"$etiqueta\" no puede contener espacios.";
        }
        if(mb_strlen($correo) > $max){
            return "El campo \"$etiqueta\" no puede superar $max caracteres.";
        }

        $patronEstricto = '/^[a-z0-9]+(?:[._-][a-z0-9]+)*@[a-z0-9]+(?:[.-][a-z0-9]+)*\.[a-z]{2,}$/';
        if(!preg_match($patronEstricto, $correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)){
            return "El campo \"$etiqueta\" no es válido. Solo se permiten letras, números, puntos, guiones y guion bajo antes de la @.";
        }

        $dominio = substr(strrchr($correo, '@'), 1);
        if(!in_array($dominio, DOMINIOS_CORREO_PERMITIDOS, true)){
            return 'El correo debe ser de uno de estos dominios: ' . implode(', ', DOMINIOS_CORREO_PERMITIDOS) . '.';
        }

        return null;
    }

    // Texto de ayuda para mostrar en pantalla (se usa en el formulario)
    function reglasCorreoTexto(){
        return 'Solo se aceptan correos ' . implode(' o ', DOMINIOS_CORREO_PERMITIDOS) . '.';
    }

?>