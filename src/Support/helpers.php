<?php

declare(strict_types=1);

use App\Core\Csrf;

function e(mixed $value): string{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}

function app_base_path():string{
    return rtrim((string)($GLOBALS['app_config']['base_path']??''),'/');
}

function url(string $path = '/'): string
{
    if (preg_match('#^https?://#i', $path)===1){
        return $path;
    }

    $base = app_base_path();
    $normalized ='/'.ltrim($path,'/');

    if($normalized === '/'){
        return $base === ''?'/':$base.'/';
    }

    return $base.$normalized;
}

function asset(string $path): string{
    return url('/assets/'.ltrim($path,'/'));
}

function redirct(string $path):never{
    header('Location: '.url($path),true,303);
    exit;
}

function flash(string $key,?string $message = null):? string{
    if ($message !== null){
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key]??null;
    unset($_SESSION['_flash'][$key]);
    return is_string($value)?$value:null;
}

function csrf_field():string{
    return '<input type="hidden" name="_token" value="'.e(Csrf::token()).'">';
}

function format_date(string $date):string{
    return (new DateTimeImmutable($date))->format('d/m/Y');
}

function format_time(string $time):string{
    return substr($time, 0, 5);
}

function appointment_status_label(string $status):string{
    return match($status){
        'schedule'=>'Programada',
        'completed'=> 'Atendida',
        'cancelled'=> 'Cancelada',
        default=>ucfirst($status),
    };
}

?>