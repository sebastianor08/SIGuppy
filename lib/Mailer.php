<?php

    require_once __DIR__ . '/../vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Envío genérico. Devuelve true/false; nunca lanza excepción hacia
    // afuera (los controladores no necesitan try/catch para esto).
    function enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpoHtml)
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
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($mailUsername, $mailFromName);
            $mail->addAddress($destinatario, $nombreDestinatario);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;
            $mail->AltBody = strip_tags($cuerpoHtml);

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

?>