<?php include 'partials/header_login.php'; ?>

<section>
    <h2 class="form-title">Restablecer contraseña</h2>
    <p class="form-subtitle">Crea una nueva contraseña para ingresar al sistema</p>

    <form class="login-form">
        <div class="form-group">
            <label>Nueva contraseña</label>
            <div class="input-wrapper">
                <i data-lucide="lock" class="input-icon"></i>
                <input 
                    type="password" 
                    name="new_password" 
                    placeholder="******" 
                    required 
                    autocomplete="new-password"
                >
            </div>
        </div>

        <div class="form-group">
            <label>Confirmar nueva contraseña</label>
            <div class="input-wrapper">
                <i data-lucide="check-circle" class="input-icon"></i>
                <input 
                    type="password" 
                    name="confirm_password" 
                    placeholder="******" 
                    required 
                    autocomplete="new-password"
                >
            </div>
        </div>

        <button type="submit" class="btn-primary">Guardar contraseña</button>
        <a href="login.php" class="btn-secondary">Cancelar</a>
    </form>
</section>

<?php include 'partials/footer_login.php'; ?>