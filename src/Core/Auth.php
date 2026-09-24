<?php

declare(strict_types=1);

namespace App\Core;

use App\Repository\UserRepository;

final class Auth
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_RECEPTIONIST = 'receptionist';
    public const ROLE_DOCTOR = 'doctor';

    /** @return list<string> */
    public static function roles(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_RECEPTIONIST, self::ROLE_DOCTOR];
    }

    public static function attempt(
        UserRepository $users,
        string $email,
        string $password
    ): bool {
        $user = $users->findActiveByEmail(mb_strtolower($email));

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        unset($_SESSION['csrf']);

        $_SESSION['user'] = [
            'id'        => (int) $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => (string) $user['role'],
            'doctor_id' => $user['doctor_id'] !== null ? (int) $user['doctor_id'] : null,
        ];

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']['id'])
            ? (int) $_SESSION['user']['id']
            : null;
    }

    public static function doctorId(): ?int
    {
        return isset($_SESSION['user']['doctor_id'])
            ? (int) $_SESSION['user']['doctor_id']
            : null;
    }

    public static function role(): ?string
{
    return isset($_SESSION['user']['role'])
        ? (string) $_SESSION['user']['role']
        : null;
}


public static function hasRole(string ...$roles): bool
{
    $current = self::role();
    return $current !== null && in_array($current, $roles, true);
}


    public static function roleLabel(?string $role = null): string
    {
        return match ($role ?? self::role()) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_RECEPTIONIST => 'Recepcionista',
            self::ROLE_DOCTOR => 'Médico',
            default => 'Usuario',
        };
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Debe iniciar sesión para continuar.');
            redirect('/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        if ($roles === [] || self::hasRole(...$roles)) {
            return;
        }

        self::deny();
    }

    public static function guard(string ...$roles): callable
    {
        return static function () use ($roles): void {
            self::requireRole(...$roles);
        };
    }

    public static function deny(): never
    {
        http_response_code(403);

        View::render('errors/403', [
            'title' => 'Acceso denegado',
        ]);

        exit;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }
}