<?php

    include_once '../lib/helpers.php';
    include_once '../lib/helpersLogin.php';
    include_once '../View/partials/head.php';

    echo "<body>";
        echo "<div class='wrapper'>";

            include_once '../View/partials/navbar.php';

            echo "<div class='main-panel'>";
                echo "<div class='main-header'>";
                    echo "<div class='main-header-logo'>";
                        echo "<div class='logo-header siguppys-logo-header' data-background-color='white'>";
                            echo "<a href='Index.php' class='logo siguppys-logo'>";
                                echo "<span class='siguppys-pin'><img src='../assets/img/siguppys/logo-pin.png' alt='SIGuppys' /></span>";
                                echo "<span class='siguppys-brand'><strong>SIGuppys</strong><small>Control Biológico contra el Dengue</small></span>";
                            echo "</a>";
                            echo "<div class='nav-toggle'>";
                                echo "<button class='btn btn-toggle toggle-sidebar'><i class='gg-menu-right'></i></button>";
                                echo "<button class='btn btn-toggle sidenav-toggler'><i class='gg-menu-left'></i></button>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                    echo "<nav class='navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom'>";
                        echo "<div class='container-fluid'>";
                            echo "<ul class='navbar-nav topbar-nav ms-md-auto align-items-center'>";
                                echo "<li class='nav-item d-flex align-items-center'>";
                                    echo "<span class='me-2'><i class='fas fa-user-circle me-1'></i> " . htmlspecialchars($_SESSION['nombre'] ?? '') . "</span>";
                                echo "</li>";
                            echo "</ul>";
                        echo "</div>";
                    echo "</nav>";
                echo "</div>";

                echo "<div class='container'>";
                    echo "<div class='page-inner'>";

                        include_once '../View/partials/modal.php';

                        if (isset($_GET['modulo'])){
                            resolve();
                        }

                    echo "</div>";
                echo "</div>";
            echo "</div>";

        echo "</div>";
    include_once '../View/partials/footer.php';
    echo "</body>";
    echo "</html>";

?>
