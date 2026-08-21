<?php

declare(strict_types=1);

/* Carga un archivo .env sencillo sin dependencias externas.
Las variables ya definidas por el sistema operativo tienen prioridad */

function load_env_file(string $path): void {

    if (!is_file($path) || !is_readable($path)){
        return;
    }

    $lines=file($path,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false){
        return;
    }

    foreach($lines as $line){
        $line=trim($line);
        if($line === ''|| str_starts_with($line, '#')){
            continue;
        }

        if (str_starts_with($line, 'export')){
            $line=trim(substr($line, 7));
        }

        $separator = strpos($line, '=');
        if ($separator === false){
            continue;
        }

        $name = trim(substr($line,0,$separator));
        $value = trim(substr($line, $separator + 1));

        if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i',$name)){
            continue;
        }

        if (
            strlen($value) >= 2
            && (($value[0] === "" && $value[-1] === "") || ($value[0] === "" && $value[-1] === ""))){
                $value = substr($value, 1, -1);
            } else {
                $commentPosition = strpos($value, '#');
                if ($commentPosition !== false){
                    $value = rtrim(substr($value,0,$commentPosition));
                }
            }

        if(getenv($name) !== false){
            continue;
        }

        putenv($name.'='.$value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

?>