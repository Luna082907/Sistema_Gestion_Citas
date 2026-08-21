<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Repository\PatientRepository;
use DateTimeImmutable;
use PDOException;

final class PatientController{
    public function __construct(private PatientRepository $patients){
    }

    public function index(): void{
        Auth::requireLogin();
        $term = trim((string) ($_GET['q'] ?? ''));
        View::render('patients/index', [
            'title' => 'Pacientes',
            'patients' => $this->patients->search($term),
            'term' => $term,
        ]);
    }

    public function create(): void{
        Auth::requireLogin();
        View::render('patients/create', [
            'title' => 'Registrar paciente',
            'data' => [],
            'errors' => [],
        ]);
    }

    public function store(): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);

        $data = [
            'document_type' => strtoupper(trim((string) ($_POST['document_type'] ?? 'CC'))),
            'document_number' => strtoupper(trim((string) ($_POST['document_number'] ?? ''))),
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'birth_date' => trim((string) ($_POST['birth_date'] ?? '')),
            'sex' => strtoupper(trim((string) ($_POST['sex'] ?? ''))),
            'phone' => trim((string) ($_POST['phone'] ?? '')) ?: null,
            'email' => mb_strtolower(trim((string) ($_POST['email'] ?? ''))) ?: null,
        ];

        $errors = $this->validate($data);
        if ($errors !== []) {
            View::render('patients/create', compact('data', 'errors') + ['title' => 'Registrar paciente']);
            return;
        }

        try {
            $this->patients->create($data);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors['document_number'] = 'Ya existe un paciente con ese documento.';
                View::render('patients/create', compact('data', 'errors') + ['title' => 'Registrar paciente']);
                return;
            }
            throw $exception;
        }

        flash('success', 'Paciente registrado correctamente.');
        redirect('/patients');
    }

    private function validate(array $data): array{
        $errors = [];

        if (!in_array($data['document_type'], ['CC', 'TI', 'CE', 'PA'], true)) {
            $errors['document_type'] = 'Seleccione un tipo de documento válido.';
        }

        if (!preg_match('/^[A-Z0-9-]{5,30}$/', $data['document_number'])) {
            $errors['document_number'] = 'El documento debe tener entre 5 y 30 letras, números o guiones.';
        }

        if (mb_strlen($data['first_name']) < 2 || mb_strlen($data['first_name']) > 80) {
            $errors['first_name'] = 'Ingrese nombres de 2 a 80 caracteres.';
        }

        if (mb_strlen($data['last_name']) < 2 || mb_strlen($data['last_name']) > 80) {
            $errors['last_name'] = 'Ingrese apellidos de 2 a 80 caracteres.';
        }

        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $data['birth_date']);
        if ($birth === false || $birth->format('Y-m-d') !== $data['birth_date'] || $birth > new DateTimeImmutable('today')) {
            $errors['birth_date'] = 'Ingrese una fecha de nacimiento válida.';
        }

        if (!in_array($data['sex'], ['F', 'M', 'O'], true)) {
            $errors['sex'] = 'Seleccione una opción válida.';
        }

        if ($data['phone'] !== null && !preg_match('/^[0-9+()\s-]{7,30}$/', $data['phone'])) {
            $errors['phone'] = 'Ingrese un teléfono válido.';
        }

        if ($data['email'] !== null && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ingrese un correo válido.';
        }

        return $errors;
    }
}

?>