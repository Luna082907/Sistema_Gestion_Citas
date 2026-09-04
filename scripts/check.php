<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$config = require $root . '/bootstrap/app.php';
$errors = [];
$checked = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $checked++;
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1';
    exec($command, $output, $exitCode);
    if ($exitCode !== 0) {
        $errors[] = implode(PHP_EOL, $output);
    }

    $output = [];
}

$slots = (new App\Domain\SlotGenerator())->generate('08:00', '12:00', 20);

if (count($slots) !== 12 || $slots[0] !== '08:00:00' || $slots[11] !== '11:40:00') {
    $errors[] = 'Falló la prueba básica del generador de horarios.';
}

if (!password_verify('Admin123*', '$2y$12$zklGWiffVJu/h7QZTe5uFuAblqsjGFby4UA/81QwADQacbtgupAsi')) {
    $errors[] = 'La contraseña de prueba no coincide con el hash del archivo seed.';
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL . PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "Comprobación correcta: {$checked} archivos PHP y pruebas básicas superadas." . PHP_EOL;