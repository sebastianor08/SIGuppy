<?php
    // Punto de entrada de conveniencia: si alguien abre la carpeta del
    // proyecto sin indicar un archivo (ej. http://localhost/SIGuppy/),
    // lo mandamos directo al panel (Web/index.php).
    header("Location: Web/index.php");
    exit;
?>
