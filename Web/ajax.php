<?php

    // Punto de entrada para peticiones que esperan JSON (fetch/AJAX), nunca HTML.
    // Se llama así: Web/ajax.php?modulo=SeguimientoZoocriadero&controlador=SeguimientoZoocriadero&funcion=zoocriaderos
    // A diferencia de Web/index.php (que es una página estática de demostración),
    // este archivo NO agrega ningún layout alrededor de la respuesta: por eso
    // el módulo SeguimientoZoocriadero (que responde con jsonResponse()) debe
    // llamarse siempre a través de este archivo y no de Web/index.php.

    include_once '../lib/helpers.php';

    if(isset($_GET['modulo'])){
        resolve();
    }

?>
