<?php

declare(strict_types=1);

namespace App\Core;

use App\Repository\UserRepository;

final class Auth{
    public static function attempt(UserRepository $users, string $email, string $password): bool{
        $user = $users->findActiveByEmail(mb_strtolower($email));
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }

    public static function check(): bool{
        return isset($_SESSION['user']['id']);
    }

    /** @return array{id:int,name:string,email:string,role:string}|null */
    public static function user(): ?array{
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int{
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    public static function requireLogin(): void{
        if (!self::check()) {
            flash('error', 'Debe iniciar sesión para continuar.');
            redirect('/login');
        }
    }

    public static function logout(): void{
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