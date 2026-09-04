<?php

declare(strict_types=1);

echo '<pre>';

echo "Proyecto:\n";
echo __DIR__ . "\n\n";

echo "Csrf.php existe:\n";
var_dump(
    file_exists(__DIR__. '/src/Core/Csrf.php')
);

echo "\nAutoload existe:\n";
var_dump(
    file_exists(__DIR__ . '/vendor/autoload.php')
);

require __DIR__ . '/vendor/autoload.php';

echo "\nClase Csrf cargada por Composer:\n";
var_dump(
    class_exists('App\\Core\\Csrf')
);

echo '</pre>';