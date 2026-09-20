<?php
$basePath  = '../../';
$pageTitle = 'Acerca de';
$bodyPage  = 'acerca-de';

// Tarjetas de "¿Qué permite SIGuppy?": ícono, título, texto y a dónde lleva la flecha.
$funcionalidades = [
    [
        'icono'  => 'fa-fish',
        'titulo' => 'Zoocriaderos',
        'texto'  => 'Consulta y gestión de la información de los zoocriaderos registrados.',
        'enlace' => 'View/Zoocriadero/zoocriaderos.php',
    ],
    [
        'icono'  => 'fa-swimming-pool',
        'titulo' => 'Tanques',
        'texto'  => 'Registro y seguimiento de los tanques utilizados para la cría de peces guppy.',
        'enlace' => 'View/Tanque/tanques.php',
    ],
    [
        'icono'  => 'fa-map-marker-alt',
        'titulo' => 'Sitios de terreno',
        'texto'  => 'Organización de los sitios identificados y la información relacionada con ellos.',
        'enlace' => 'View/Deposito/DepositoView.php',
    ],
    [
        'icono'  => 'fa-chart-bar',
        'titulo' => 'Reportes',
        'texto'  => 'Generación de reportes filtrables y gráficos para facilitar el análisis de la información.',
        'enlace' => 'View/Reportes/SeguimientoDeActividadesView.php',
    ],
];

// Integrantes del equipo y color de su avatar (en el orden del diseño).
$integrantes = [
    ['nombre' => 'Andrea Rivera Quino',                 'color' => 'cyan'],
    ['nombre' => 'Juan Sebastian Olano Reginfo',        'color' => 'azul'],
    ['nombre' => 'Lenin David Idarraga Montaño',        'color' => 'morado'],
    ['nombre' => 'Jaider Alexis Montaño Mondragon',     'color' => 'cyan'],
    ['nombre' => 'Lilliannys Fabiana Baptista Paolini', 'color' => 'morado'],
];

