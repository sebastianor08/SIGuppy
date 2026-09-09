<?php include '../partials/header.php'; ?>

<section>
    <h2 class="form-title">Iniciar sesión</h2>
    <p class="form-subtitle">Accede según tu rol asignado</p>

    <form class="login-form">
        <div class="form-group">
            <label>Usuario o correo electrónico</label>
            <div class="input-wrapper">
                <i data-lucide="user" class="input-icon"></i>
                <input type="text" name="username" required autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <div class="input-wrapper">
                <i data-lucide="lock" class="input-icon"></i>
                <input type="password" name="password" placeholder="******" required autocomplete="current-password">
                <button type="button" class="toggle-password-btn" tabindex="-1">
                    <i data-lucide="eye" class="toggle-icon"></i>
                </button>
            </div>
        </div>

        <div class="forgot-link-container">
            <a href="recuperacion.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-primary">Iniciar sesión</button>
    </form>
</section>

<?php include '../partials/footer.php'; ?>