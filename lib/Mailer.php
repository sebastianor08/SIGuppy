<?php

    // ============================================================
    // Envío de correos con PHPMailer (SMTP).
    //
    // Requisito: haber instalado PHPMailer con Composer, corriendo
    // en la raíz del proyecto (donde vas a crear la carpeta vendor/):
    //
    //     composer require phpmailer/phpmailer
    //
    // Eso crea vendor/autoload.php, que es lo que carga esta línea.
    // Si no tienes Composer, revisa la nota al final de este archivo.
    // ============================================================

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

    // Correo específico del código de verificación de login (2FA por correo).
    function enviarCodigoVerificacion($destinatario, $nombreDestinatario, $codigo)
    {
        $asunto = "Tu código de verificación - SIGuppys";

        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto;'>
                <h2 style='color:#2f7dfa; margin-bottom:4px;'>Verificación de inicio de sesión</h2>
                <p>Hola <strong>" . htmlspecialchars($nombreDestinatario) . "</strong>,</p>
                <p>Usa el siguiente código para confirmar tu identidad. Vence en 10 minutos:</p>
                <p style='font-size:28px; font-weight:bold; letter-spacing:6px; background:#f1f3f8;
                          padding:14px 20px; border-radius:8px; text-align:center; color:#2f7dfa;'>
                    " . htmlspecialchars($codigo) . "
                </p>
                <p style='color:#888; font-size:13px;'>
                    Si no intentaste iniciar sesión en SIGuppys, ignora este correo y
                    considera cambiar tu contraseña.
                </p>
            </div>
        ";

        return enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpo);
    }

    // ------------------------------------------------------------
    // ¿No tienes Composer? Alternativa manual (menos recomendable):
    //   1. Descarga el .zip de https://github.com/PHPMailer/PHPMailer
    //      (botón verde "Code" -> "Download ZIP").
    //   2. Copia la carpeta "src" del zip dentro de tu proyecto, por
    //      ejemplo en:  lib/PHPMailer/
    //   3. Cambia el require_once de arriba por estos tres:
    //        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    //        require_once __DIR__ . '/PHPMailer/SMTP.php';
    //        require_once __DIR__ . '/PHPMailer/Exception.php';
    //      y quita el "require_once vendor/autoload.php".
    // ------------------------------------------------------------

?>
