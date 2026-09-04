<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Repository\AppointmentRepository;
use App\Repository\DoctorRepository;
use App\Repository\PatientRepository;
use App\Repository\RoomRepository;
use App\Service\AppointmentService;
use DateTimeImmutable;
use DomainException;

final class AppointmentController{
    public function __construct(
        private PatientRepository $patients,
        private DoctorRepository $doctors,
        private RoomRepository $rooms,
        private AppointmentRepository $appointments,
        private AppointmentService $service
    ) {
    }

    public function index(): void{
        Auth::requireLogin();
        $document = trim((string) ($_GET['document'] ?? ''));
        View::render('appointments/index', [
            'title' => 'Citas',
            'appointments' => $this->appointments->list($document),
            'document' => $document,
        ]);
    }

    public function create(): void{
        Auth::requireLogin();
        $this->renderCreate([], []);
    }

    public function store(): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);

        $form = [
            'patient_document' => strtoupper(trim((string) ($_POST['patient_document'] ?? ''))),
            'doctor_id' => (int) ($_POST['doctor_id'] ?? 0),
            'room_id' => (int) ($_POST['room_id'] ?? 0),
            'appointment_date' => trim((string) ($_POST['appointment_date'] ?? '')),
            'appointment_time' => trim((string) ($_POST['appointment_time'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];

        $errors = [];
        $patient = $this->patients->findByDocument($form['patient_document']);
        
        if ($patient === null) {
            $errors['patient_document'] = 'No existe un paciente con ese documento.';
        }

        if ($this->doctors->findActive($form['doctor_id']) === null) {
            $errors['doctor_id'] = 'Seleccione un médico activo.';
        }

        if ($this->rooms->findActive($form['room_id']) === null) {
            $errors['room_id'] = 'Seleccione un consultorio activo.';
        }
        
        if ($form['appointment_date'] === '') {
            $errors['appointment_date'] = 'Seleccione una fecha.';
        }
        
        if ($form['appointment_time'] === '') {
            $errors['appointment_time'] = 'Seleccione una hora disponible.';
        }
        
        if (mb_strlen($form['notes']) > 500) {
            $errors['notes'] = 'Las observaciones no pueden superar 500 caracteres.';
        }

        if ($errors !== []) {
            $this->renderCreate($form, $errors);
            return;
        }

        try {
            $id = $this->service->create([
                'patient_id' => (int) $patient['id'],
                'doctor_id' => $form['doctor_id'],
                'room_id' => $form['room_id'],
                'appointment_date' => $form['appointment_date'],
                'appointment_time' => $form['appointment_time'],
                'notes' => $form['notes'] !== '' ? $form['notes'] : null,
                'created_by' => (int) Auth::id(),
            ]);
        } catch (DomainException $exception) {
            $errors['appointment_time'] = $exception->getMessage();
            $this->renderCreate($form, $errors);
            return;
        }

        flash('success', 'Cita asignada correctamente.');
        redirect('/appointments/' . $id);
    }

    public function show(string $id): void{
        Auth::requireLogin();
        $appointment = $this->appointments->find((int) $id);
        
        if ($appointment === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Cita no encontrada']);
            return;
        }

        View::render('appointments/show', [
            'title' => 'Detalle de la cita',
            'appointment' => $appointment,
        ]);
    }

    public function cancel(string $id): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);
        
        if (!$this->appointments->cancel((int) $id, (int) Auth::id())) {
            flash('error', 'La cita no existe o ya no puede cancelarse.');
        } else {
            flash('success', 'La cita fue cancelada y el horario quedó disponible.');
        }
        
        redirect('/appointments/' . (int) $id);
    }

    public function complete(string $id): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);
        
        if (!$this->appointments->complete((int) $id, (int) Auth::id())) {
            flash('error', 'La cita no existe o ya no puede marcarse como atendida.');
        } else {
            flash('success', 'La cita fue marcada como atendida.');
        }

        redirect('/appointments/' . (int) $id);
    }

    private function renderCreate(array $data, array $errors): void{
        View::render('appointments/create', [
            'title' => 'Asignar cita',
            'doctors' => $this->doctors->active(),
            'rooms' => $this->rooms->active(),
            'data' => $data,
            'errors' => $errors,
            'minimumDate' => (new DateTimeImmutable('today'))->format('Y-m-d'),
            'maximumDate' => (new DateTimeImmutable('+90 days'))->format('Y-m-d'),
        ]);
    }
}

?>