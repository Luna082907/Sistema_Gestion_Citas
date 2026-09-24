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
use App\Controller\RoomController;
use App\Controller\UserController;
use App\Core\Auth;
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
    $roomController = new RoomController($rooms);
    $doctorController = new DoctorController($doctors);
    $userController = new UserController($users, $doctors);
    $appointmentController = new AppointmentController(
        $patients,
        $doctors,
        $rooms,
        $appointments,
        $appointmentService
    );
    $apiController = new ApiController($appointmentService);
    $healthController = new HealthController($pdo);
    
    $requireAuth           = Auth::guard();                                   // cualquier sesión
    $requireAdmin          = Auth::guard(Auth::ROLE_ADMIN);                   // solo admin
    $requirePatientManager = Auth::guard(Auth::ROLE_ADMIN, Auth::ROLE_RECEPTIONIST);
    $requireCareTeam       = Auth::guard(Auth::ROLE_ADMIN, Auth::ROLE_RECEPTIONIST, Auth::ROLE_DOCTOR);

    $router = new Router($config['base_path']);

    $router->get('/health', [$healthController, 'show']);

    $router->get('/login', [$authController, 'showLogin']);
    $router->post('/login', [$authController, 'login']);
    $router->post('/logout', [$authController, 'logout']);

    $router->get('/', [$dashboardController, 'index']);

      // Administración de usuarios y roles: solo admin
    $router->get('/users', [$userController, 'index'], [$requireAdmin]);
    $router->get('/users/create', [$userController, 'create'], [$requireAdmin]);
    $router->post('/users', [$userController, 'store'], [$requireAdmin]);
    $router->post('/users/{id}/toggle', [$userController, 'toggle'], [$requireAdmin]);

    // Médicos: solo admin
    $router->get('/doctors', [$doctorController, 'index'], [$requireAdmin]);
    $router->get('/doctors/create', [$doctorController, 'create'], [$requireAdmin]);
    $router->post('/doctors', [$doctorController, 'store'], [$requireAdmin]);

    // Pacientes: el médico solo consulta; la edición es del admin
    $router->get('/patients', [$patientController, 'index'], [$requireCareTeam]);
    $router->get('/patients/create', [$patientController, 'create'], [$requirePatientManager]);
    $router->post('/patients', [$patientController, 'store'], [$requirePatientManager]);
    $router->get('/patients/{id}/edit', [$patientController, 'edit'], [$requireAdmin]);
    $router->post('/patients/{id}', [$patientController, 'update'], [$requireAdmin]);
    $router->post('/patients/{id}/delete', [$patientController, 'delete'], [$requireAdmin]);

    // Citas
    $router->get('/appointments', [$appointmentController, 'index'], [$requireAuth]);
    $router->get('/appointments/create', [$appointmentController, 'create'], [$requirePatientManager]);
    $router->post('/appointments', [$appointmentController, 'store'], [$requirePatientManager]);
    $router->get('/appointments/{id}', [$appointmentController, 'show'], [$requireAuth]);
    $router->post('/appointments/{id}/cancel', [$appointmentController, 'cancel'], [$requirePatientManager]);
    $router->post('/appointments/{id}/complete', [$appointmentController, 'complete'], [$requireCareTeam]);
    $router->post('/appointments/{id}/no-show', [$appointmentController, 'noShow'], [$requireCareTeam]);
    $router->post('/appointments/{id}/notes', [$appointmentController, 'updateNotes'], [$requireCareTeam]);

    $router->get('/agenda', [$agendaController, 'index']);

    $router->get('/rooms', [$roomController, 'index']);
    $router->get('/rooms/create', [$roomController, 'create'], [$requireAdmin]);
    $router->post('/rooms', [$roomController, 'store'], [$requireAdmin]);
    $router->get('/rooms/{id}/edit', [$roomController, 'edit'], [$requireAdmin]);
    $router->post('/rooms/{id}', [$roomController, 'update'], [$requireAdmin]);

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