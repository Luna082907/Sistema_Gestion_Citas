<?php

declare(strict_types=1);

$host=getenv('DB_HOST')?:'127.0.0.1';
$port=getenv('DB_PORT')?:'3306';
$name=getenv('DB_NAME')?:'citas';
$debug=filter_var(getenv('APP_DEBUG')?:'1', FILTER_VALIDATE_BOOL);
$secureCookie=filter_var(getenv('SESSION_SECURE')?:'0', FILTER_VALIDATE_BOOL);

$basePath=trim((string)(getenv('APP_BASE_PATH')?:''));
if ($basePath === '/' || $basePath === '.'){
    $basePath='';
}elseif($basePath !==''){
    $basePath='/'.trim($basePath, '/');
}

return [
    'name'=> getenv('APP_NAME')?:'Sistema de Gestion de Citas',
    'environment'=> getenv('APP_ENV')?:'development',
    'debug'=> $debug,
    'timezone'=> getenv('APP_TIMEZONE')?:'America/Bogota',
    'base_path'=> $basePath,
    'session'=>[
        'name'=>getenv('SESSION_NAME')?:'citas_session',
        'secure'=> $secureCookie,
    ],
    'database'=>[
        'dsn'=>"mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
    ],
    'appointments'=>[
        'day_start'=> getenv('APPOINTMENT_DAY_START')?:'08:00',
        'day_end'=> getenv('APPOINTMENT_DAY_END')?:'12:00',
        'slot_minutes'=> (int)(getenv('APPOINTMENT_SLOT_MINUTES')?:20),
        'max_days_ahead'=> (int)(getenv('APPOINTMENT_MAX_DAYS')?:90),
        'allow_weekends'=> filter_var(getenv('APPOINTMENT_ALLOW_WEEKENDS')?:'0',FILTER_VALIDATE_BOOL),
    ],
];

?>