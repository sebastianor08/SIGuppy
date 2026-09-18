<?php

    // ============================================================
    // Validaciones compartidas por los formularios del sistema.
    //
    // El problema que resuelven: un campo con la barra espaciadora
    // ("   ") NO está vacío para PHP ni para el required del HTML,
    // así que sin esto se alcanzaba a guardar un registro en blanco.
    // ============================================================

    // Quita espacios de los extremos y colapsa los espacios repetidos
    // del medio: "  Zoocriadero    Norte " -> "Zoocriadero Norte"
    function limpiar($valor){
        return trim(preg_replace('/\s+/u', ' ', (string) $valor));
    }

    // ¿Quedó vacío después de limpiar? (cubre "", "   ", tabs, saltos de línea)
    function esVacio($valor){
        return limpiar($valor) === '';
    }

    // ¿Tiene al menos una letra o un número? Evita nombres como "---" o "...."
    function tieneContenido($valor){
        return preg_match('/[\p{L}\p{N}]/u', (string) $valor) === 1;
    }

    // Validación completa de un campo de texto obligatorio.
    // Devuelve null si está bien, o el mensaje de error si no.
    //   $etiqueta: cómo se llama el campo en pantalla
    //   $min/$max: longitud permitida ya limpia
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

    // Igual que validarTexto pero para campos opcionales:
    // si viene vacío lo da por bueno, si viene con algo lo valida.
    function validarTextoOpcional($valor, $etiqueta, $max = 200){
        if(esVacio($valor)){
            return null;
        }
        return validarTexto($valor, $etiqueta, 2, $max);
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

    // ============================================================
    // Número de documento
    // ------------------------------------------------------------
    // Se admiten letras y números (el pasaporte y la cédula de
    // extranjería los usan). Sin espacios, puntos ni guiones: el
    // documento se guarda "pelado" para poder compararlo y para que
    // no se repita escrito de dos formas distintas.
    //
    // Si SOLO quieres permitir números (cédula colombiana), cambia
    // la expresión de abajo por:  '/^[0-9]+$/'
    // ============================================================
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

    // Deja el documento listo para guardar: sin espacios y en mayúscula
    // (así "abc123" y "ABC123" no se pueden registrar como dos usuarios).
    function normalizarDocumento($valor){
        return mb_strtoupper(str_replace(' ', '', trim((string) $valor)));
    }

    // ============================================================
    // Contraseña segura
    // ------------------------------------------------------------
    // Reglas: mínimo 8 caracteres, al menos una letra minúscula,
    // al menos una letra mayúscula y al menos un carácter especial.
    //
    // Si además quieres exigir un número, descomenta el bloque
    // marcado más abajo.
    // ============================================================
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

        // --- Descomenta si también quieres exigir un número ---
        // if(!preg_match('/[0-9]/', $clave)){
        //     return "La contraseña debe incluir al menos un número.";
        // }

        return null;
    }

    // Texto de ayuda para mostrar en pantalla (se usa en el formulario)
    function reglasContrasenaTexto(){
        return 'Mínimo ' . PASSWORD_MIN_LONGITUD . ' caracteres, con al menos una minúscula, una mayúscula y un carácter especial.';
    }

?>
