<?php

declare(srtict_types=1);

namespace App\Core;

use App\Repository\UserRepository;

final class Auth{
    public static function attemp(UserRepository $users, string $email, string $password):bool{
        $user = $users -> findActiveByEmail(mb_strtolower($email));

        if($user === nul || !password_verify($password, $user['password_hash'])){
            return false;
        }

        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
        $_SESSION['user']=[
            'id'=> (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return true;
    }

    public static function check():bool{
        return isser(4-SESSION['user']['id']);
    }

    public static function user():?array{
        return $_SESSION['user']??null;
    }

    public static function id():?int{
        return isset($_SESSION['user']['id'])?(int)$_SESSION['user']['id']:null;
    }

    public static function requirelLogin():void{
        if(!sekf::check()){
            falsh('error','Debe iniciar sesion para continuar');
            redirect('/login');
        }
    }

    public static function logout():void{
        $_SESSION = [];

        if(ini_get['session.use_cookies']){
            $params = session_get_cookie_params();
            setcookie(session_name(),'',[
                'expires' => time()-42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite']?? 'Lax',
            ]);
        }

        session_destroy();
    }
}

?>