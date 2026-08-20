<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class Database {
    private PDO $pdo;

    public function __construct(array $config){
        $this->pdo = new PDO(
            $config['dsn'],
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );
    }

    public function pdo():PDO{
        return $this ->pdo;
    }

    public function transaction(callable $callback):mixed{
        $this->pdo->beginTransaction();

        try{
            $result = $callback($this->pdo);
            $this->pdo->commit();
            return $result;
        } catch (Throwable $exception){
            if ($this->pdo->inTransaction()){
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }
}

?>