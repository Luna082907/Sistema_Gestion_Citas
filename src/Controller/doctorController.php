<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Repository\DoctorRepository;
use DateTimeImmutable;
use PDOException;

final class DoctorController{

    public function __construct(private DoctorRepository $doctors){
    }

    public function index(): void
{
    Auth::requireLogin();

    $term = trim((string) ($_GET['q'] ?? ''));

    $perPage = 10;
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1) {
        $page = 1;
    }

    $result = $this->doctors->search($term, $page, $perPage);
    $total = $result['total'];
    $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;

    View::render('doctors/index',
    [
        'title' => 'Médicos',
        'doctors' => $result['items'],
        'term' => $term,
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
    ]);
}

    public function create(): void{
        Auth::requireLogin();
        View::render('doctors/create',
        [
            'title' => 'Registrar médico',
            'data' => [],
            'errors' => [],
        ]);
    }

    public function store(): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);
        $data =
        [
            'license_number' => strtoupper(trim((string) ($_POST['license_number'] ?? ''))),
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'specialty' => trim((string) ($_POST['specialty'] ?? '')),
            'active' => trim((string) ($_POST['active'] ?? '')),
        ];
        
        $errors = $this->validate($data);

        if ($errors !== []){
            View::render('doctors/create', compact('data', 'errors') + ['title' => 'Registrar Médico']);
            return;
        }try{
            $this->doctors->create($data);
        }catch (PDOException $exception){

            if ($exception->getCode() === '23000'){
                $errors['license_number'] = 'Ya existe un doctor con esa licencia.';
                View::render('doctors/create', compact('data', 'errors') + ['title' => 'Registrar Médico']);
                return;
            }

            throw $exception;
        }
            
        flash('success', 'Médico registrado correctamente.');
        redirect('/doctors');
    }
    
    private function validate(array $data): array{
        $errors = [];

        if (!preg_match('/^[A-Z0-9-]{5,30}$/', $data['license_number'])){
            $errors['license_number'] = 'El documento debe tener entre 5 y 30 letras, números o guiones.';
        }

        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['first_name']) > 80){
            $errors['first_name'] = 'Ingrese nombres de 2 a 80 caracteres.';
        }

        if (mb_strlen($data['last_name']) < 2 || mb_strlen($data['last_name']) > 80){
            $errors['last_name'] = 'Ingrese apellidos de 2 a 80 caracteres.';
        }

        $allowedSpecialties = [
            'Medicina General',
            'Cardiologia',
            'Pediatria',
            'Cardiología',
            'Neurología',
            'Pediatría',
        ];

        if (!in_array($data['specialty'], $allowedSpecialties, true)){
            $errors['specialty'] = 'Seleccione una opción válida.';
        }

        if (!in_array($data['active'], ['1', '0'], true)){
            $errors['active'] = 'Seleccione una opción válida.';
        }

        return $errors;

        }

    public function edit(int $id): void{
        Auth::requireLogin();
        $doctor = $this->doctors->findById($id);
        
        if ($doctor === null) {
            http_response_code(404);
            echo 'Médico no encontrado.';
            return;
        }

        View::render('doctors/edit', [
            'title' => 'Editar médico',
            'data' => $doctor,
            'errors' => [],
        ]);
    }

    public function update(int $id): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);
        $doctor = $this->doctors->findById($id);
        if ($doctor === null) {
        http_response_code(404);
        echo 'Médico no encontrado.';
        return;
        }
        $data = [
                'license_number' => strtoupper(trim((string) ($_POST['license_number'] ?? ''))),
                'first_name' => trim((string) ($_POST['first_name'] ?? '')),
                'last_name' => trim((string) ($_POST['last_name'] ?? '')),
                'specialty' => trim((string) ($_POST['specialty'] ?? '')),
                'active' => trim((string) ($_POST['active'] ?? '')),
                ];
        $errors = $this->validate($data);
        if ($errors !== []) {
            View::render('doctors/edit', [
            'title' => 'Editar médico',
            'data' => $data,
            'errors' => $errors,
            ]);
        return;
        }
        try {
         $this->doctors->update($id, $data);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
            $errors['license_number'] =
            'Ya existe otro médico con esa licencia.';
            View::render('doctors/edit', [
            'title' => 'Editar médico',
            'data' => $data,
            'errors' => $errors,
            ]);
            return;
            }
            throw $exception;
            }
        flash('success', 'Médico actualizado correctamente.');
        redirect('/doctors');
    }

    public function delete(int $id): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);

        $doctor = $this->doctors->findById($id);

        if ($doctor === null){
            http_response_code(404);
            echo 'Médico no encontrado';
            return;
        }

        try{
            $this->doctors->delete($id);
            flash('success', 'Médico eliminado correctamente');
            redirect('/doctors');
        } catch (PDOException $exception){
            if ($exception->getCode() === '23000'){
                flash(
                    'error',
                    'No se puede eliminar el médico porque tiene información relacionada'
                );
                redirect('/doctors');
                return;
            }

            throw $exception;
        }
    } 

    private function requireAccessibleAppointment(string $id): array
    {
        $appointment = $this->appointments->find((int) $id);

        if ($appointment === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Cita no encontrada']);
            exit;
        }

        if (Auth::hasRole(Auth::ROLE_DOCTOR)
            && (int) $appointment['doctor_id'] !== (int) Auth::doctorId()) {
            Auth::deny();                 // 403: la cita es de otro médico
        }

        return $appointment;
    }

    
}


?>