include '../partials/head.php';
?>
<div class="wrapper">
    <?php $rutaBase = '../../'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <div class="main-panel">
        <?php include '../partials/topbar.php'; ?>
        <div class="container">
            <div class="page-inner">

                <!-- Encabezado de la página -->
                <div class="acd-card acd-encabezado mb-3">
                    <span class="acd-icono">
                        <i class="fas fa-fish"></i>
                    </span>
                    <div>
                        <h3 class="fw-bold mb-1 acd-titulo">Acerca de SIGuppy</h3>
                        <h6 class="op-7 mb-0 acd-texto">Sistema de gestión y seguimiento para zoocriaderos de peces guppy</h6>
                    </div>
                </div>

                <!-- ¿Qué es SIGuppy? + Nuestro propósito -->
                <div class="row g-3 mb-3">
                    <div class="col-lg-8">
                        <div class="acd-card acd-que-es h-100">
                            <div class="acd-que-es-texto">
                                <div class="acd-seccion-titulo">
                                    <span class="acd-icono">
                                        <i class="fas fa-fish"></i>
                                    </span>
                                    <h5 class="fw-bold mb-0 acd-titulo">¿Qué es SIGuppy?</h5>
                                </div>
                                <p class="acd-texto mb-0">SIGuppy es una plataforma diseñada para facilitar la gestión, seguimiento y control de la producción de peces guppy en zoocriaderos. El sistema permite organizar información relacionada con zoocriaderos, tanques, sitios de terreno y actividades realizadas, facilitando el registro y consulta de los datos necesarios para el seguimiento de los procesos.</p>
                            </div>
                            <?php // Para cambiar la ilustración, cambia la ruta de la imagen 
                            ?>
                            <div class="acd-ilustracion">
                                <img src="<?= $basePath ?>Web/assets/img/siguppys/logo-pin.png" alt="SIGuppy">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="acd-card h-100">
                            <div class="acd-seccion-titulo">
                                <span class="acd-icono">
                                    <i class="fas fa-bullseye"></i>
                                </span>
                                <h5 class="fw-bold mb-0 acd-titulo">Nuestro propósito</h5>
                            </div>
                            <p class="acd-texto mb-0">Facilitar la administración de la información de los zoocriaderos de peces guppy mediante una herramienta organizada, sencilla y accesible que permita realizar un mejor seguimiento de las actividades y recursos registrados.</p>
                        </div>
                    </div>
                </div>

                <!-- ¿Qué permite SIGuppy? -->
                <div class="acd-card mb-3">
                    <div class="acd-seccion-titulo">
                        <span class="acd-icono">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <h5 class="fw-bold mb-0 acd-titulo">¿Qué permite SIGuppy?</h5>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($funcionalidades as $i => $funcion): ?>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="acd-funcion">
                                    <span class="acd-icono <?= $i === 3 ? 'acd-icono--morado' : '' ?>">
                                        <i class="fas <?= $funcion['icono'] ?>"></i>
                                    </span>
                                    <h6 class="fw-bold mt-3 mb-2 acd-titulo"><?= $funcion['titulo'] ?></h6>
                                    <p class="acd-texto small mb-3"><?= $funcion['texto'] ?></p>
                                    <a href="<?= $rutaBase . $funcion['enlace'] ?>"
                                        class="acd-flecha stretched-link"
                                        aria-label="Ir a <?= $funcion['titulo'] ?>">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Nuestro equipo -->
                <div class="acd-card mb-3">
                    <div class="acd-seccion-titulo mb-1">
                        <span class="acd-icono">
                            <i class="fas fa-users"></i>
                        </span>
                        <h5 class="fw-bold mb-0 acd-titulo">Nuestro equipo</h5>
                    </div>
                    <p class="acd-texto small mb-3">SIGuppy fue desarrollado como un proyecto colaborativo, integrando diferentes conocimientos y aportes para construir una herramienta orientada a la gestión de zoocriaderos de peces guppy.</p>
                    <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5">
                        <?php foreach ($integrantes as $integrante): ?>
                            <div class="col">
                                <div class="acd-miembro">
                                    <span class="acd-avatar acd-avatar--<?= $integrante['color'] ?>">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <div class="acd-miembro-nombre"><?= $integrante['nombre'] ?></div>
                                    <div class="acd-miembro-rol">Integrante del proyecto</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <style>
                    .acd-card {
                        background-color: #ffffff;
                        border-radius: 14px;
                        padding: 22px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                    }

                    .acd-encabezado {
                        display: flex;
                        align-items: center;
                        gap: 14px;
                    }

                    .acd-titulo {
                        color: #2a2f5b;
                    }

                    .acd-texto {
                        color: #555555;
                        line-height: 1.65;
                    }

                    .acd-seccion-titulo {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        margin-bottom: 14px;
                    }

                    .acd-icono {
                        width: 44px;
                        height: 44px;
                        border-radius: 12px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                        font-size: 18px;
                        background-color: #dff4fb;
                        color: #2ab6d9;
                    }

                    .acd-icono--morado {
                        background-color: #efe9fd;
                        color: #6c5ce7;
                    }

                    /* ¿Qué es SIGuppy?: texto a la izquierda, ilustración a la derecha */
                    .acd-que-es {
                        display: flex;
                        align-items: center;
                        gap: 22px;
                    }

                    .acd-que-es-texto {
                        flex: 1;
                    }

                    .acd-ilustracion {
                        flex: 0 0 240px;
                        min-height: 170px;
                        border-radius: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background:
                            radial-gradient(circle at 20% 25%, rgba(255, 255, 255, 0.55) 0 8px, transparent 9px),
                            radial-gradient(circle at 78% 68%, rgba(255, 255, 255, 0.4) 0 12px, transparent 13px),
                            linear-gradient(135deg, #c8f0ff 0%, #5cc9f2 100%);
                    }

                    .acd-ilustracion img {
                        max-height: 130px;
                        max-width: 80%;
                        object-fit: contain;
                    }

                    /* ¿Qué permite SIGuppy?: tarjetas con flecha */
                    .acd-funcion {
                        position: relative;
                        height: 100%;
                        border: 1px solid #e6ecf5;
                        border-radius: 12px;
                        padding: 18px;
                        display: flex;
                        flex-direction: column;
                        align-items: flex-start;
                        transition: box-shadow 0.2s ease, transform 0.2s ease;
                    }

                    .acd-funcion:hover {
                        box-shadow: 0 6px 16px rgba(42, 182, 217, 0.18);
                        transform: translateY(-2px);
                    }

                    .acd-flecha {
                        margin-top: auto;
                        align-self: flex-end;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 12px;
                        background-color: #dff4fb;
                        color: #2ab6d9;
                        text-decoration: none;
                    }

                    /* Nuestro equipo */
                    .acd-miembro {
                        height: 100%;
                        border: 1px solid #e6ecf5;
                        border-radius: 12px;
                        padding: 18px 12px;
                        text-align: center;
                    }

                    .acd-avatar {
                        width: 54px;
                        height: 54px;
                        border-radius: 50%;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 22px;
                        color: #ffffff;
                        margin-bottom: 10px;
                    }

                    .acd-avatar--cyan {
                        background: linear-gradient(135deg, #6ee0f5, #2ab6d9);
                    }

                    .acd-avatar--azul {
                        background: linear-gradient(135deg, #5aa8ff, #2f7dfa);
                    }

                    .acd-avatar--morado {
                        background: linear-gradient(135deg, #9b8cf0, #6c5ce7);
                    }

                    .acd-miembro-nombre {
                        font-size: 13px;
                        font-weight: 700;
                        color: #2a2f5b;
                    }

                    .acd-miembro-rol {
                        font-size: 11.5px;
                        color: #8a8d93;
                        margin-top: 2px;
                    }

                    /* Modo oscuro */
                    body[data-background-color="dark"] .acd-card {
                        background-color: #202940;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
                    }

                    body[data-background-color="dark"] .acd-titulo,
                    body[data-background-color="dark"] .acd-miembro-nombre {
                        color: #ffffff;
                    }

                    body[data-background-color="dark"] .acd-texto,
                    body[data-background-color="dark"] .acd-miembro-rol {
                        color: #a9afbb;
                    }

                    body[data-background-color="dark"] .acd-icono {
                        background-color: rgba(42, 182, 217, 0.18);
                    }

                    body[data-background-color="dark"] .acd-icono--morado {
                        background-color: rgba(108, 92, 231, 0.22);
                    }

                    body[data-background-color="dark"] .acd-funcion,
                    body[data-background-color="dark"] .acd-miembro {
                        border-color: rgba(255, 255, 255, 0.1);
                        background-color: rgba(255, 255, 255, 0.03);
                    }

                    body[data-background-color="dark"] .acd-flecha {
                        background-color: rgba(42, 182, 217, 0.18);
                    }

                    body[data-background-color="dark"] .acd-ilustracion {
                        background:
                            radial-gradient(circle at 20% 25%, rgba(255, 255, 255, 0.18) 0 8px, transparent 9px),
                            radial-gradient(circle at 78% 68%, rgba(255, 255, 255, 0.12) 0 12px, transparent 13px),
                            linear-gradient(135deg, #1d4f6b 0%, #1f7fa8 100%);
                    }

                    /* Pantallas pequeñas: la ilustración pasa debajo del texto */
                    @media (max-width: 768px) {
                        .acd-que-es {
                            flex-direction: column;
                            align-items: stretch;
                        }

                        .acd-ilustracion {
                            flex-basis: auto;
                            width: 100%;
                        }
                    }
                </style>

            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = [];
include '../partials/footer.php';
?>
</body>

</html>