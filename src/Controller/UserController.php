<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\View;
use App\Repository\UserRepository;
use App\Repository\DoctorRepository;

final class UserController
{
    public function __construct(
        private UserRepository $users,
        private DoctorRepository $doctors
    ) {
    }

    /**
     * Lista los usuarios.
     */
    public function index(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN);

        View::render('users/index', [
            'title' => 'Usuarios',
            'users' => $this->users->all(),
        ]);
    }

    /**
     * Muestra el formulario para crear un usuario.
     */
    public function create(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN);

        View::render('users/create', [
            'title' => 'Crear usuario',
            'doctors' => $this->doctors->active(),
            'data' => [
                'name' => '',
                'email' => '',
                'role' => '',
                'doctor_id' => '',
            ],
            'errors' => [],
        ]);
    }

    /**
     * Guarda un nuevo usuario.
     */
    public function store(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN);

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $role = trim((string) ($_POST['role'] ?? ''));
        $doctorIdRaw = trim((string) ($_POST['doctor_id'] ?? ''));

        $errors = [];

        if ($name === '') {
            $errors[] = 'El nombre es obligatorio.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingrese un correo válido.';
        }

        if ($password === '') {
            $errors[] = 'La contraseña es obligatoria.';
        }

        if (!in_array($role, Auth::roles(), true)) {
            $errors[] = 'El rol seleccionado no es válido.';
        }

        $doctorId = null;

        if ($role === Auth::ROLE_DOCTOR) {
            if ($doctorIdRaw === '') {
                $errors[] = 'Debe seleccionar el médico asociado.';
            } else {
                $doctorId = (int) $doctorIdRaw;

                if ($doctorId <= 0) {
                    $errors[] = 'El médico seleccionado no es válido.';
                }
            }
        }

        if ($this->users->emailExists($email)) {
            $errors[] = 'Ya existe un usuario con ese correo.';
        }

        if ($errors !== []) {
            View::render('users/create', [
                'title' => 'Crear usuario',
                'doctors' => $this->doctors->active(),
                'data' => [
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'doctor_id' => $doctorIdRaw,
                ],
                'errors' => $errors,
            ]);

            return;
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $this->users->create(
            $name,
            $email,
            $passwordHash,
            $role,
            $doctorId
        );

        flash('success', 'Usuario creado correctamente.');

        redirect('/users');
    }

    /**
     * Activa o desactiva un usuario.
     */
    public function toggle(string $id): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN);

        $user = $this->users->findById((int) $id);

        if ($user === null) {
            http_response_code(404);

            View::render('errors/404', [
                'title' => 'Usuario no encontrado',
            ]);

            return;
        }

        $active = (bool) $user['active'];

        $this->users->setActive(
            (int) $id,
            !$active
        );

        flash(
            'success',
            $active
                ? 'Usuario desactivado.'
                : 'Usuario activado.'
        );

        redirect('/users');
    }
}