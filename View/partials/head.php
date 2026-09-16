<?php
    $basePath  = $basePath  ?? '../../';
    $pageTitle = $pageTitle ?? 'SIGuppys';
    $bodyPage  = $bodyPage  ?? '';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> · SIGuppys</title>

    <link rel="icon" href="<?php echo $basePath; ?>assets/img/siguppys/favicon-32.png" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo $basePath; ?>assets/img/siguppys/favicon-180.png">

    <!-- Fuentes e iconos -->
    <script src="<?php echo $basePath; ?>assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons"
          ],
          urls: ["<?php echo $basePath; ?>assets/css/fonts.min.css"]
        },
        active: function () { sessionStorage.fonts = true; }
      });
    </script>

    <!-- CSS del template (mismos archivos en todas las vistas) -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/plugins.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/kaiadmin.min.css">

    <!-- Estilos propios de SIGuppys: solo AGREGAN reglas encima del kaiadmin.css original -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/siguppys.css">
    <?php if(!empty($extraStyles)): ?>
    <style>
<?php echo $extraStyles; ?>
    </style>
    <?php endif; ?>
</head>
<body data-page="<?php echo htmlspecialchars($bodyPage, ENT_QUOTES, 'UTF-8'); ?>">
