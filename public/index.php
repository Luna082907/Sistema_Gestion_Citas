<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\DashboardController;
use App\Controller\HealthController;
use App\Controller\PatientController;

use App\Core\Database;
use App\Core\Router;
use App\Core\View;

use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use App\Repository\UserRepository;

$config = require dirname(__DIR__) . '/bootstrap/app.php';

try {

    // Conexión
    $database = new Database($config['database']);
    $pdo = $database->pdo();

    // Repositorios construidos hasta esta unidad
    $users = new UserRepository($pdo);
    $patients = new PatientRepository($pdo);
    $appointments = new AppointmentRepository($pdo);

    // Controladores construidos hasta esta unidad
    $healthController = new HealthController($pdo);
    $authController = new AuthController($users);

    $dashboardController = new DashboardController(
        $patients,
        $appointments
    );

    $patientController = new PatientController(
        $patients
    );

    // Router
    $router = new Router($config['base_path']);

    // Unidad 4
    $router->get('/health', [
        $healthController,
        'show'
    ]);

    // Unidad 5
    $router->get('/login', [
        $authController,
        'showLogin'
    ]);

    $router->post('/login', [
        $authController,
        'login'
    ]);

    $router->post('/logout', [
        $authController,
        'logout'
    ]);

    // Unidad 6
    $router->get('/', [
        $dashboardController,
        'index'
    ]);

    // Unidad 7 - AQUÍ se agregan las rutas del punto 7.5
    $router->get('/patients', [
        $patientController,
        'index'
    ]);

    $router->get('/patients/create', [
        $patientController,
        'create'
    ]);

    $router->post('/patients', [
        $patientController,
        'store'
    ]);

    // Siempre debe quedar al final
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/'
    );

} catch (Throwable $exception) {

    error_log((string) $exception);

    http_response_code(500);

    View::render('errors/500', [
        'title' => 'Error del servidor',
        'details' => $config['debug']
            ? $exception->getMessage()
            : null,
    ]);
}