<?php include '../partials/header.php'; ?>

<section>
    <h2 class="form-title">¿Has olvidado tu contraseña?</h2>
    <p class="form-subtitle">No te preocupes te ayudaremos en los que sea necesario</p>

    <form class="login-form">
        <div class="form-group">
            <label>Correo electrónico</label>
            <div class="input-wrapper">
                <i data-lucide="user" class="input-icon"></i>
                <input type="email" name="email" required>
            </div>
        </div>

        <button type="submit" class="btn-primary">Enviar</button>
        <a href="login.php" class="btn-secondary">Volver</a>
    </form>
</section>

<?php 

?>

<?php include '../partials/footer.php'; ?>