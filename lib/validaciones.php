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

?>
