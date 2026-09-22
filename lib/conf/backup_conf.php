<?php

// Zona horaria de los respaldos. La página web ya la fija en
// lib/helpers.php, pero la tarea programada (cron/backup_automatico.php)
// corre por PHP CLI, que NO carga helpers.php y usa la zona del php.ini
// (normalmente UTC). Por eso los .sql automáticos quedaban con otra hora
// en el nombre. Al fijarla aquí, web y automático usan la misma.
if (!defined('BACKUP_TIMEZONE')) {
    define('BACKUP_TIMEZONE', 'America/Bogota');
}
date_default_timezone_set(BACKUP_TIMEZONE);

if (!defined('BACKUP_DIR')) {
    define('BACKUP_DIR', __DIR__ . '/../../backups/');
}


if (!defined('PG_BIN_DIR')) {
    define('PG_BIN_DIR', 'C:\\Program Files\\PostgreSQL\\18\\bin\\');
}


if (!defined('BACKUP_CLOUD_DIR')) {
    define('BACKUP_CLOUD_DIR', 'C:\\Users\\Hewlett-Packard\\OneDrive\\Escritorio\\Respaldo_BD\\');
}


if (!defined('BACKUP_RETENTION_DIAS')) {
    define('BACKUP_RETENTION_DIAS', 30);
}