<?php
use App\Core\Auth;
$success = flash('success');
$errorFlash = flash('error');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title><?= e($title ?? 'Gestión de Citas') ?> | Gestión de Citas</title>
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body data-base-url="<?= e(url('/')) ?>">
<a class="skip-link" href="#main-content">Saltar al contenido</a>
<header class="site-header">
<div class="container header-content">
<a class="brand" href="<?= e(url('/')) ?>">Gestión de Citas</a>
<?php if (Auth::check()): ?>
<nav aria-label="Navegación principal">
<a href="<?= e(url('/')) ?>">Inicio</a>
<a href="<?= e(url('/patients')) ?>">Pacientes</a>
<a href="<?= e(url('/appointments')) ?>">Citas</a>
<a href="<?= e(url('/appointments/create')) ?>">Asignar cita</a>
</nav>
<div class="user-menu">
<span><?= e(Auth::user()['name']) ?></span>
<form method="post" action="<?= e(url('/logout')) ?>">
<?= csrf_field() ?>
<button class="link-button" type="submit">Salir</button>
</form>
</div>
<?php endif; ?>
</div>
</header>
<main id="main-content" class="container main-content">
<?php if ($success): ?><div class="alert success" role="status"><?= e($success) ?></div><?php endif; ?>
<?php if ($errorFlash): ?><div class="alert error" role="alert"><?= e($errorFlash) ?></div><?php endif; ?>
<?= $content ?>
</main>
<footer class="site-footer">
<div class="container">Proyecto educativo con PHP 8, PDO y MariaDB.</div>
</footer>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>