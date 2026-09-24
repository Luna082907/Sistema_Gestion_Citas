<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Repository\RoomRepository;
use DateTimeImmutable;
use PDOException;

final class RoomController{

    public function __construct(private RoomRepository $rooms){
    }

    public function index(): void
{
    Auth::requireLogin();
    Auth::requireRole(Auth::ROLE_ADMIN, Auth::ROLE_RECEPTIONIST, Auth::ROLE_DOCTOR);

    $term = trim((string) ($_GET['q'] ?? ''));

    $perPage = 10;
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1) {
        $page = 1;
    }

    $result = $this->rooms->search($term, $page, $perPage);
    $total = $result['total'];
    $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;

    View::render('rooms/index',
    [
        'title' => 'Salas',
        'rooms' => $result['items'],
        'term' => $term,
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
    ]);
}

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(Auth::ROLE_ADMIN);

        View::render('rooms/create', [
            'title' => 'Registrar consultorio',
            'data' => [],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(Auth::ROLE_ADMIN);
        Csrf::requireValid($_POST['_token'] ?? null);

        $data = [
            'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'active' => trim((string) ($_POST['active'] ?? '')),
        ];
        $errors = $this->validate($data);

        if ($errors !== []) {
            View::render('rooms/create', compact('data', 'errors') + ['title' => 'Registrar consultorio']);
            return;
        }

        $this->rooms->create($data);
        flash('success', 'Consultorio registrado correctamente.');
        redirect('/rooms');
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(Auth::ROLE_ADMIN);
        $room = $this->rooms->findById($id);

        if ($room === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Consultorio no encontrado']);
            return;
        }

        View::render('rooms/edit', [
            'title' => 'Editar consultorio',
            'data' => $room,
            'errors' => [],
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(Auth::ROLE_ADMIN);
        Csrf::requireValid($_POST['_token'] ?? null);

        $room = $this->rooms->findById($id);
        if ($room === null) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Consultorio no encontrado']);
            return;
        }

        $data = [
            'code' => strtoupper(trim((string) ($_POST['code'] ?? ''))),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'active' => trim((string) ($_POST['active'] ?? '')),
        ];
        $errors = $this->validate($data);

        if ($errors !== []) {
            View::render('rooms/edit', compact('data', 'errors') + ['title' => 'Editar consultorio']);
            return;
        }

        $this->rooms->update($id, $data);
        flash('success', 'Consultorio actualizado correctamente.');
        redirect('/rooms');
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (!preg_match('/^[A-Z0-9-]{2,20}$/', $data['code'])) {
            $errors['code'] = 'Ingrese un código de 2 a 20 letras, números o guiones.';
        }

        if (mb_strlen($data['name']) < 2 || mb_strlen($data['name']) > 80) {
            $errors['name'] = 'Ingrese un nombre de 2 a 80 caracteres.';
        }

        if (!in_array($data['active'], ['1', '0'], true)) {
            $errors['active'] = 'Seleccione una opción válida.';
        }

        return $errors;
    }
}