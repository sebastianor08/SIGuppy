<?php

    // ============================================================
    // Configuración del correo saliente (SMTP) que usa lib/Mailer.php
    // para avisarle a un usuario nuevo cuál es su contraseña temporal.
    //
    // Completa estos datos con una cuenta real antes de usar el envío
    // de correos en producción. Con Gmail, por ejemplo:
    //   - mail_host: smtp.gmail.com
    //   - mail_port: 587
    //   - mail_user: tu_correo@gmail.com
    //   - mail_pass: una "contraseña de aplicación" (no la contraseña normal
    //                de la cuenta; Gmail exige generarla aparte)
    // ============================================================

    $mail_host   = "smtp.gmail.com";
    $mail_port   = 587;
    $mail_user   = "correo@dominio.com";      // <-- pon aquí la cuenta que enviará los correos
    $mail_pass   = "clave-de-aplicacion";      // <-- pon aquí su contraseña de aplicación
    $mail_from   = "correo@dominio.com";       // normalmente igual a mail_user
    $mail_from_nombre = "SIGuppys";

?>
