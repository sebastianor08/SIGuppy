<?php
    // ============================================================
    // Partial: scripts de cierre (core JS + JS propios de la vista)
    // Variables esperadas:
    //   $basePath     -> ver head.php
    //   $pageScripts  -> (opcional) array de rutas JS (relativas a $basePath)
    //                    propias de la vista, ej. ['assets/js/siguppys-zoocriaderos.js']
    // ============================================================
    $basePath    = $basePath ?? '../../';
    $pageScripts = $pageScripts ?? [];
?>
<!-- Core JS Files -->
<script src="<?php echo $basePath; ?>assets/js/core/jquery-3.7.1.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/core/popper.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/core/bootstrap.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?php echo $basePath; ?>assets/js/kaiadmin.min.js"></script>

<!-- SIGuppys: resaltado de menú activo, modo oscuro/daltonismo y selector de rol -->
<script src="<?php echo $basePath; ?>assets/js/siguppys-nav.js"></script>
<?php foreach($pageScripts as $script): ?>
<script src="<?php echo $basePath . $script; ?>"></script>
<?php endforeach; ?>
