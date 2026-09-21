<?php

    require_once __DIR__ . '/../vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Envío genérico. Devuelve true/false; nunca lanza excepción hacia
    // afuera (los controladores no necesitan try/catch para esto).
    //
    // $cuerpoTexto (opcional) es la versión en texto plano. Si no se envía, se
    // deriva del HTML con strip_tags (suficiente para mensajes sin caracteres
    // especiales, como el código de recuperación).
    function enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpoHtml, $cuerpoTexto = null)
    {
        require __DIR__ . '/conf/mail.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $mailHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailUsername;
            $mail->Password   = $mailPassword;
            $mail->SMTPSecure = $mailEncryption;
            $mail->Port       = $mailPort;
            // Por defecto PHPMailer espera hasta 300 s si Gmail no responde (sin
            // internet, firewall...): la petición del usuario se quedaría colgada.
            $mail->Timeout    = 15;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($mailUsername, $mailFromName);
            $mail->addAddress($destinatario, $nombreDestinatario);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;
            $mail->AltBody = ($cuerpoTexto !== null) ? $cuerpoTexto : strip_tags($cuerpoHtml);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo (PHPMailer): " . $mail->ErrorInfo);
            return false;
        }
    }

    // Correo específico del código para "¿olvidaste tu contraseña?".
    function enviarCodigoRecuperacion($destinatario, $nombreDestinatario, $codigo)
    {
        $asunto = "Recupera tu contraseña - SIGuppys";

        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto;'>
                <h2 style='color:#2f7dfa; margin-bottom:4px;'>Recuperación de contraseña</h2>
                <p>Hola <strong>" . htmlspecialchars($nombreDestinatario) . "</strong>,</p>
                <p>Recibimos una solicitud para restablecer tu contraseña. Usa este código,
                   vence en 15 minutos:</p>
                <p style='font-size:28px; font-weight:bold; letter-spacing:6px; background:#f1f3f8;
                          padding:14px 20px; border-radius:8px; text-align:center; color:#2f7dfa;'>
                    " . htmlspecialchars($codigo) . "
                </p>
                <p style='color:#888; font-size:13px;'>
                    Si no fuiste tú quien solicitó este cambio, ignora este correo:
                    tu contraseña actual seguirá funcionando.
                </p>
            </div>
        ";

        return enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpo);
    }

    // Correo con las credenciales de un usuario recién creado. El "usuario" para
    // iniciar sesión es el correo. Devuelve true/false como enviarCorreo().
    function enviarCredencialesUsuario($destinatario, $nombreDestinatario, $usuario, $contrasena)
    {
        $asunto = "Tus credenciales de acceso - SIGuppys";

        // Todo lo que se inserta en el HTML va escapado: la contraseña puede
        // llevar símbolos como < > & que romperían el mensaje (o se interpretarían).
        $nombreH     = htmlspecialchars($nombreDestinatario, ENT_QUOTES, 'UTF-8');
        $usuarioH    = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
        $contrasenaH = htmlspecialchars($contrasena, ENT_QUOTES, 'UTF-8');

        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto;'>
                <h2 style='color:#2f7dfa; margin-bottom:4px;'>Bienvenido a SIGuppys</h2>
                <p>Hola <strong>$nombreH</strong>,</p>
                <p>Se creó tu cuenta en el sistema. Estas son tus credenciales de acceso:</p>
                <table style='width:100%; border-collapse:collapse; background:#f1f3f8; border-radius:8px;'>
                    <tr>
                        <td style='padding:12px 16px; color:#666; width:110px;'>Usuario</td>
                        <td style='padding:12px 16px; font-weight:bold;'>$usuarioH</td>
                    </tr>
                    <tr>
                        <td style='padding:12px 16px; color:#666;'>Contraseña</td>
                        <td style='padding:12px 16px; font-family:Consolas, monospace; font-weight:bold;'>$contrasenaH</td>
                    </tr>
                </table>
                <p style='color:#888; font-size:13px;'>
                    Por seguridad, no compartas este correo. Si quieres cambiar tu contraseña,
                    usa la opción &quot;¿Olvidaste tu contraseña?&quot; en la pantalla de inicio de sesión.
                </p>
            </div>
        ";

        // Versión en texto plano SIN escapar, para que la contraseña salga exacta.
        $texto = "Bienvenido a SIGuppys\n\n"
            . "Hola $nombreDestinatario,\n"
            . "Se creó tu cuenta en el sistema. Tus credenciales de acceso son:\n\n"
            . "Usuario: $usuario\n"
            . "Contraseña: $contrasena\n\n"
            . "Por seguridad, no compartas este correo. Si quieres cambiar tu contraseña, "
            . "usa la opción \"¿Olvidaste tu contraseña?\" en la pantalla de inicio de sesión.";

        return enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpo, $texto);
    }

?>