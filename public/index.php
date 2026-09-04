<?php

declare(strict_types=1);

use App\Controller\ApiController;
use App\Controller\AppointmentController;
use App\Controller\AuthController;
use App\Controller\DashboardController;
use App\Controller\HealthController;
use App\Controller\doctorController;
use App\Controller\PatientController;
use App\Controller\AgendaController; /**Se importa*/
use App\Core\Database;
use App\Core\Router;
use App\Core\View;
use App\Domain\SlotGenerator;
use App\Repository\AppointmentRepository;
use App\Repository\DoctorRepository;
use App\Repository\PatientRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentService;

$config = require dirname(__DIR__) . '/bootstrap/app.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");

try {
    $database = new Database($config['database']);
    $pdo = $database->pdo();
    $users = new UserRepository($pdo);
    $patients = new PatientRepository($pdo);
    $doctors = new DoctorRepository($pdo);
    $rooms = new RoomRepository($pdo);
    $appointments = new AppointmentRepository($pdo);
    $appointmentService = new AppointmentService(
    $database,
    $appointments,
    $doctors,
    $rooms,
    new SlotGenerator(),
        $config['appointments']
    );

    $authController = new AuthController($users);
    $dashboardController = new DashboardController($patients, $appointments);
    $patientController = new PatientController($patients);
    $agendaController = new AgendaController($appointments, $doctors, $rooms); /**Se agrega la clase AgendaController */
    $doctorController = new DoctorController($doctors);
    $appointmentController = new AppointmentController(
        $patients,
        $doctors,
        $rooms,
        $appointments,
        $appointmentService
    );
    $apiController = new ApiController($appointmentService);
    $healthController = new HealthController($pdo);

    $router = new Router($config['base_path']);

    $router->get('/health', [$healthController, 'show']);

    $router->get('/login', [$authController, 'showLogin']);
    $router->post('/login', [$authController, 'login']);
    $router->post('/logout', [$authController, 'logout']);

    $router->get('/', [$dashboardController, 'index']);

    $router->get('/doctors', [$doctorController, 'index']);
    $router->get('/doctors/create', [$doctorController, 'create']);
    $router->post('/doctors', [$doctorController, 'store']);

    $router->get('/patients', [$patientController, 'index']);
    $router->get('/patients/create', [$patientController, 'create']);
    $router->post('/patients', [$patientController, 'store']);
    $router->get('/patients/{id}/edit',[$patientController, 'edit']);
    $router->post('/patients/{id}',[$patientController, 'update']);
    $router->post('/patients/{id}/delete',[$patientController, 'delete']);

    $router->get('/doctors/{id}/edit',[$doctorController, 'edit']);
    $router->post('/doctors/{id}',[$doctorController, 'update']);
    $router->post('/doctors/{id}/delete',[$doctorController, 'delete']);

    $router->get('/appointments', [$appointmentController, 'index']);
    $router->get('/appointments/create', [$appointmentController, 'create']);
    $router->post('/appointments', [$appointmentController, 'store']);
    $router->get('/appointments/{id}', [$appointmentController, 'show']);
    $router->post('/appointments/{id}/cancel', [$appointmentController, 'cancel']);
    $router->post('/appointments/{id}/complete', [$appointmentController, 'complete']);

    $router->get('/agenda', [$agendaController, 'index']);

    $router->get('/api/availability', [$apiController, 'availability']);

    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    View::render('errors/500', [
        'title' => 'Error del servidor',
        'details' => $config['debug'] ? $exception->getMessage() : null,
    ]);
}