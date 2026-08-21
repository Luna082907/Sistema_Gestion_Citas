<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <section class="auth-card">

        <h1>Iniciar Sesion</h1>
        <p>Ingrese con una cuenta activa del sistema</p>

        <?php if(!empty($error)):?> <div class="alert error" role="alert"><?=e($error)?></div>
        <?php endif;?>

        <form action="<?=e(url('\login'))?>" method="post" novalidate>

            <?=csrf_field()?>
            <label for="email">Correo Electronico</label>
            <input type="email" name="email" id="email" value="<?=e($email ??'')?>" autocomplete="username" required>

            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>

            <button type="submit" class="button primary">Ingresar</button>

        </form>

        <p class="help">Usuario de practica: admin@citas.local/Admin123*</p>
    
    </section>
    
</body>
</html>