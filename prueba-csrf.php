<?php

declare(strict_types=1);

require __DIR__ . '/src/Core/Csrf.php';

var_dump(
    class_exists(\App\Core\Csrf::class)
);