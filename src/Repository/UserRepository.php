<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Busca un usuario activo por correo.
     */
    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                email,
                password_hash,
                role,
                doctor_id
             FROM users
             WHERE email = :email
               AND active = 1
             LIMIT 1'
        );

        $statement->execute([
            'email' => $email
        ]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    /**
     * Lista todos los usuarios.
     */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                u.id,
                u.name,
                u.email,
                u.role,
                u.active,
                u.doctor_id,
                d.license_number,
                d.first_name AS doctor_first_name,
                d.last_name AS doctor_last_name
             FROM users u
             LEFT JOIN doctors d ON d.id = u.doctor_id
             ORDER BY u.name'
        );

        return $statement->fetchAll();
    }

    /**
     * Verifica si un correo ya existe.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id
                FROM users
                WHERE email = :email';

        $params = [
            'email' => $email
        ];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() !== false;
    }

    /**
     * Crea un nuevo usuario.
     */
    public function create(
        string $name,
        string $email,
        string $passwordHash,
        string $role,
        ?int $doctorId = null
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO users
                (name, email, password_hash, role, doctor_id, active)
             VALUES
                (:name, :email, :password_hash, :role, :doctor_id, 1)'
        );

        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'doctor_id' => $doctorId
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Busca un usuario por ID.
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                email,
                role,
                active,
                doctor_id
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id
        ]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    /**
     * Activa o desactiva un usuario.
     */
    public function setActive(int $id, bool $active): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET active = :active
             WHERE id = :id'
        );

        $statement->execute([
            'active' => $active ? 1 : 0,
            'id' => $id
        ]);
    }

    /**
     * Busca un médico que todavía no esté vinculado
     * a una cuenta de usuario.
     */
    public function availableDoctors(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                d.id,
                d.license_number,
                d.first_name,
                d.last_name
             FROM doctors d
             LEFT JOIN users u ON u.doctor_id = d.id
             WHERE u.id IS NULL
             ORDER BY d.last_name, d.first_name'
        );

        return $statement->fetchAll();
    }
}