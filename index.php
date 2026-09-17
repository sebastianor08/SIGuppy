<?php
session_start();

// Si no existe sesión activa, expulsar al login
if (!isset($_SESSION['id_usuario'])) {
  header("Location: ../../View/login/login.php");
    exit();
}
?>