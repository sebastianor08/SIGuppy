<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>SIGuppys - Control Biológico contra el Dengue</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/logo_sistema_SIGuppy.svg"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files (del template, sin modificar) -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <!-- Estilos propios de SIGuppys: solo se AGREGAN reglas encima del kaiadmin.css original -->
    <style>
      /* ---- Marca / logo ---- */
      .siguppys-logo-header {
        padding-left: 20px;
        padding-right: 15px;
        height: 78px;
        border-bottom: 1px solid #f1f1f1;
      }
      .siguppys-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        color: inherit;
      }
      .siguppys-logo img {
        height: 34px;
        width: auto;
      }
      .siguppys-brand {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
      }
      .siguppys-brand strong {
        font-size: 15px;
        color: #2a2f5b;
        font-weight: 700;
      }
      .siguppys-brand small {
        font-size: 10.5px;
        color: #8a8d93;
        font-weight: 500;
        white-space: normal;
      }

      /* ---- Sidebar en columna: menú scrollable + pie fijo con Cerrar Sesión ---- */
      .siguppys-sidebar {
        display: flex;
        flex-direction: column;
      }
      .siguppys-sidebar .sidebar-logo {
        flex: none;
      }
      .siguppys-sidebar .sidebar-wrapper {
        flex: 1 1 auto;
        max-height: none;
      }
      .siguppys-sidebar .nav-section .text-section {
        font-size: 11px;
      }

      /* ---- Estado activo estilo "pastilla" ----
         Se usa !important porque kaiadmin.css trae, para .sidebar-style-2
         + .nav-secondary, un relleno sólido de color con más especificidad;
         aquí lo reemplazamos por un tono suave. */
      .siguppys-sidebar.sidebar.sidebar-style-2 .nav.nav-secondary > .nav-item.active > a,
      .siguppys-sidebar .nav > .nav-item > a[aria-expanded="true"] {
        background: rgba(29, 122, 243, 0.08) !important;
        box-shadow: none !important;
        border-radius: 10px;
      }
      .siguppys-sidebar.sidebar.sidebar-style-2 .nav.nav-secondary > .nav-item.active > a:before {
        background: transparent !important;
      }
      .siguppys-sidebar.sidebar.sidebar-style-2 .nav.nav-secondary > .nav-item.active > a p,
      .siguppys-sidebar.sidebar.sidebar-style-2 .nav.nav-secondary > .nav-item.active > a i,
      .siguppys-sidebar.sidebar.sidebar-style-2 .nav.nav-secondary > .nav-item.active > a .caret,
      .siguppys-sidebar.sidebar.sidebar-style-2 .nav.nav-secondary > .nav-item.active > a span {
        color: #1d7af3 !important;
      }
      .siguppys-sidebar .nav-collapse li.active > a {
        background: rgba(29, 122, 243, 0.1) !important;
        border-radius: 8px;
      }
      .siguppys-sidebar .nav-collapse li.active > a .sub-item {
        color: #1d7af3 !important;
        font-weight: 600;
        opacity: 1;
      }
      .siguppys-sidebar .nav-collapse li.active > a .sub-item:before {
        background: #1d7af3;
      }

      /* ---- Pie del sidebar: botón Cerrar Sesión ---- */
      .sidebar-footer {
        flex: none;
        padding: 16px 20px 22px;
        border-top: 1px solid #f1f1f1;
      }
      .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #f3545d;
        border-radius: 8px;
        color: #f3545d;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
      }
      .btn-logout:hover {
        background: #f3545d;
        color: #fff;
        text-decoration: none;
      }

      /* ---- Ítems del menú aún no implementados ---- */
      .nav-item.disabled > a {
        cursor: not-allowed;
        opacity: 0.55;
      }

      /* ---- Sin navbar: el contenido ya no necesita el margen superior
         que kaiadmin.css reserva para el encabezado fijo ---- */
      .main-panel > .container {
        margin-top: 0;
      }
    </style>
</head>
