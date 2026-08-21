<?php
declare(strict_types=1);
$root = dirname(__DIR__);
require_once __DIR__ . '/env.php';
load_env_file($root . '/.env');
// Detecta automáticamente la carpeta base cuando el proyecto está dentro de htdocs.
$configuredBasePath = getenv('APP_BASE_PATH');
if ($configuredBasePath === false || strtolower(trim((string) $configuredBasePath)) === 'auto') {
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$directory = str_replace('\\', '/', dirname($scriptName));
if (str_ends_with($directory, '/public')) {
$directory = substr($directory, 0, -7);
}
$detectedBasePath = ($directory === '/' || $directory === '.' || $directory === '\\')
? ''
: '/' . trim($directory, '/');
putenv('APP_BASE_PATH=' . $detectedBasePath);
$_ENV['APP_BASE_PATH'] = $detectedBasePath;
$_SERVER['APP_BASE_PATH'] = $detectedBasePath;
}
$composerAutoload = $root . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
require_once $composerAutoload;
} else {
spl_autoload_register(static function (string $class) use ($root): void {
$prefix = 'App\\';
if (!str_starts_with($class, $prefix)) {
return;
}
$relative = substr($class, strlen($prefix));
$file = $root . '/src/' . str_replace('\\', '/', $relative) . '.php';
if (is_file($file)) {
require_once $file;
}
});require_once $root . '/src/Support/helpers.php';
}
$config = require $root . '/config/app.php';
$GLOBALS['app_config'] = $config;
date_default_timezone_set($config['timezone']);
if ($config['debug']) {
ini_set('display_errors', '1');
error_reporting(E_ALL);
} else {
ini_set('display_errors', '0');
}
ini_set('session.use_strict_mode', '1');
session_name($config['session']['name']);
$cookiePath = $config['base_path'] === '' ? '/' : $config['base_path'] . '/';
session_set_cookie_params([
'lifetime' => 0,
'path' => $cookiePath,
'domain' => '',
'secure' => $config['session']['secure'],
'httponly' => true,
'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
session_start();
}
return $config;