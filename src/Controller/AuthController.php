<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Repository\UserRepository;

final class AuthController{

    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

    public function __construct(private UserRepository $users){
    }

    public function showLogin(): void{
        if (Auth::check()) {
        redirect('/');
        }
        View::render('auth/login', ['title' => 'Iniciar sesión']);
    }

    public function login(): void{

        Csrf::requireValid($_POST['_token'] ?? null);
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if ($this->isLocked()) {
            View::render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Demasiados intentos. Espere un minuto e inténtelo nuevamente.',
                'email' => $email,
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->registerFailure();
            View::render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Ingrese un correo válido y la contraseña.',
                'email' => $email,
            ]);
            return;
        }

        if (!Auth::attempt($this->users, $email, $password)) {
            $this->registerFailure();
            View::render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Las credenciales no son válidas.',
                'email' => $email,
            ]);
            return;
        }

        unset($_SESSION['_login_failures']);
        flash('success', 'Bienvenido al sistema.');
        redirect('/');
    }

    public function logout(): void{
        Auth::requireLogin();
        Csrf::requireValid($_POST['_token'] ?? null);
        Auth::logout();
        redirect('/login');
    }

    private function registerFailure(): void{
        $failures = $_SESSION['_login_failures'] ?? ['count' => 0, 'first_at' => time()];

        if (time() - (int) $failures['first_at'] > self::LOCK_SECONDS) {
            $failures = ['count' => 0, 'first_at' => time()];
        }

        $failures['count']++;
        $_SESSION['_login_failures'] = $failures;
    }

    private function isLocked(): bool{
        $failures = $_SESSION['_login_failures'] ?? null;

        if (!is_array($failures)) {
            return false;
        }

        if (time() - (int) $failures['first_at'] > self::LOCK_SECONDS) {
            unset($_SESSION['_login_failures']);
            return false;
        }

        return (int) $failures['count'] >= self::MAX_ATTEMPTS;
    }
}

?>