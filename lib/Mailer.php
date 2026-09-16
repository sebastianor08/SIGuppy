<?php

// ============================================================
// lib/Mailer.php
// --------------
// Envoltorio sencillo sobre PHPMailer para poder escribir, en
// cualquier controlador:
//
//   $mailer = new Mailer();
//   $mailer->enviar('destino@correo.com', 'Asunto', '<p>Cuerpo en HTML</p>');
//
// PHPMailer NO viene incluido en este ZIP (es una librería externa
// de terceros). Para activarlo hay que instalarlo una sola vez con
// Composer, desde la carpeta del proyecto (SIGuppy/):
//
//   composer require phpmailer/phpmailer
//
// Eso crea la carpeta vendor/ con la librería adentro. Si esa
// carpeta todavía no existe, esta clase NO tira error fatal: el
// método enviar() devuelve false y deja el motivo en el log de PHP,
// para que el resto del sistema (por ejemplo, crear un usuario)
// siga funcionando aunque el correo no se pueda mandar todavía.
// ============================================================

class Mailer
{
    private $disponible = false;
    private $error = null;

    public function __construct()
    {
        $autoload = __DIR__ . '/../vendor/autoload.php';

        if (!file_exists($autoload)) {
            $this->error = 'PHPMailer no está instalado. Ejecuta "composer require phpmailer/phpmailer" en la carpeta del proyecto.';
            return;
        }

        require_once $autoload;

        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $this->disponible = true;
        } else {
            $this->error = 'vendor/autoload.php existe pero no se encontró la clase PHPMailer. Revisa la instalación.';
        }
    }

    // Devuelve true si el correo se pudo enviar, false si no (revisa
    // ultimoError() para saber por qué).
    public function enviar($destinoCorreo, $destinoNombre, $asunto, $cuerpoHtml)
    {
        if (!$this->disponible) {
            error_log('Mailer: ' . $this->error);
            return false;
        }

        require_once __DIR__ . '/conf/mail_conf.php';
        // $mail_host, $mail_port, $mail_user, $mail_pass, $mail_from, $mail_from_nombre

        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $phpmailer->isSMTP();
            $phpmailer->Host       = $mail_host;
            $phpmailer->Port       = $mail_port;
            $phpmailer->SMTPAuth   = true;
            $phpmailer->Username   = $mail_user;
            $phpmailer->Password   = $mail_pass;
            $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $phpmailer->CharSet    = 'UTF-8';

            $phpmailer->setFrom($mail_from, $mail_from_nombre);
            $phpmailer->addAddress($destinoCorreo, $destinoNombre);

            $phpmailer->isHTML(true);
            $phpmailer->Subject = $asunto;
            $phpmailer->Body    = $cuerpoHtml;
            $phpmailer->AltBody = strip_tags($cuerpoHtml);

            $phpmailer->send();
            return true;
        } catch (\Exception $e) {
            $this->error = $phpmailer->ErrorInfo ?: $e->getMessage();
            error_log('Mailer: no se pudo enviar el correo: ' . $this->error);
            return false;
        }
    }

    public function ultimoError()
    {
        return $this->error;
    }

    public function disponible()
    {
        return $this->disponible;
    }
}

?>
