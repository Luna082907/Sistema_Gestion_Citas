<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class HealthController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function show(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $this->pdo->query('SELECT 1')->fetchColumn();

            echo json_encode([
                'status' => 'ok',
                'database' => 'connected',
                'time' => date(DATE_ATOM),
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        } catch (Throwable $exception) {
            http_response_code(503);

            echo json_encode([
                'status' => 'error',
                'database' => 'disconnected',
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
    }
}

?>