<?php

// ============================================================
// Punto de entrada "estilo MVCLenin": páginas completas (HTML),
// sin AJAX y sin JSON. Se llama así:
//
//   Web/mvc.php?modulo=Sitio&controlador=Sitio&funcion=list
//
// Usa exactamente la misma función resolve() que ya existía en
// lib/helpers.php (la que arma el nombre de la clase Controller
// a partir de $_GET y llama al método pedido). Por eso este
// archivo es muy corto: toda la lógica de enrutamiento ya estaba
// escrita, solo hacía falta un punto de entrada que no forzara
// JSON como lo hace ajax.php.
//
// Módulos que hoy se sirven por aquí: Sitio, TerritorioPriorizado
// y Usuarios (Gestión de Usuarios). El resto del sistema
// (Zoocriaderos, Depósitos, Reportes, Roles, etc.) sigue
// funcionando exactamente igual que antes, por sus propias
// vistas en View/ y su propio ajax.php.
// ============================================================

include_once '../lib/helpers.php';

if (isset($_GET['modulo'])) {
    resolve();
} else {
    redirect('index.php');
}

?>
