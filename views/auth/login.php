<section class="auth-card">
    <h1>Iniciar sesión</h1>
    <p>Ingrese con una cuenta activa del sistema.</p>
    <?php if (!empty($error)): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/login')) ?>" novalidate>
        <?= csrf_field() ?>
        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" value="<?= e($email ?? '') ?>" autocomplete="username" required>
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="button primary" type="submit">Ingresar</button>
    </form>
    <p class="help">Usuario de práctica: admin@citas.local / Admin123*</p>
</section>