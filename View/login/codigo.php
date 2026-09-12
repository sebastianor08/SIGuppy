<?php include '../partials/header_login.php'; ?>

<section>
    <h2 class="form-title">Código de verificación</h2>
    <p class="form-subtitle">Ingresa el código de confirmación enviado a tu correo</p>

    <form class="login-form">
        <div class="form-group">
            <label>Código de confirmación</label>
            <div class="input-wrapper">
                <i data-lucide="key-round" class="input-icon"></i>
                <input 
                    type="text" 
                    name="code" 
                    placeholder="****" 
                    maxlength="6" 
                    required 
                    style="letter-spacing: 4px; font-weight: bold;"
                >
            </div>
        </div>

        <button type="submit" class="btn-primary">Verificar código</button>
        <a href="recuperacion.php" class="btn-secondary">Volver</a>
    </form>
</section>

<?php include '../partials/footer_login.php'; ?>