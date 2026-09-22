<?php
    // ============================================================
    // Partial: scripts de cierre (core JS + JS propios de la vista)
    // Variables esperadas:
    //   $basePath     -> ver head.php
    //   $pageScripts  -> (opcional) array de rutas JS (relativas a $basePath)
    //                    propias de la vista, ej. ['Web/assets/js/siguppys-zoocriaderos.js']
    // ============================================================
    $basePath    = $basePath ?? '../../';
    $pageScripts = $pageScripts ?? [];
?>
<!-- Core JS Files -->
<script src="<?php echo $basePath; ?>Web/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="<?php echo $basePath; ?>Web/assets/js/core/popper.min.js"></script>
<script src="<?php echo $basePath; ?>Web/assets/js/core/bootstrap.min.js"></script>
<script src="<?php echo $basePath; ?>Web/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?php echo $basePath; ?>Web/assets/js/kaiadmin.min.js"></script>

<script>
  // basePath del lado del servidor, disponible para cualquier script de
  // esta página (inactividad, permisos, etc.) sin tener que recalcularlo.
  window.SIG_BASE_PATH = <?php echo json_encode($basePath, JSON_UNESCAPED_SLASHES); ?>;
  <?php if (!empty($moduloPermisos)): ?>
  // Permisos REALES del rol de la sesión sobre este módulo (tabla
  // rol_permiso), para que el JS del módulo deje de usar el selector de
  // rol de mentira (auxiliar/coordinador en localStorage).
  window.SIG_PERMISOS = <?php
      require_once __DIR__ . '/../../lib/permisos.php';
      echo json_encode(sigPermisosDeModulo($moduloPermisos), JSON_UNESCAPED_UNICODE);
  ?>;
  <?php endif; ?>
</script>

<!-- SIGuppys: cierre de sesión automático a los 15 minutos de inactividad -->
<script src="<?php echo $basePath; ?>Web/assets/js/siguppys-inactividad.js"></script>
<!-- SIGuppys: en cualquier formulario con fecha_inicio/fecha_fin, la fecha de inicio no puede superar la fecha fin -->
<script src="<?php echo $basePath; ?>Web/assets/js/siguppys-rango-fechas.js"></script>

<!-- SIGuppys: resaltado de menú activo, modo oscuro/daltonismo y selector de rol -->
<script src="<?php echo $basePath; ?>Web/assets/js/siguppys-nav.js"></script>
<!-- SIGuppys: el botón "Generar Reportes" (.btn-reportes) exporta a PDF con window.print() -->
<script src="<?php echo $basePath; ?>Web/assets/js/siguppys-exportar-pdf.js?v=<?php echo filemtime(__DIR__ . '/../../Web/assets/js/siguppys-exportar-pdf.js'); ?>"></script>
<script src="<?php echo $basePath; ?>Web/assets/js/siguppys-exportar-excel.js?v=<?php echo filemtime(__DIR__ . '/../../Web/assets/js/siguppys-exportar-excel.js'); ?>"></script>
<!-- SIGuppys: en los filtros de reportes, valida que Fecha Inicio no sea posterior a Fecha Fin -->
<script src="<?php echo $basePath; ?>Web/assets/js/siguppys-validar-fechas.js?v=<?php echo filemtime(__DIR__ . '/../../Web/assets/js/siguppys-validar-fechas.js'); ?>"></script>
<?php foreach($pageScripts as $script): ?>
<script src="<?php echo $basePath . $script; ?>"></script>
<?php endforeach; ?>
